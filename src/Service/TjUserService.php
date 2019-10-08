<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class TjUserService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function checkOrCreateUserByData($data)
    {

    }
}