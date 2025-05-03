<?php

namespace App\Controller;

use App\Entity\Livres;
use App\Form\livreType;
use App\Form\LivresType;
use App\Repository\livreRepository;
use App\Repository\LivresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LivresController extends AbstractController
{

    #[Route('/admin/livre/{id}/edit', name: 'app_livres_edit')]
    public function edit(int $id, LivresRepository $rep, EntityManagerInterface $em, Request $request): Response
    {
        // 1. Récupérer la catégorie depuis la base
        $livre = $rep->find($id);

        if (!$livre) {
            throw $this->createNotFoundException("Livre introuvable !");
        }

        // 2. Créer le formulaire pré-rempli avec les données existantes
        $form = $this->createForm(LivresType::class, $livre);
        $form->handleRequest($request);

        // 3. Enregistrer les modifications si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush(); // Pas besoin de persist, l'objet est déjà managé
            return $this->redirectToRoute('app_livres_all');
        }

        // 4. Afficher le formulaire
        return $this->render('livres/edit.html.twig', [
            'f' => $form->createView(),
            'livre' => $livre
        ]);
    }
    #[Route('/admin/livres/delete/{id}', name: 'app_livres_delete')]
    public function delete(LivresRepository $rep, EntityManagerInterface $em, $id): Response
    {
        $livre = $rep->find($id);

        if (!$livre) {
            throw $this->createNotFoundException("Livre avec ID {$id} introuvable.");
        }

        $em->remove($livre);
        $em->flush();

        return $this->redirectToRoute('app_livres_all');
        
    }

    #[Route('/admin/livres/all', name: 'app_livres_all')]
public function all(LivresRepository $rep, PaginatorInterface $paginator, Request $request): Response
{
    $query = $rep->createQueryBuilder('l')->getQuery();

    $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        10
    );

    return $this->render('livres/all.html.twig', [
        'pagination' => $pagination // ✅ ici le nom doit être "pagination"
    ]);
}

    

    #[Route('/admin/livres/show2', name: 'app_livres_show2')]
    public function show2(LivresRepository $rep): Response
    {
        $livres = $rep->findBy(['titre' => 'titre 1']);
        dd($livres);
    }

    #[Route('/admin/livres/show/{id}', name: 'app_livres_show')]
    public function show(Livres $livre): Response
    {
        return $this->render('livres/show.html.twig', ['livres' => $livre]);
    }

    #[Route('/admin/livres/create', name: 'app_livres_create')]
    public function create(EntityManagerInterface $em, Request $request): Response
    {
        $Livres = new Livres();
        $form = $this->createForm(LivresType::class, $Livres);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($Livres);
            $em->flush();

            return $this->redirectToRoute('app_livres_all');
        }

        return $this->render('livres/create.html.twig', [
            'f' => $form->createView(),
        ]);
    }
}
