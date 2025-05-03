<?php
namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Livres;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LivresFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $categories = [];

        // Générer 5 catégories
        for ($j = 1; $j <= 5; $j++) {
            $categorie = new Categorie();
            $libelle = $faker->word;
            $categorie->setLibelle($libelle);
            $categorie->setSlug(strtolower(str_replace(' ', '-', $libelle)));
            $categorie->setDescription($faker->text);
            $manager->persist($categorie);
            $categories[] = $categorie; // On garde les catégories pour les associer aux livres
        }

        // Générer 100 livres
        for ($i = 1; $i <= 100; $i++) {
            $livre = new Livres();
            $titre = $faker->sentence(3);

            $livre->setTitre($titre)
                ->setIsbn($faker->isbn13())
                ->setEditeur($faker->company)
                ->setPrix($faker->randomFloat(2, 0, 100))
                ->setDateEdition($faker->dateTimeThisCentury)
                ->setImage("https://bookthumbs.com/thumbs".$i . "/200/300")
                ->setResume($faker->text)
                ->setSlug($this->generateSlug($titre));

            // Associer une catégorie aléatoire
            $livre->setCategorie($faker->randomElement($categories));

            $manager->persist($livre);
        }

        $manager->flush();
    }

    private function generateSlug(string $title): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    }
}
