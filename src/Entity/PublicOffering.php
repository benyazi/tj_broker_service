<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\PublicOfferingRepository")
 */
class PublicOffering
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
     * @ORM\Column(type="datetime")
     */
    private $publicDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $tjUserId;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $stocksCount;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private $startPrice;

    /**
     * @return mixed
     */
    public function getPublicDate()
    {
        return $this->publicDate;
    }

    /**
     * @param mixed $publicDate
     * @return PublicOffering
     */
    public function setPublicDate($publicDate)
    {
        $this->publicDate = $publicDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTjUserId()
    {
        return $this->tjUserId;
    }

    /**
     * @param mixed $tjUserId
     * @return PublicOffering
     */
    public function setTjUserId($tjUserId)
    {
        $this->tjUserId = $tjUserId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return PublicOffering
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStocksCount()
    {
        return $this->stocksCount;
    }

    /**
     * @param mixed $stocksCount
     * @return PublicOffering
     */
    public function setStocksCount($stocksCount)
    {
        $this->stocksCount = $stocksCount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartPrice()
    {
        return $this->startPrice;
    }

    /**
     * @param mixed $startPrice
     * @return PublicOffering
     */
    public function setStartPrice($startPrice)
    {
        $this->startPrice = $startPrice;
        return $this;
    }
}