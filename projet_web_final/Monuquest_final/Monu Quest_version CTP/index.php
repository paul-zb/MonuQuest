<?php
session_start();
include_once "libs/maLibUtils.php";
include_once "libs/maLibSQL.pdo.php";
include_once "libs/modele.php"; 

$view = valider("view", "GET") ?: "accueil";

// Protection : si non connecté on est redirigé vers le login
if (!isset($_SESSION["idUser"]) && $view != "login" && $view != "inscription") {
    $view = "login";
}

// Protection vue admin
if ($view == "admin") {
    $checkAdmin = SQLgetUtilisateurById($_SESSION["idUser"] ?? 0);
    if (empty($checkAdmin['is_admin'])) {
        $view = "accueil";
    }
}

include("templates/header.php");
include("templates/$view.php");
include("templates/footer.php");
?>