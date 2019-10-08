<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TjUserRepository")
 */
class TjUser
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
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $tjId;

    /**
     * @ORM\Column(type="integer")
     */
    private $karma = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $entities = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $comments = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $subs = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return TjUser
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTjId()
    {
        return $this->tjId;
    }

    /**
     * @param mixed $tjId
     * @return TjUser
     */
    public function setTjId($tjId)
    {
        $this->tjId = $tjId;
        return $this;
    }

    /**
     * @return int
     */
    public function getKarma(): int
    {
        return $this->karma;
    }

    /**
     * @param int $karma
     * @return TjUser
     */
    public function setKarma(int $karma): TjUser
    {
        $this->karma = $karma;
        return $this;
    }

    /**
     * @return int
     */
    public function getEntities(): int
    {
        return $this->entities;
    }

    /**
     * @param int $entities
     * @return TjUser
     */
    public function setEntities(int $entities): TjUser
    {
        $this->entities = $entities;
        return $this;
    }

    /**
     * @return int
     */
    public function getComments(): int
    {
        return $this->comments;
    }

    /**
     * @param int $comments
     * @return TjUser
     */
    public function setComments(int $comments): TjUser
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return int
     */
    public function getSubs(): int
    {
        return $this->subs;
    }

    /**
     * @param int $subs
     * @return TjUser
     */
    public function setSubs(int $subs): TjUser
    {
        $this->subs = $subs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return TjUser
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}