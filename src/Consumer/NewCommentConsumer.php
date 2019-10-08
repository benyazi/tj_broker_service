<?php
namespace App\Consumer;

use App\Service\BrokerService;
use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class NewCommentConsumer implements ConsumerInterface
{
    /** @var BrokerService  */
    private $brokerService;

    public function __construct(BrokerService $brokerService)
    {
        $this->brokerService = $brokerService;
    }

    public function execute(AMQPMessage $msg)
    {
        $body = $msg->getBody();
        $commentData = unserialize($body);
        echo '==== BROKER START ======'.PHP_EOL;
        echo 'New webhook'.PHP_EOL;
        var_dump($commentData);
        $this->brokerService->checkComment($commentData);
        echo '===== BROKER END ======'.PHP_EOL;
    }
}