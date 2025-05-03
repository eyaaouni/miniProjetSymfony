<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Livres;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use App\Repository\LivresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // ✅ la bonne version
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategorieController extends AbstractController
{
    #[Route('/admin/categorie', name: 'admin_categorie')]
    public function index(CategorieRepository $rep): Response
    {
        $categories = $rep->findAll();


        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
        ]);
    }


    #[Route('/admin/categorie/create', name: 'admin_categorie_create')]
    public function create(EntityManagerInterface $em, Request $request): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();


            return $this->redirectToRoute('admin_categorie');
        }

        return $this->render('categorie/create.html.twig', [
            'f' => $form->createView(), 
        ]);
    }
    #[Route('/admin/categorie/{id}/edit', name: 'admin_categorie_edit')]
public function edit(int $id, CategorieRepository $rep, EntityManagerInterface $em, Request $request): Response
{
    // 1. Récupérer la catégorie depuis la base
    $categorie = $rep->find($id);

    if (!$categorie) {
        throw $this->createNotFoundException("Catégorie introuvable !");
    }

    // 2. Créer le formulaire pré-rempli avec les données existantes
    $form = $this->createForm(CategorieType::class, $categorie);
    $form->handleRequest($request);

    // 3. Enregistrer les modifications si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush(); // Pas besoin de persist, l'objet est déjà managé
        return $this->redirectToRoute('admin_categorie');
    }

    // 4. Afficher le formulaire
    return $this->render('categorie/edit.html.twig', [
        'f' => $form->createView(),
        'categorie' => $categorie
    ]);
}
    #[Route('/admin/categorie/delete/{id}', name: 'categorie_delete', methods: ['POST'])]
    public function delete(CategorieRepository $rep, EntityManagerInterface $em, $id): Response
    {
        $categorie = $rep->find($id);

        if (!$categorie) {
            throw $this->createNotFoundException("Catégorie avec ID {$id} introuvable.");
        }

        // ✅ Vérifier s’il y a des livres liés
        if (!$categorie->getLivres()->isEmpty()) {
            return $this->redirectToRoute('admin_categorie');
        }

        $em->remove($categorie);
        $em->flush();

        return $this->redirectToRoute('admin_categorie');
    }

    #[Route('/admin/categorie/show/{id}', name: 'categorie_show')]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/show.html.twig', ['categorie' => $categorie]);
    }


}
