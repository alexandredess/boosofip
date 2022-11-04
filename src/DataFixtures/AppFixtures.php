<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Author;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
         // Création des auteurs.
    $listAuthor = [];
    for ($i = 0; $i < 10; $i++) {
        // Création de l'auteur lui-même.
        $author = new Author();
        //on envoie son prénom
        $author->setFirstName("Prénom " . $i);
        //on envoie son nom
        $author->setLastName("Nom " . $i);
        $manager->persist($author);
        // On sauvegarde l'auteur créé dans un tableau.
        $listAuthor[] = $author;
    }
      // Création d'une vingtaine de livres ayant pour titre
      for ($i = 0; $i < 20; $i++) {
        //on crée un nouveau livre
        $livre = new Book;
        //on envoie un titre
        $livre->setTitle('Livre ' . $i);
        //on envoie la couverture
        $livre->setCoverText('Quatrième de couverture numéro : ' . $i);
        //on envoie un auteur dans les livres parmi une liste d'auteurs
        $livre->setAuthor($listAuthor[array_rand($listAuthor)]);
        $manager->persist($livre);
    }

        $manager->flush();
    }
}
