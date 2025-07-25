<?php

namespace App\Repository;

use App\Entity\Hotel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hotel>
 */
class HotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hotel::class);
    }

    //    /**
    //     * @return Hotel[] Returns an array of Hotel objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Hotel
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findAvailableOffers(): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.status = :status')
            ->setParameter('status', 'available')
            ->getQuery()
            ->getResult();
    }

    public function findWithFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('h')
            ->where('h.status = :status')
            ->setParameter('status', 'available');

        if (!empty($filters['city'])) {
            $qb->andWhere('h.city LIKE :city')
                ->setParameter('city', '%' . $filters['city'] . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
