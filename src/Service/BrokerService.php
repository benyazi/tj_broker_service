<?php
namespace App\Service;

use Benyazi\CmttPhp\Api;
use Doctrine\ORM\EntityManagerInterface;
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
            $result = $api->sendComment($contentTjId, $msg, $commentData['tj_id']);
            return;
        }
        $msg = 'Я пока не знаю такой команды :(';
        $api = new Api(Api::TJOURNAL, $token);
        $result = $api->sendComment($contentTjId, $msg, $commentData['tj_id']);
    }
}