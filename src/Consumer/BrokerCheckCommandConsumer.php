<?php
namespace App\Consumer;

use App\Service\TjUserService;
use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class BrokerCheckCommandConsumer implements ConsumerInterface
{
    /** @var TjUserService  */
    private $tjService;

    public function __construct(TjUserService $tjService)
    {
        $this->tjService = $tjService;
    }

    public function execute(AMQPMessage $msg)
    {
        $body = $msg->getBody();
        $json = unserialize($body);
        $data = json_decode($json, true);

        $tjUser = $this->tjService->checkOrCreateUserByData($data);
        echo '==== BROKER START ======'.PHP_EOL;
        echo 'New webhook'.PHP_EOL;
        var_dump($data);
        echo '===== BROKER END ======'.PHP_EOL;
    }
}