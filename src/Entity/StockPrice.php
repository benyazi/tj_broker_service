<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\StockPriceRepository")
 */
class StockPrice
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
     * @ORM\Column(type="integer")
     */
    private $publicOfferingId;

    /**
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity="App\Entity\PublicOffering", inversedBy="prices")
     */
    private $publicOffering;

    /**
     * @ORM\Column(type="datetime")
     */
    private $priceDate;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private $price;

    /**
     * @var integer|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $oldKarma;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $currentKarma;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private $inflation;

    /**
     * @return mixed
     */
    public function getPublicOfferingId()
    {
        return $this->publicOfferingId;
    }

    /**
     * @param mixed $publicOfferingId
     * @return StockPrice
     */
    public function setPublicOfferingId($publicOfferingId)
    {
        $this->publicOfferingId = $publicOfferingId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriceDate()
    {
        return $this->priceDate;
    }

    /**
     * @param mixed $priceDate
     * @return StockPrice
     */
    public function setPriceDate($priceDate)
    {
        $this->priceDate = $priceDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return StockPrice
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOldKarma(): ?int
    {
        return $this->oldKarma;
    }

    /**
     * @param int|null $oldKarma
     * @return StockPrice
     */
    public function setOldKarma(?int $oldKarma): StockPrice
    {
        $this->oldKarma = $oldKarma;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentKarma(): int
    {
        return $this->currentKarma;
    }

    /**
     * @param int $currentKarma
     * @return StockPrice
     */
    public function setCurrentKarma(int $currentKarma): StockPrice
    {
        $this->currentKarma = $currentKarma;
        return $this;
    }

    /**
     * @return float
     */
    public function getInflation(): float
    {
        return $this->inflation;
    }

    /**
     * @param float $inflation
     * @return StockPrice
     */
    public function setInflation(float $inflation): StockPrice
    {
        $this->inflation = $inflation;
        return $this;
    }

    /**
     * @return PublicOffering
     */
    public function getPublicOffering(): ?PublicOffering
    {
        return $this->publicOffering;
    }

    /**
     * @param PublicOffering|null $publicOffering
     * @return StockPrice
     */
    public function setPublicOffering(?PublicOffering $publicOffering): StockPrice
    {
        $this->publicOffering = $publicOffering;
        return $this;
    }
}