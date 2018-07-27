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

//    /**
//     * @return Webobject[] Returns an array of Webobject objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Webobject
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
