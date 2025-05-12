<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Livres;
use App\Repository\LivresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;


class ClientLivreController extends AbstractController
{
    #[Route('client/livres', name: 'client_livres_list')]
    public function list(Request $request, LivresRepository $livresRepository, PaginatorInterface $paginator): Response
    {
        $titre = $request->query->get('titre');
        $auteur = $request->query->get('auteur');
        $categorie = $request->query->get('categorie');

        // Création du QueryBuilder
        $qb = $livresRepository->createQueryBuilder('l')
            ->join('l.categorie', 'c');

        // Ajout des filtres à la requête
        if ($titre) {
            $qb->andWhere('l.titre LIKE :titre')
                ->setParameter('titre', '%' . $titre . '%');
        }

        if ($auteur) {
            $qb->andWhere('l.editeur LIKE :auteur') // ou "auteur" si c'est un champ séparé
            ->setParameter('auteur', '%' . $auteur . '%');
        }

        if ($categorie) {
            $qb->andWhere('c.libelle LIKE :categorie')
                ->setParameter('categorie', '%' . $categorie . '%');
        }

        // Pagination avec KnpPaginator
        $query = $qb->getQuery();
        $pagination = $paginator->paginate(
            $query, // La requête
            $request->query->getInt('page', 1), // La page actuelle (1 par défaut)
            10 // Le nombre d'éléments par page
        );

        // Retourne la vue avec les données de pagination
        return $this->render('client/livres.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('client/panier/add/{id}', name: 'client_panier_add')]
    public function addPanier(int $id, LivresRepository $livresRepository, SessionInterface $session): Response
    {
        $livre = $livresRepository->find($id);

        if (!$livre) {
            throw $this->createNotFoundException('Livre non trouvé');
        }

        $panier = $session->get('panier', []);

        if (!isset($panier[$id])) {
            $panier[$id] = 0;
        }
        $panier[$id]++;

        $session->set('panier', $panier);


        return $this->redirectToRoute('client_livres_list');
    }

    #[Route('client/panier', name: 'client_panier_show')]
    public function showPanier(SessionInterface $session, LivresRepository $livresRepository): Response
    {
        $panier = $session->get('panier', []);
        $livres = [];
        $total = 0;

        foreach ($panier as $id => $quantity) {
            $livre = $livresRepository->find($id);
            if ($livre) {
                $livres[] = [
                    'livre' => $livre,
                    'quantity' => $quantity,
                ];
                $total += $livre->getPrix() * $quantity;
            }
        }


        return $this->render('client/panier.html.twig', [
            'livres' => $livres,
            'total' => $total
        ]);
    }

#[Route('/client/livres/show/{id}', name: 'client_livres_show')]
    public function show(Livres $livre): Response
    {
        return $this->render('client/show.html.twig', ['livres' => $livre]);
    }

    #[Route('/client/commande/paiement', name: 'client_commande_paiement')]
    public function choisirPaiement(SessionInterface $session): Response
    {
        $modesPaiement = ['Paiement à la livraison', 'Virement bancaire'];

        return $this->render('client/choix_paiement.html.twig', [
            'modes' => $modesPaiement,
        ]);
    }
    #[Route('/client/commande/valider', name: 'client_commande_valider', methods: ['POST'])]
    public function validerCommande(Request $request, SessionInterface $session, LivresRepository $livresRepository, EntityManagerInterface $em): Response
    {
        $modePaiement = $request->request->get('mode_paiement');

        $panier = $session->get('panier', []);
        $total = 0;

        foreach ($panier as $id => $qty) {
            $livre = $livresRepository->find($id);
            if ($livre) {
                $total += $livre->getPrix() * $qty;
            }
        }

         $commande = new Commande();
        $commande->setDateCommande(new \DateTime());
        $commande->setModePaiement($modePaiement);
         $commande->setTotal($total);
         $commande->setStatut('en attente');
         $em->persist($commande);
         $em->flush();

        $session->remove('panier');

        return $this->render('client/confirmation.html.twig', [
            'mode' => $modePaiement,
            'total' => $total,
        ]);
    }

}