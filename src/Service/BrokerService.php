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
                echo $e->getMessage().PHP_EOL;
                echo $e->getFile().' '.$e->getLine().PHP_EOL;
                throw $e;
            }
            return;
        }
        $msg = 'Я пока не знаю такой команды :(';
        $api = new Api(Api::TJOURNAL, $token);
        $result = $api->sendComment($contentTjId, $msg, $commentData['tj_id']);
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
        $tjUserData = $client->get($url . 'api/getUserInfo/{id}')->getBody()->getContents();
        $po = new PublicOffering();
        $po->setTjUserId($tjUserId);
        $po->setPublicDate(new \DateTime());
//        $po->setTitle()
    }

    public function generateTitle()
    {

    }
}