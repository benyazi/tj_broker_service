<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\BalanceHistoryRepository")
 */
class BalanceHistory
{
    const TYPE_WRITE_OFF = 'write-off';
    const TYPE_REFILL = 'refill';

    const REASON_START = 'start-balance';
    const REASON_PURCHASE = 'purchase';
    const REASON_SALE = 'sale';
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
    private $tjUserId;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private $amount;
    /**
     * @ORM\Column(type="datetime")
     */
    private $operationDate;

    /**
     * @ORM\Column(type="string")
     */
    private $operationType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $operationReason;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $operationDescription;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2)
     */
    private $result;

    /**
     * @return mixed
     */
    public function getTjUserId()
    {
        return $this->tjUserId;
    }

    /**
     * @param mixed $tjUserId
     * @return BalanceHistory
     */
    public function setTjUserId($tjUserId)
    {
        $this->tjUserId = $tjUserId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     * @return BalanceHistory
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
     * @return BalanceHistory
     */
    public function setOperationDate($operationDate)
    {
        $this->operationDate = $operationDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOperationType()
    {
        return $this->operationType;
    }

    /**
     * @param mixed $operationType
     * @return BalanceHistory
     */
    public function setOperationType($operationType)
    {
        $this->operationType = $operationType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOperationReason()
    {
        return $this->operationReason;
    }

    /**
     * @param mixed $operationReason
     * @return BalanceHistory
     */
    public function setOperationReason($operationReason)
    {
        $this->operationReason = $operationReason;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOperationDescription()
    {
        return $this->operationDescription;
    }

    /**
     * @param mixed $operationDescription
     * @return BalanceHistory
     */
    public function setOperationDescription($operationDescription)
    {
        $this->operationDescription = $operationDescription;
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
     * @return BalanceHistory
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }
}