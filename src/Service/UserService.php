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
        if ($user) {
            return "Le compte existe déja";
        }

        //Hasher le mot de passe
        $hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

        //hydrater l'objet User
        $newUser = User::hydrate($_POST);

        //Setter le mot de passe hashé
        $newUser->setPassword($hash);

        $newUser->setImgProfil($this->uploadFile());
        // dd($_FILES);

        //Ajouter en BDD le compte
        $this->userRepository->create($newUser);

        return "Le compte " . $_POST["email"] . " a été ajouté en BDD";
    }

    public function connectUser(): string
    {
        //Test si les champs sont remplis
        if (empty($_POST["email"]) || empty($_POST["password"])) {
            return "Veuillez remplir tous les champs";
        }

        //Nettoyage des entrées utilisateurs
        Tools::sanitizeArray($_POST);

        //Test si le mail est valide
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            return "Le format de l’email est invalide.";
        }

        //Récupération du compte
        $user = $this->userRepository->findByEmail($_POST["email"]);

        //Test si le compte existe
        if (!$user) {
            return "Les informations de connexion ne sont pas valides";
        }

        //Test si le mot de passe est valide
        if (!password_verify($_POST["password"], $user->getPassword())) {
            return "Les informations de connexion ne sont pas valides";
        }

        //Renouvellement de l'id de session
        session_regenerate_id(true);
    
        //Création de la super globale de session
        $_SESSION["user"] = [
            "id"=> $user->getId(),
            "firstname"=> $user->getFirstname(),
            "lastname"=> $user->getLastname(),
            "email"=> $user->getEmail(),
            "grant"=> $user->getGrant() ?? ""
        ];        
        header("Refresh:2; url=/");
        return "Connexion réussie";
    }

    /**
     * Méthode pour se déconnecter
     */
    public function disconnect(): void 
    {
        //Supprimer la session
        session_destroy();
        //supprime le cookie dans le navigateur
        unset($_COOKIE["PHPSESSID"]);
        //Redirection vers home
        header('Location:/');
    }

    private function uploadFile(): ?string 
    {
        if (isset($_FILES["img_profil"])) {
            $tmp_name = $_FILES["img_profil"]["tmp_name"];
            $name_file = $_FILES["img_profil"]["name"];
            $upload_path = "assets/img/profil/";
            //récupération de l'extension
            $ext = $this->getFileExtension($name_file);
            //renommer le fichier
            $name_file = $this->renameFile($ext);
            //Déplacement du fichier aprés upload
            move_uploaded_file($tmp_name, $upload_path . $name_file);
            return $name_file;
        }
        return null;
    }

    /**
     * Méthode pour renommer le fichier avec un nom aléatoire
     * @param string $ext extension du fichier
     * @return string nom de fichier renommée
     */
    private function renameFile(string $ext): string
    {
        return uniqid(true) . "." . $ext;
    }

    /**
     * Méthode pour récupérer l'extension du fichier
     * @param string $fileName nom de fichier
     * @return string $ext extension du fichier
     */
    private function getFileExtension(string $fileName): string
    {
        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }
}
