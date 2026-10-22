<?php

namespace App\Controller;

use App\Controller\AbstractController;
use App\Service\CategoryService;
use App\Entity\Category;

class CategoryController extends AbstractController
{
    private CategoryService $categoryService;

    public function __construct()
    {
        $this->categoryService = new CategoryService();
    }

    public function addCategory(): mixed
    {
        //Test si non connecté
        if (!isset($_SESSION["user"]["id"])) {
            //redirection vers la route de connexion
            header('Location:/login');
            exit;
        }
        
        //Test droit ROLE_ADMIN
        if (($_SESSION["user"]["grant"] ?? '') != 'ROLE_ADMIN') {
            http_response_code(403);
            return $this->render('error_403', '403');
        }

        //Test si le formulaire est soumis
        if (isset($_POST["submit"])) {
            //Traitement ajout de la categorie
            $data = $this->categoryService->insertCategory();
        }
        
        //Retourner le template + $data["msg"]
        return $this->render('add_category', 'Ajouter une catégorie', $data ?? []);
    }

    public function showAllCategorie(): mixed
    {
        //Récupération de la liste des categories
        $categories = $this->categoryService->getAllCategories();

        //Retourner le template + $data (liste des catégories)
        return $this->render('show_all_categories', 'Liste des catégories', $categories);
    }
}
