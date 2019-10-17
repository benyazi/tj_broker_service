<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\StockPortfolioRepository")
 */
class StockPortfolio
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity="App\Entity\PublicOffering", inversedBy="prices")
     */
    private $publicOffering;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $userId;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $count = 0;

    /**
     * @return PublicOffering
     */
    public function getPublicOffering()
    {
        return $this->publicOffering;
    }

    /**
     * @param mixed $publicOffering
     * @return StockPortfolio
     */
    public function setPublicOffering($publicOffering)
    {
        $this->publicOffering = $publicOffering;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     * @return StockPortfolio
     */
    public function setUserId(string $userId): StockPortfolio
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return StockPortfolio
     */
    public function setCount(int $count): StockPortfolio
    {
        $this->count = $count;
        return $this;
    }
}