<?php

namespace App\Repository;

use App\Database\Mysql;
use App\Entity\Book;
use App\Entity\Category;

class BookRepository
{
    private \PDO $connexion;

    public function __construct()
    {
        $this->connexion = Mysql::connectBdd();
    }

    public function create(Book $book): ?Book
    {
        try {
            $sql = "INSERT INTO book(title, content, publish_at, author, cover) VALUE (?,?,?,?,?)";
            $stmt = $this->connexion->prepare($sql);
            $stmt->bindValue(1, $book->getTitle(), \PDO::PARAM_STR);
            $stmt->bindValue(2, $book->getContent(), \PDO::PARAM_STR);
            $stmt->bindValue(3, $book->getPublishAt()->format('Y-m-d'), \PDO::PARAM_STR);
            $stmt->bindValue(4, $book->getAuthor(), \PDO::PARAM_STR);
            $stmt->bindValue(5, $book->getCover(), \PDO::PARAM_STR);
            $stmt->execute();
            $id = $this->connexion->lastInsertId();
            $book->setId($id);
        } catch (\PDOException $e) {
            echo $e->getMessage();
            error_log($e->getMessage());
            return null;
        }
        return $book;
    }

    public function assignCategoryToBook(Book $book): ?Book
    {
        try {
            foreach ($book->getCategories() as $category) {
                $sql = "INSERT INTO book_category(id_book,id_category) VALUE (?,?)";
                $stmt = $this->connexion->prepare($sql);
                $stmt->bindValue(1, $book->getId(), \PDO::PARAM_INT);
                $stmt->bindValue(2, $category->getId(), \PDO::PARAM_INT);
                $stmt->execute();
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
            error_log($e->getMessage());
            return null;
        }
        return $book;
    }

    //Méthode pour récupérer les livres sans les categories
    public function findAllBooksWithoutCategories(): array
    {
        $books = [];
        try {
            $sql = "SELECT b.id_book, b.title, b.content, b.publish_at, b.author, b.cover FROM book AS b";
            $stmt = $this->connexion->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($data as $book) {
                $books[] = Book::hydrate($book);
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
            error_log($e->getMessage());
        }
        return $books ?? [];
    }

    //méthodes pour récupérer les categories du livre
    public function findCategoriesByBook(Book $book): ?Book 
    {
        try {
            $sql = "SELECT c.id_category, c.category_name FROM book_category AS bc
            INNER JOIN category As c ON bc.id_category = c.id_category
            WHERE bc.id_book = ?";
            $stmt = $this->connexion->prepare($sql);
            $stmt->bindValue(1, $book->getId(), \PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($data as $category) {
                $cat = new Category($category["category_name"]);
                $cat->setId($category["id_category"]);
                $book->addCategory($cat);
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
            error_log($e->getMessage());
        }
        return $book;
    }

    //méthode qui assigne les categories au livres
    public function findAllBooks() : array 
    {
        $booksWithCategories = [];
        try {
            $books = $this->findAllBooksWithoutCategories();
            foreach ($books as $book) {
                $booksWithCategories[] = $this->findCategoriesByBook($book);
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
            error_log($e->getMessage());
        }
        return $booksWithCategories;
    }
}
