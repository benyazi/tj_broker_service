<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\StockOperationRepository")
 */
class StockOperation
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
     * @ORM\Column(type="datetime")
     */
    private $operationDate;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $count = 0;

    const OPERATION_TYPE_PURCHASE = 'purchase';
    const OPERATION_TYPE_SALE = 'sale';
    /**
     * @ORM\Column(type="string")
     */
    private $operationType = self::OPERATION_TYPE_PURCHASE;

    /**
     * @ORM\Column(type="string")
     */
    private $commentId;

    const STATUS_NEW = 'new';
    const STATUS_CLOSED = 'closed';
    /**
     * @ORM\Column(type="string")
     */
    private $status = self::STATUS_NEW;

    const RESULT_SUCCESS = 'success';
    const RESULT_CANCEL = 'cancel';
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $result;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $userId;

    /**
     * @return PublicOffering
     */
    public function getPublicOffering()
    {
        return $this->publicOffering;
    }

    /**
     * @param mixed $publicOffering
     * @return StockOperation
     */
    public function setPublicOffering($publicOffering)
    {
        $this->publicOffering = $publicOffering;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOperationDate()
    {
        return $this->operationDate;
    }

    /**
     * @param mixed $operationDate
     * @return StockOperation
     */
    public function setOperationDate($operationDate)
    {
        $this->operationDate = $operationDate;
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
     * @return StockOperation
     */
    public function setPrice($price)
    {
        $this->price = $price;
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
     * @return StockOperation
     */
    public function setCount(int $count): StockOperation
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperationType(): string
    {
        return $this->operationType;
    }

    /**
     * @param string $operationType
     * @return StockOperation
     */
    public function setOperationType(string $operationType): StockOperation
    {
        $this->operationType = $operationType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommentId()
    {
        return $this->commentId;
    }

    /**
     * @param mixed $commentId
     * @return StockOperation
     */
    public function setCommentId($commentId)
    {
        $this->commentId = $commentId;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return StockOperation
     */
    public function setStatus(string $status): StockOperation
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return StockOperation
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     * @return StockOperation
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
}