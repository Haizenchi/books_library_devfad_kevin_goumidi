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
}
