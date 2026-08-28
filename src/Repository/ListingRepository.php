<?php

namespace App\Repository;

use App\Dto\Request\Listing\ListingSearchRequestDto;
use App\Entity\Listing;
use App\Enums\ListingStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Listing>
 */
class ListingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Listing::class);
    }

    /**
     * @return array{items: list<Listing>, total: int}
     */
    public function search(ListingSearchRequestDto $filters): array
    {
        $qb = $this->createQueryBuilder('l')
            ->orderBy('l.id', 'DESC');

        if (null !== $filters->status) {
            $qb->andWhere('l.status = :status')->setParameter('status', $filters->status);
        } else {
            $qb->andWhere('l.status != :deleted')->setParameter('deleted', ListingStatus::Deleted);
        }

        if (null !== $filters->categoryId) {
            $qb->andWhere('l.category = :categoryId')->setParameter('categoryId', $filters->categoryId);
        }

        if (null !== $filters->cityId) {
            $qb->andWhere('l.city = :cityId')->setParameter('cityId', $filters->cityId);
        }

        if (null !== $filters->ownerId) {
            $qb->andWhere('l.owner = :ownerId')->setParameter('ownerId', $filters->ownerId);
        }

        if (null !== $filters->minPrice) {
            $qb->andWhere('l.price >= :minPrice')->setParameter('minPrice', $filters->minPrice);
        }

        if (null !== $filters->maxPrice) {
            $qb->andWhere('l.price <= :maxPrice')->setParameter('maxPrice', $filters->maxPrice);
        }

        if (null !== $filters->q && '' !== $filters->q) {
            $qb->andWhere('(l.title LIKE :q OR l.description LIKE :q)')
                ->setParameter('q', '%'.$filters->q.'%');
        }

        $total = (int) (clone $qb)
            ->select('COUNT(l.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $page = max(1, $filters->page);
        $limit = max(1, min(100, $filters->limit));

        $items = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'total' => $total,
        ];
    }
}
