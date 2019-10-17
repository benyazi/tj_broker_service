<?php
namespace App\Service;

use App\Entity\BalanceHistory;
use App\Entity\PublicOffering;
use App\Entity\StockOperation;
use App\Entity\StockPortfolio;
use App\Entity\StockPrice;
use App\Repository\StockPortfolioRepository;
use Benyazi\CmttPhp\Api;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use http\Env;

class BrokerService
{
    /** @var EntityManager  */
    private $em;
    private $balanceService;

    public function __construct(EntityManagerInterface $em, BalanceService $balanceService)
    {
        $this->em = $em;
        $this->balanceService = $balanceService;
    }

    public function checkComment($commentData)
    {
        if(!isset($commentData['text']) || empty($commentData['text'])) {
            return;
        }
        $commentText = $commentData['text'];
        if(strpos($commentText, '[@268765|') === false) {
            echo 'Не нашлось команды '.$commentText.PHP_EOL;
            return;
        }
        $contentTjId = $commentData['content_tj_id'];
        $creatorTjId = $commentData['creator_tj_id'];
        if($creatorTjId < 1) {
            echo 'Я не знать такого юзера > '.$creatorTjId.PHP_EOL;
            return;
        }
        $token = $_ENV['TJ_BROKER_TOKEN'];
        if(strpos(mb_strtoupper($commentText), 'МОЙ БАЛАНС') !== false) {
            $msg = 'Твой баланс: ' . $this->balanceService->printCurrent($creatorTjId);
            $api = new Api(Api::TJOURNAL, $token);
            try {
                $result = $api->sendComment($contentTjId, $msg, $commentData['tj_id']);
            } catch (\Exception $e) {
                if(strpos($e->getMessage(), '404 Not Found') === false) {
                    echo $e->getMessage() . PHP_EOL;
                    echo $e->getFile() . ' ' . $e->getLine() . PHP_EOL;
                    throw $e;
                } else {
                    echo 'Станица на ТЖ не найдена'.PHP_EOL;
                }
            }
            return;
        } elseif(strpos(mb_strtoupper($commentText), 'МОИ АКЦИИ') !== false) {
            $msg = 'Твой портфель: ' . PHP_EOL. $this->printPortfolioList($creatorTjId);
            $api = new Api(Api::TJOURNAL, $token);
            try {
                $result = $api->sendComment($contentTjId, $msg, $commentData['tj_id']);
            } catch (\Exception $e) {
                if(strpos($e->getMessage(), '404 Not Found') === false) {
                    echo $e->getMessage() . PHP_EOL;
                    echo $e->getFile() . ' ' . $e->getLine() . PHP_EOL;
                    throw $e;
                } else {
                    echo 'Станица на ТЖ не найдена'.PHP_EOL;
                }
            }
            return;
        } elseif (strpos(mb_strtoupper($commentText), 'ИНФОРМАЦИЯ О') !== false) {
            $matches = [];
            $regExp = '/[\[]{1}[@]{1}([\d]{1,})/';
            preg_match_all($regExp, $commentText, $matches);
            if(isset($matches[1]) && count($matches[1]) < 2) {
                echo 'Недостаточно упоминаний в тексте'.PHP_EOL;
                return;
            }
            if($matches[1][0] != 268765) {
                echo 'Первым надо упомянуть бота'.PHP_EOL;
            }
            if($matches[1][1] == 268765) {
                echo 'Вторым надо упомянуть НЕ бота'.PHP_EOL;
            }
            $ipo = $this->IPO($matches[1][1]);
            $msg = $this->printIPOInfo($ipo);
            $api = new Api(Api::TJOURNAL, $token);
            $result = $api->sendComment($contentTjId, $msg, $commentData['tj_id']);
            return;
        } elseif (strpos(mb_strtoupper($commentText), 'КУПИТЬ') !== false) {
            $matches = [];
            $regExp = '/[\[]{1}[@]{1}([\d]{1,})/';
            preg_match_all($regExp, $commentText, $matches);
            if(isset($matches[1]) && count($matches[1]) < 2) {
                echo 'Недостаточно упоминаний в тексте'.PHP_EOL;
                return;
            }
            if($matches[1][0] != 268765) {
                echo 'Первым надо упомянуть бота'.PHP_EOL;
                return;
            }
            if($matches[1][1] == 268765) {
                echo 'Вторым надо упомянуть НЕ бота'.PHP_EOL;
                return;
            }
            $output_array = [];
            preg_match('/КУПИТЬ ([\d]{1,})/', mb_strtoupper($commentText), $output_array);
            if(count($output_array) != 2 || $output_array[1] < 1) {
                echo 'Необходимо написать, например, "купить 100"';
                return;
            }
            $count = $output_array[1];
            try {
                $operation = $this->createNewPurchase($commentData['tj_id'], $commentData['creator_tj_id'], $matches[1][1], $count);
                $this->processOperation($operation);
                $msg = $this->printOperationResult($operation);
            } catch (\Exception $e) {
                if(in_array($e->getCode(), [510])) {
                    $msg = $e->getMessage();
                } else {
                    return;
                }
            }
            $api = new Api(Api::TJOURNAL, $token);
            $result = $api->sendComment($contentTjId, $msg, $commentData['tj_id']);
            return;
        } elseif (strpos(mb_strtoupper($commentText), 'ПРОДАТЬ') !== false) {
            $matches = [];
            $regExp = '/[\[]{1}[@]{1}([\d]{1,})/';
            preg_match_all($regExp, $commentText, $matches);
            if(isset($matches[1]) && count($matches[1]) < 2) {
                echo 'Недостаточно упоминаний в тексте'.PHP_EOL;
                return;
            }
            if($matches[1][0] != 268765) {
                echo 'Первым надо упомянуть бота'.PHP_EOL;
                return;
            }
            if($matches[1][1] == 268765) {
                echo 'Вторым надо упомянуть НЕ бота'.PHP_EOL;
                return;
            }
            $output_array = [];
            preg_match('/ПРОДАТЬ ([\d]{1,})/', mb_strtoupper($commentText), $output_array);
            if(count($output_array) != 2 || $output_array[1] < 1) {
                echo 'Необходимо написать, например, "продать 100"';
                return;
            }
            $count = $output_array[1];
            try {
                $operation = $this->createNewSale($commentData['tj_id'], $commentData['creator_tj_id'], $matches[1][1], $count);
                $this->processOperation($operation);
                $msg = $this->printOperationResult($operation);
            } catch (\Exception $e) {
                if(in_array($e->getCode(), [510])) {
                    $msg = $e->getMessage();
                } else {
                    return;
                }
            }
            $api = new Api(Api::TJOURNAL, $token);
            $result = $api->sendComment($contentTjId, $msg, $commentData['tj_id']);
            return;
        }
        $msg = 'Я пока не знаю такой команды :(';
        $api = new Api(Api::TJOURNAL, $token);
        $result = $api->sendComment($contentTjId, $msg, $commentData['tj_id']);
    }

