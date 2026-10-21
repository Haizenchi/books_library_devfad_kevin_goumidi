<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\Tools;

class UserService
{
    //Attribut
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function insertUser(): string 
    {
        //Tester si les champs obligatoire sont vides
        if (
            empty($_POST["firstname"]) ||
            empty($_POST["lastname"]) ||
            empty($_POST["email"]) ||
            empty($_POST["password"]) ||
            empty($_POST["confirm-password"])
        ) {
            return "Veuillez remplir tous les champs du formulaire";
        }

        //Nettoyer les données
        Tools::sanitizeArray($_POST);

        //Tests Si email est non valide
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            return "L'email : " . $_POST["email"] . " est invalide";
        }
        
        //Tests si les 2 mot de sont différents
        if ($_POST["password"] != $_POST["confirm-password"]) {
            return "Les mots de passe ne sont pas identiques";
        }

        //Test si le mot de passe est plus petit que 8 caractères
        if (strlen($_POST["password"]) < 8) {
            return "Le mot de passe est trop court il faut 8 caractères au minimum";
        }

        //récupérer le compte
        $user = $this->userRepository->findByEmail($_POST["email"]);

        //Tester si le compte existe
        if($user) {
            return "Le compte existe déja";
        }

        //Hasher le mot de passe
        $hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
        
        //hydrater l'objet User
        $newUser = User::hydrate($_POST);

        //Setter le mot de passe hashé
        $newUser->setPassword($hash);

        //Ajouter en BDD le compte
        $this->userRepository->create($newUser);

        return "Le compte " . $_POST["email"] . " a été ajouté en BDD";
    }



    public function connectUser(array $post): string
    {
        // Test champs remplies
        if (empty(trim($post["email"])) || empty($post["password"]))
        {
            return "Veuillez remplir tous les champs du formulaire";
        }

        // Test email valide
        if (!filter_var($post["email"], FILTER_VALIDATE_EMAIL)) {
            return "Le format de l'email est invalide";
        }

        // Get user object if exist
        $user = $this->userRepository->findByEmail(trim($post["email"]));

        if (
            !$user
            || !password_verify($post["password"], $user->getPassword())
        ) {
            return "Email ou mot de passe incorrect";
        }

        // Initialisation de la session utilisateur
        session_regenerate_id(true);

        $_SESSION["user"] = [
            "id"=> $user->getId(), 
            "firstname"=> $user->getFirstname(), 
            "lastname"=> $user->getLastname(), 
            "email"=> $user->getEmail(), 
            "grant"=> $user->getGrant() ?? ""
        ];

        return "Connexion réussie";
    }
}
