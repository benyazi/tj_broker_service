<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class BalanceHistoryRepository extends EntityRepository
{
    public function getLastBalance($tjUserId)
    {
        return $this->findOneBy([
            'tjUserId' => $tjUserId
        ], [
            'operationDate' => 'DESC'
        ]);
    }
}