<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Entity\Livres;
use App\Repository\LivresRepository;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


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

    #[Route('client/panier/add/{id}', name: 'client_panier_add', methods: ['POST'])]
    public function addPanier(
        int $id,
        LivresRepository $livresRepository,
        SessionInterface $session,
        Request $request
    ): Response {
        // On récupère le livre depuis la base
        $livre = $livresRepository->find($id);

        if (!$livre) {
            throw $this->createNotFoundException('Livre non trouvé');
        }

        // Récupérer la quantité depuis le formulaire
        $quantite = max(1, (int) $request->request->get('quantite', 1)); // par défaut 1 si rien envoyé

        // Récupérer le panier depuis la session (ou tableau vide)
        $panier = $session->get('panier', []);

        // Ajouter la quantité
        if (!isset($panier[$id])) {
            $panier[$id] = 0;
        }
        $panier[$id] += $quantite;

        // Enregistrer dans la session
        $session->set('panier', $panier);

        // Rediriger vers la liste
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
    public function validerCommande(
        Request $request,
        SessionInterface $session,
        LivresRepository $livresRepository,
        EntityManagerInterface $em
    ): Response {
        $modePaiement = $request->request->get('mode_paiement');
        $panier = $session->get('panier', []);
        $total = 0;

        if (empty($panier)) {
            return $this->redirectToRoute('app_livres_all');
        }

        $commande = new Commande();
        $commande->setDateCommande(new \DateTime());
        $commande->setModePaiement($modePaiement);
        $commande->setStatut('en attente');
        $commande->setUser($this->getUser());

        foreach ($panier as $id => $qty) {
            $livre = $livresRepository->find($id);
            if (!$livre) continue;

            $detail = new DetailCommande();
            $detail->setLivre($livre);
            $detail->setCommande($commande);
            $detail->setQuantite($qty);

            $em->persist($detail);

            $total += $livre->getPrix() * $qty;
        }

        $commande->setTotal($total);
        $em->persist($commande);
        $em->flush();

        $session->remove('panier');

        return $this->render('client/confirmation.html.twig', [
            'mode' => $modePaiement,
            'total' => $total,
        ]);
    }


    #[Route('/client/commandes/historique', name: 'client_commandes_historique')]
    public function historiqueCommandes(Security $security, EntityManagerInterface $em): Response
    {
        $user = $security->getUser();

        $commandes = $em->getRepository(Commande::class)->findBy([
            'user' => $user,
            'statut' => 'terminée'
        ], [
            'dateCommande' => 'DESC'
        ]);

        return $this->render('client/historique_commandes.html.twig', [
            'commandes' => $commandes
        ]);
    }


   /** #[Route('/client/commande/stripe', name: 'client_commande_stripe')]
    public function stripeCheckout(SessionInterface $session, LivresRepository $livresRepository, EntityManagerInterface $em, ParameterBagInterface $params): Response
    {
        \Stripe\Stripe::setApiKey($params->get('stripe_secret_key'));

        $panier = $session->get('panier', []);
        $lineItems = [];
        $total = 0;

        foreach ($panier as $id => $qty) {
            $livre = $livresRepository->find($id);
            if ($livre) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $livre->getTitre(),
                        ],
                        'unit_amount' => $livre->getPrix() * 100,
                    ],
                    'quantity' => $qty,
                ];
                $total += $livre->getPrix() * $qty;
            }
        }

        $checkoutSession = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [$lineItems],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('client_commande_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('client_panier_show', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkoutSession->url);
    }


    #[Route('/client/commande/stripe/success', name: 'client_commande_stripe_success')]
    public function stripeSuccess(SessionInterface $session, LivresRepository $livresRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $panier = $session->get('panier', []);
        $total = 0;

        foreach ($panier as $id => $qty) {
            $livre = $livresRepository->find($id);
            if ($livre) {
                $total += $livre->getPrix() * $qty;
            }
        }

        $commande = new Commande();
        $commande->setUser($user);
        $commande->setDateCommande(new \DateTime());
        $commande->setModePaiement('Stripe');
        $commande->setStatut('terminée');
        $commande->setTotal($total);

        $em->persist($commande);
        $em->flush();

        $session->remove('panier');

        return $this->render('client/confirmation.html.twig', [
            'mode' => 'Stripe',
            'total' => $total
        ]);
    }
*/

}