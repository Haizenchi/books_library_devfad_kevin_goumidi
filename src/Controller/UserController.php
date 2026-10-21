<?php

namespace App\Controller;

use App\Controller\AbstractController;
use App\Service\UserService;

class UserController extends AbstractController
{
    //Attribut
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function register(): mixed
    {
        $this->isConnected();

        //Tester si le formulaire est soumis
        if (isset($_POST["submit"])) {
            $data["msg"] = $this->userService->insertUser();
        }
        //retourne la vue 
        return $this->render('register', 'Inscription', $data ?? []);
    }

    public function login(): mixed
    {
        $this->isConnected();

        $data = [];

        if (isset($_POST["submit"]))
        {
            $data["msg"] = $this->userService->connectUser($_POST);
        }

        return $this->render('login', 'Connexion', $data);
    }
 
}
