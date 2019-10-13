<?php
namespace App\Service;

use App\Entity\PublicOffering;
use Benyazi\CmttPhp\Api;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use http\Env;

class BrokerService
{
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

    public function IPO($tjUserId)
    {
        $po = $this->em->getRepository(PublicOffering::class)
            ->findOneBy([
                'tjUserId' => $tjUserId
            ]);
        if(!empty($po)) {
            return $po;
        }
        $client = new Client();
        $url = $_ENV['TJ_WEBHOOK_SERVICE_URL'];
        $tjUserData = $client->get($url . 'api/getUserInfo/' . $tjUserId)->getBody()->getContents();
        $tjUserData = json_decode($tjUserData, true);
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
        $this->em->flush();
        return $po;
    }

    public function generateTitle($tjUserData)
    {
        return 'NASDAQ-'.random_int(111,999);
    }
}