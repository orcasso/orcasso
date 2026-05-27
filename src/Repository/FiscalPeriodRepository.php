<?php

namespace App\Repository;

use App\Entity\FiscalPeriod;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<FiscalPeriod>
 */
class FiscalPeriodRepository extends AbstractRepository
{
    public const ENTITY_CLASS = FiscalPeriod::class;

    public function getCurrent(): ?FiscalPeriod
    {
        return $this->findOneBy(['current' => true]);
    }

    public function getCurrentOrFail(): FiscalPeriod
    {
        if (null === $current = $this->getCurrent()) {
            throw new \RuntimeException('No current fiscal period is set.');
        }

        return $current;
    }

    /**
     * @return FiscalPeriod[]
     */
    public function findAllOrderedByCurrentFirst(): array
    {
        return $this->createQueryBuilder('fp')
            ->orderBy('fp.current', 'DESC')
            ->addOrderBy('fp.name', 'DESC')
            ->getQuery()->getResult();
    }

    public function setCurrent(FiscalPeriod $period): void
    {
        $this->checkSupport($period);

        $em = $this->getEntityManager();
        $em->wrapInTransaction(function () use ($em, $period): void {
            $em->createQuery('UPDATE '.FiscalPeriod::class.' fp SET fp.current = false WHERE fp.id <> :id')
                ->setParameter('id', $period->getId())
                ->execute();
            $period->setCurrent(true);
            $em->persist($period);
            $em->flush();
        });
    }

    public function isRemovable(object $entity): bool
    {
        /* @var FiscalPeriod $entity */
        $this->checkSupport($entity);

        if ($entity->isCurrent()) {
            return false;
        }

        return 0 === $this->getEntityManager()->getRepository(Order::class)->count(['fiscalPeriod' => $entity]);
    }
}
