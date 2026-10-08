<?php

namespace App\Repository;

use App\Entity\Patient;
use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RendezVous>
 */
class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    /**
     * Clés "{medecinId}|Y-m-d H:i" des créneaux déjà réservés sur la période.
     *
     * @return list<string>
     */
    public function findBookedKeys(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.medecin) AS medecin', 'r.dateDebutRDV AS debut')
            ->andWhere('r.dateDebutRDV >= :from')
            ->andWhere('r.dateDebutRDV < :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            fn (array $r) => $r['medecin'].'|'.$r['debut']->format('Y-m-d H:i'),
            $rows
        );
    }

    /**
     * @return RendezVous[]
     */
    public function findForPatient(Patient $patient): array
    {
        return $this->createQueryBuilder('r')
            ->addSelect('m', 'p')
            ->join('r.medecin', 'm')
            ->join('m.personneMedecin', 'p')
            ->andWhere('r.patient = :patient')
            ->setParameter('patient', $patient)
            ->orderBy('r.dateDebutRDV', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
