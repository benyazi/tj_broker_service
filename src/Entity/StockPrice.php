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
     * @ORM\Column(type="datetime")
     */
    private $priceDate;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private $price;

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
}