<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Listing;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findOneByListingAndBuyer(Listing $listing, User $buyer): ?Conversation
    {
        return $this->findOneBy(['listing' => $listing, 'buyer' => $buyer]);
    }

    /**
     * @return list<Conversation>
     */
    public function findForParticipant(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.buyer = :user OR c.seller = :user')
            ->setParameter('user', $user)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
