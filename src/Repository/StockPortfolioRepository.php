<?php
namespace App\Repository;

use App\Entity\PublicOffering;
use App\Entity\StockPortfolio;
use Doctrine\ORM\EntityRepository;

class StockPortfolioRepository extends EntityRepository
{
    public function getByUserAndPoOrCreate($userId, PublicOffering $po)
    {
        $portfolio = $this->findOneBy([
            'userId' => $userId,
            'publicOffering' => $po
        ]);
        if(empty($portfolio)) {
            $portfolio = new StockPortfolio();
            $portfolio->setUserId($userId);
            $portfolio->setPublicOffering($po);
            $this->getEntityManager()->persist($portfolio);
        }
        return $portfolio;
    }

    public function getByUser($userId)
    {
        $portfolios = $this->findBy([
            'userId' => $userId
        ]);
        return $portfolios;
    }
}