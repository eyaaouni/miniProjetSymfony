<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /**
     * Retourne le livre le plus vendu.
     */
    public function findTopVente(): array
    {
        return $this->getEntityManager()->createQuery(
            'SELECT l.titre, SUM(d.quantite) AS total
             FROM App\Entity\DetailCommande d
             JOIN d.livre l
             GROUP BY l.id
             ORDER BY total DESC'
        )
            ->setMaxResults(1)
            ->getResult();
    }

    /**
     * Retourne le total des commandes groupé par mois (YYYY-MM).
     * Utilise SQL natif.
     */
    public function countCommandesParMois(): array
    {
        // Récupérer la connexion à la base de données
        $conn = $this->getEntityManager()->getConnection();

        // Requête SQL native utilisant la fonction SQL DATE_FORMAT
        $sql = "SELECT DATE_FORMAT(c.date_commande, '%Y-%m') AS mois, SUM(c.total) AS total
            FROM commande c
            GROUP BY mois
            ORDER BY mois ASC";

        // Exécution de la requête SQL
        $stmt = $conn->executeQuery($sql);

        // Récupérer et retourner les résultats
        return $stmt->fetchAllAssociative();
    }


}
