<?php

namespace App\Repository;

use App\Entity\Webobject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Webobject|null find($id, $lockMode = null, $lockVersion = null)
 * @method Webobject|null findOneBy(array $criteria, array $orderBy = null)
 * @method Webobject[]    findAll()
 * @method Webobject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebobjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Webobject::class);
    }

    /**
     * @return Webobject[] Returns an array of named Webobject objects
     */

    public function findNamed() : ?array
    {
        return $this->createQueryBuilder('w')
                    ->where('w.ip != w.fullhost')
                    ->orWhere('w.ip IS NULL')
                    ->getQuery()
                    ->getResult();
    }

    public function getStoredHosts() : ?array
    {
        return $this->getEntityManager()->createQuery('SELECT DISTINCT w.fullhost FROM App\Entity\Webobject w')->getScalarResult();
    }
}
