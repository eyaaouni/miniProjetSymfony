<?php

namespace App\Controller;

use App\Entity\Livres;
use App\Repository\LivresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ClientLivreController extends AbstractController
{
    #[Route('client/livres', name: 'client_livres_list')]
    public function list(Request $request, LivresRepository $livresRepository): Response
    {
        $search = $request->query->get('search');

        if ($search) {
            $livres = $livresRepository->createQueryBuilder('l')
                ->where('l.titre LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->getQuery()
                ->getResult();
        } else {
            $livres = $livresRepository->findAll();
        }

        return $this->render('client/livres.html.twig', [
            'livres' => $livres
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
}
