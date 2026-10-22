<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Category;
use App\Repository\BookRepository;
use App\Utils\Tools;

class BookService
{
    private BookRepository $bookRepository;

    public function __construct()
    {
        $this->bookRepository = new BookRepository();
    }

    public function insertBook(): string
    {
        if (
            empty($_POST["title"]) ||
            empty($_POST["content"]) ||
            empty($_POST["publish_at"]) ||
            empty($_POST["author"])
        ) {
            return "Veuillez remplir tous les champs du formulaire";
        }

        //Tools::sanitizeArray($_POST);
        $_POST["title"] = trim($_POST["title"]);
        $_POST["content"] = trim($_POST["content"]);
        $_POST["publish_at"] = trim($_POST["publish_at"]);
        $_POST["author"] = trim($_POST["author"]);
 
        //Validation des tailles d'input
        if (strlen($_POST["title"]) > 50) {
            return "la taille du titre est trop importante";
        }

        if (strlen($_POST["content"]) > 255) {
            return "la taille du contenu est trop importante";
        }

        if (strlen($_POST["author"]) > 100) {
            return "la taille de l'auteur est trop importante";
        }

        //Validation de la date
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $_POST["publish_at"])) {
            return "La date de publication est invalide";
        }

        $publishAt = \DateTimeImmutable::createFromFormat('!Y-m-d', $_POST["publish_at"]);

        if (
            !$publishAt ||
            $publishAt->format('Y-m-d') !== $_POST["publish_at"] ||
            (int) $publishAt->format('Y') < 1000
        ) {
            return "La date de publication est invalide";
        }

        //Hydratation en objet Book
        $book = Book::hydrate($_POST);
        //ou création d'un objet
        //$book = new Book(
        //          $_POST["title"], 
        //          $_POST["content"], 
        //          $_POST["publish_at"], 
        //          $_POST["author"]
        //      );
        
        //Ajout du livre en BDD
        $newBook = $this->bookRepository->create($book);

        //Test si l'insert est valide
        if ($newBook == null) {
            return "Le livre n’a pas pu être ajouté";
        }
        
        return "Le livre : " . $newBook->getTitle() . " a été ajouté avec succès";
    }
}
