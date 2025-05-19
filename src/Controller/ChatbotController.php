<?php

namespace App\Controller;

use App\Repository\LivresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ChatbotController extends AbstractController
{
    private $livresRepository;

    public function __construct(LivresRepository $livresRepository)
    {
        $this->livresRepository = $livresRepository;
    }

    #[Route('/chatbot', name: 'chatbot', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        // Vérifier que l'utilisateur a le rôle ROLE_USER
        if (!$this->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('Accès refusé.');
        }

        $response = 'Bonjour, je suis un chatbot simple !';
        if ($request->isMethod('POST')) {
            $message = $request->request->get('message', '');
            if (strtolower($message) === 'bonjour') {
                $response = 'Bonjour ! Comment puis-je t\'aider aujourd\'hui ?';
            } elseif (strtolower($message) === 'aide') {
                $response = 'Je peux répondre à "bonjour", "aide", ou "ajouter le livre de titre [titre] au panier".';
            } elseif (preg_match('/commander.*livre.*(.*)/i', $message, $matches)) {
                $titre = trim($matches[1]);
                $response = 'Tu souhaites commander le livre : ' . $titre . '. Je vais traiter ta demande.';
            } elseif (preg_match('/disponible.*(.*)/i', $message, $matches)) {
                $titre = trim($matches[1]);
                $livre = $this->livresRepository->findOneBy(['titre' => $titre]);
                if ($livre) {
                    $response = 'Le livre "' . $titre . '" est disponible.';
                } else {
                    $response = 'Je ne connais pas ce livre.';
                }
            } elseif (preg_match('/je veux ajouter le livre de titre (.*) au panier/i', $message, $matches)) {
                $titre = trim($matches[1]);
                $livre = $this->livresRepository->findOneBy(['titre' => $titre]);
                if ($livre) {
                    // Utiliser le panier du ClientLivreController
                    $panier = $request->getSession()->get('panier', []);
                    $livreId = $livre->getId();
                    
                    // Incrémenter la quantité si le livre existe déjà dans le panier
                    if (isset($panier[$livreId])) {
                        $panier[$livreId]++;
                        $response = 'La quantité du livre "' . $titre . '" a été augmentée à ' . $panier[$livreId] . ' dans ton panier.';
                    } else {
                        $panier[$livreId] = 1;
                        $response = 'Le livre "' . $titre . '" a été ajouté à ton panier avec une quantité de 1.';
                    }
                    
                    $request->getSession()->set('panier', $panier);
                } else {
                    $response = 'Je ne connais pas ce livre.';
                }
            } else {
                $response = 'Je comprends ton message : "' . $message . '". Comment puis-je t\'aider davantage ?';
            }
        }
        return $this->render('chatbot/index.html.twig', [
            'response' => $response
        ]);
    }
} 