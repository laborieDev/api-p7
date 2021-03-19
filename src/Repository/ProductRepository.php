<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param User $user
     * @return Array 
     */
    public function getUserProducts(User $user)
    {
        return $this->createQueryBuilder('p')
                            ->innerJoin('p.users', 'u')
                            ->where('u.id = :user_id')
                            ->setParameter('user_id', $user->getId())
                            ->getQuery()->getResult();
    }
}
