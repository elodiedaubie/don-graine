<?php

namespace App\Repository;

use App\Entity\Purpose;
use App\Entity\SeedBatch;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<SeedBatch>
 *
 * @method SeedBatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeedBatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method SeedBatch[]    findAll()
 * @method SeedBatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeedBatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeedBatch::class);
    }

    public function add(SeedBatch $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SeedBatch $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLikeName(string $name)
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->join('s.plant', 'p')
            ->where('p.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('p.name', 'ASC')
            ->getQuery();

        return $queryBuilder->getResult();
    }

    public function findbyPurpose(Purpose $purpose)
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->join('s.plant', 'p')
            ->where('p.purpose = :purpose')
            ->setParameter('purpose', $purpose)
            ->getQuery();

        return $queryBuilder->getResult();
    }

//    /**
//     * @return SeedBatch[] Returns an array of SeedBatch objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SeedBatch
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
