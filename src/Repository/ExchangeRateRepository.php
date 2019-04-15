<?php

namespace App\Repository;

use App\Entity\ExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ExchangeRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExchangeRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExchangeRate[]    findAll()
 * @method ExchangeRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    /**
     * @param \DateTime $date
     * @param array $codes
     * @return ExchangeRate[]
     */
    public function findAllByDateAndCodes(\DateTime $date, array $codes)
    {
        return $this->createQueryBuilder('r')
            ->where('r.date = :date')
            ->andWhere('r.currency_code in (:codes)')
            ->setParameters([
                'date' => $date->format('Y-m-d'),
                'codes' => $codes
            ])
            ->getQuery()
            ->getResult();
    }

    public function findOneLastDate(): ?ExchangeRate
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllLastDate()
    {
        $lastRate = $this->findOneLastDate();
        if (null === $lastRate) {
            return [];
        }
        return $this->createQueryBuilder('r')
            ->where('r.date = :date')
            ->setParameter('date', $lastRate->getDate()->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }
}
