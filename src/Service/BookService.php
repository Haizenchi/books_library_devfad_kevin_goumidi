<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\DTO\AddBookDTO;
use App\Entity\Category;
use App\Repository\BookRepository;
use App\Utils\Tools;
use Mithridatem\Validation\Validator;
use App\Service\UploadService;

class BookService
{
    private BookRepository $bookRepository;
    private Validator $validator;
    private UploadService $upload;

    public function __construct()
    {
        $this->bookRepository = new BookRepository();
        $this->validator = new Validator();
        $this->upload = new UploadService();
    }

    public function insertBook(): array
    {
        //Tableau Erreurs
        $errors = [];

        //1 Nettoyage des données
        Tools::sanitizeArray($_POST);
        
        //2 Création d'un objet AddBookDTO
        $addBook = new AddBookDTO(
            $_POST["title"],
            $_POST["content"],
            $_POST["publish_at"],
            $_POST["author"],
            $_POST["categories"]??[]
        );

        //4 Récupérer les erreurs de validations
        $errors_validation = $this->validator->validate($addBook);
        
        //5 Test si il y à des erreurs de validation (champ vide, taille, date etc ... )
        if (!empty($errors_validation)) {
            //ajouter au tableau
            $errors["validation"] = $errors_validation;
            return $errors;
        }

        //6 Créer un objet book
        $book = new Book(
            $addBook->title,
            $addBook->content,
            new \DateTimeImmutable($addBook->publishAt),
            $addBook->author
        );

        //7 Récupération des categories
        foreach ($addBook->categories as $category) {
            $cat = new Category(null);
            $cat->setId($category);
            $book->addCategory($cat);
        }

        //8 set du nom du fichier
        $book->setCover($this->upload->uploadFile($_FILES["cover"], 'assets/img/cover/'));
        
        //9 Ajout du livre en BDD
        $newBook = $this->bookRepository->create($book);

        //10 Test si l'insert est valide
        if ($newBook == null) {
            $errors["errors"] =  "Le livre n’a pas pu être ajouté";
        }
        
        //11 ajout des categories si elles ont été ajouté 
        if (!empty($book->getCategories())) {
            $this->bookRepository->assignCategoryToBook($newBook);
        }

        $errors["success"] = "Le livre : " . $newBook->getTitle() . " a été ajouté avec succès";
        
        return $errors;
    }

    public function getAllbooks(): array 
    {
        return $this->bookRepository->findAllBooks();
    }
}