    /**
     * @param PublicOffering $ipo
     * @return string
     */
    public function printIPOInfo($ipo)
    {
        $msg = $ipo->getTitle(). PHP_EOL;
        $msg .= 'Акций выпущено: ' .$ipo->getStocksCount().PHP_EOL;
        $msg .= 'Стоимость одной акции: ' .$ipo->getStartPrice().PHP_EOL;
        return $msg;
    }

    /**
     * @param int $tjUserId
     * @return array
     */
    public function getUserData(int $tjUserId)
    {
        $client = new Client();
        $url = $_ENV['TJ_WEBHOOK_SERVICE_URL'];
        $tjUserData = $client->get($url . 'api/getUserInfo/' . $tjUserId)->getBody()->getContents();
        $tjUserData = json_decode($tjUserData, true);
        return $tjUserData;
    }

    /**
     * @param $tjUserId
     * @return PublicOffering
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function IPO($tjUserId)
    {
        /** @var PublicOffering $po */
        $po = $this->em->getRepository(PublicOffering::class)
            ->findOneBy([
                'tjUserId' => $tjUserId
            ]);
        if(!empty($po)) {
            return $po;
        }
        $tjUserData = $this->getUserData($tjUserId);
        $po = new PublicOffering();
        $po->setTjUserId($tjUserId);
        $po->setPublicDate(new \DateTime());
        $po->setTitle($this->generateTitle($tjUserData));
        $karma = (int) $tjUserData['karma'];
        if($karma < 0) {
            $karma = 100;
        }
        $stockCount = 1000;
        $startPrice = ((float) $karma/$stockCount);
        $startPrice = floor($startPrice * 100) / 100;
        $po->setStartPrice($startPrice);
        $po->setStocksCount($stockCount);
        $po->setAllowedStocksCount($stockCount);
        $this->em->persist($po);

