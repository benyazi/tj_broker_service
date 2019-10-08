<?php
namespace App\Service;

use App\Entity\BalanceHistory;
use App\Repository\BalanceHistoryRepository;
use Benyazi\CmttPhp\Api;
use Doctrine\ORM\EntityManagerInterface;
use http\Env;

class BalanceService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function printCurrent($tjUserId)
    {
        $current = $this->getCurrent($tjUserId);
        return number_format($current, '2', '.', ' '). ' олдиков';
    }

    public function getCurrent($tjUserId)
    {
        /** @var BalanceHistoryRepository $repository */
        $repository = $this->em->getRepository(BalanceHistory::class);
        /** @var BalanceHistory $last */
        $last = $repository->getLastBalance($tjUserId);
        if(empty($last)) {
            $last = $this->addStartBalance($tjUserId);
        }
        return $last->getResult();
    }

    public function addStartBalance($tjUserId)
    {
        $startAmount = 1000.00;
        $balance = new BalanceHistory();
        $balance->setTjUserId($tjUserId);
        $balance->setAmount($startAmount);
        $balance->setOperationDate(new \DateTime());
        $balance->setOperationDescription('Пополение стартового баланса');
        $balance->setOperationType(BalanceHistory::TYPE_REFILL);
        $balance->setOperationReason(BalanceHistory::REASON_START);
        $balance->setResult($startAmount);
        $this->em->persist($balance);
        $this->em->flush();
        return $balance;
    }
}