<?php
namespace App\Service;

use App\Entity\PublicOffering;
use App\Entity\StockPrice;
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

    public function IPO($tjUserId)
    {
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
}