        $stockPrice = new StockPrice();
        $stockPrice->setCurrentKarma($karma);
        $stockPrice->setInflation(0);
        $stockPrice->setPrice($startPrice);
        $stockPrice->setPriceDate(new \DateTime());
        $stockPrice->setPublicOffering($po);
        $this->em->persist($stockPrice);

        $this->em->flush();
        return $po;
    }

    public function generateTitle($tjUserData)
    {
        $name = $tjUserData['name'];
        $engName = $this->rus2translit($name);
        $engName = strtolower($engName);
        // заменям все ненужное нам на "-"
        $engName = preg_replace('~[^-a-z0-9_]+~u', '', $engName);
        // удаляем начальные и конечные '-'
        $engName = trim($engName);
        $engName = strtoupper($engName);
        $title = substr($engName, 0, 5);
        return $title . random_int(11,99);
    }

    public function rus2translit($string) {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );
        return strtr($string, $converter);
    }

    /**
     * @param PublicOffering $ipo
     */
    public function calculateNewPrice($ipo)
    {
        $currentPrice = $this->em->getRepository(StockPrice::class)
            ->createQueryBuilder('sp')
            ->andWhere('sp.publicOfferingId = :publicOfferingId')->setParameter('publicOfferingId', $ipo->getId())
            ->orderBy('sp.priceDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getResult();
        if(empty($currentPrice)) {
            throw new \Exception('not found price');
        }
        /** @var StockPrice $currentPrice */
        $currentPrice = $currentPrice[0];
        $tjUserData = $this->getUserData($ipo->getTjUserId());
        $newKarma = $tjUserData['karma'];

        $price = ((float) $newKarma/$ipo->getStocksCount());
        $price = floor($price * 100) / 100;

        $diff = $price - $currentPrice->getPrice();
        $inflation = $currentPrice->getInflation();

        $newCalculatedPrice = $price;

        $newPrice = new StockPrice();
        $newPrice->setOldKarma($currentPrice->getCurrentKarma());
        $newPrice->setCurrentKarma($newKarma);
        $newPrice->setPrice($newCalculatedPrice);
        $newPrice->setPriceDate(new \DateTime());
        $newPrice->setInflation($inflation);
        $newPrice->setPublicOffering($ipo);
        $this->em->persist($newPrice);
        $this->em->flush();
    }

    /**
     * @param PublicOffering $po
     * @return StockPrice
     * @throws \Exception
     */
    public function getCurrentPrice(PublicOffering $po)
    {
        $currentPrice = $this->em->getRepository(StockPrice::class)
            ->createQueryBuilder('sp')
            ->andWhere('sp.publicOfferingId = :publicOfferingId')->setParameter('publicOfferingId', $po->getId())
            ->orderBy('sp.priceDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getResult();
        if(empty($currentPrice)) {
            throw new \Exception('not found price');
        }
        /** @var StockPrice $currentPrice */
        $currentPrice = $currentPrice[0];
        return $currentPrice;
    }

    /**
     * @param $commentId
     * @param $fromUserId
     * @param $targetUserId
     * @param int $count
     * @return StockOperation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createNewPurchase($commentId, $fromUserId, $targetUserId, $count)
    {
        $po = $this->IPO($targetUserId);
        if($po->getAllowedStocksCount() < $count) {
            throw new \Exception('Недостаточно свободных акций', 510);
        }
        $balance = $this->balanceService->getCurrent($fromUserId);
        $currentPrice = $this->getCurrentPrice($po);
        $needMoney = $currentPrice->getPrice() * $count;
        $needMoney = floor($needMoney * 100) / 100;
        if($balance < $needMoney) {
            throw new \Exception('Недостаточно денег для покупки акций', 510);
        }
        $stockOperation = new StockOperation();
        $stockOperation->setCommentId($commentId);
        $stockOperation->setPublicOffering($po);
        $stockOperation->setPrice($currentPrice->getPrice());
        $stockOperation->setCount($count);
        $stockOperation->setOperationType(StockOperation::OPERATION_TYPE_PURCHASE);
        $stockOperation->setOperationDate(new \DateTime());
        $stockOperation->setUserId($fromUserId);
        $stockOperation->setStatus(StockOperation::STATUS_NEW);
        $this->em->persist($stockOperation);
        $this->em->flush();
        return $stockOperation;
    }

    /**
     * @param $commentId
     * @param $fromUserId
     * @param $targetUserId
     * @param int $count
     * @return StockOperation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createNewSale($commentId, $fromUserId, $targetUserId, $count)
    {
        $po = $this->IPO($targetUserId);
//        if($po->getAllowedStocksCount() < $count) {
//            throw new \Exception('Недостаточно свободных акций', 510);
//        }
//        $balance = $this->balanceService->getCurrent($fromUserId);
        $currentPrice = $this->getCurrentPrice($po);
//        $needMoney = $currentPrice->getPrice() * $count;
//        $needMoney = floor($needMoney * 100) / 100;
//        if($balance < $needMoney) {
//            throw new \Exception('Недостаточно денег для покупки акций', 511);
//        }
        $stockOperation = new StockOperation();
        $stockOperation->setCommentId($commentId);
        $stockOperation->setPublicOffering($po);
        $stockOperation->setPrice($currentPrice->getPrice());
        $stockOperation->setCount($count);
        $stockOperation->setOperationType(StockOperation::OPERATION_TYPE_SALE);
        $stockOperation->setOperationDate(new \DateTime());
        $stockOperation->setUserId($fromUserId);
        $stockOperation->setStatus(StockOperation::STATUS_NEW);
        $this->em->persist($stockOperation);
        $this->em->flush();
        return $stockOperation;
    }

    /**
     * @param StockOperation $stockOperation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function processOperation(StockOperation $stockOperation)
    {
        /** @var StockPortfolioRepository $rep */
        $rep = $this->em->getRepository(StockPortfolio::class);
        $stockPortfolio = $rep->getByUserAndPoOrCreate($stockOperation->getUserId(), $stockOperation->getPublicOffering());
        if($stockOperation->getOperationType() == StockOperation::OPERATION_TYPE_PURCHASE) {
            $stockPortfolio->setCount($stockPortfolio->getCount() + $stockOperation->getCount());
        } else {
            //check
            if($stockOperation->getCount() > $stockPortfolio->getCount()) {
                $stockOperation->setStatus(StockOperation::STATUS_CLOSED);
                $stockOperation->setResult(StockOperation::RESULT_CANCEL);
                $this->em->flush();
                throw new \Exception('Недостаточно акция для продажи', 510);
            }
            $stockPortfolio->setCount($stockPortfolio->getCount() - $stockOperation->getCount());
        }
        $stockOperation->setStatus(StockOperation::STATUS_CLOSED);
        $stockOperation->setResult(StockOperation::RESULT_SUCCESS);

        $amount = $stockOperation->getPrice() * $stockOperation->getCount();
        $amount = floor($amount * 100) / 100;
        $po = $stockOperation->getPublicOffering();
        if($stockOperation->getOperationType() == StockOperation::OPERATION_TYPE_PURCHASE) {
            $po->setAllowedStocksCount($po->getAllowedStocksCount() - $stockOperation->getCount());
            $this->balanceService->withDraw($stockOperation->getUserId(), $amount, BalanceHistory::REASON_PURCHASE, 'Покупка акции по операции №' . $stockOperation->getId());
        } else {
            $po->setAllowedStocksCount($po->getAllowedStocksCount() + $stockOperation->getCount());
            $this->balanceService->refill($stockOperation->getUserId(), $amount, BalanceHistory::REASON_SALE, 'Продажа акции по операции №' . $stockOperation->getId());
        }

        $this->em->flush();
    }

    /**
     * @param StockOperation $so
     * @return string
     */
    public function printOperationResult(StockOperation $so)
    {
        $count = $so->getCount();
        $price = $so->getPrice() * $count;
        if($so->getResult() == StockOperation::RESULT_SUCCESS) {
            if($so->getOperationType() == StockOperation::OPERATION_TYPE_PURCHASE) {
                return "Вы успешно приобрели $count акций за $price";
            } else {
                return "Вы успешно продали $count акций за $price";
            }
        }
        return "Операция завершилась неудачно :(";
    }

    public function printPortfolioList($userId)
    {
        /** @var StockPortfolioRepository $rep */
        $rep = $this->em->getRepository(StockPortfolio::class);
        $portfolios = $rep->getByUser($userId);
        if(empty($portfolios)) {
            return 'Список пока пуст :(';
        }
        $list = '';
        /** @var StockPortfolio $portfolio */
        foreach ($portfolios as $portfolio)
        {
            $count = $portfolio->getCount();
            $po = $portfolio->getPublicOffering();
            $currentPrice = $this->getCurrentPrice($po)->getPrice();
            $name = $po->getTitle();
            $price = $currentPrice * $count;
            $list .= "*$name*: $currentPrice * $count = $price".PHP_EOL;
        }
        return $list;
    }
}