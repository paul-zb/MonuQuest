<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monu Quest</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" /> <!-- permet de charger la barre de recherche pour la carte -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>  <!-- permet de charger la carte en elle même -->
</head>
<body>

<div id="wrap">

<div class="header-simple">
    <div class="header-top">
        <h1>Monu Quest</h1>

        <?php if (isset($_SESSION['pseudo'])): ?>
        <div class="header-user">
            <span>Connecté : <b><?= htmlspecialchars($_SESSION['pseudo']) ?></b></span>
            <a href="controleur.php?action=Logout" class="btn-logout">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Déconnexion
            </a>
        </div>
        <?php endif; ?>
    </div>

    <nav class="nav-simple"> <!--les différentes pages (vues)!-->
    <a href="index.php?view=accueil" class="<?= ($view=='accueil')?'active':'' ?>">CARTE</a>
    <a href="index.php?view=vus"     class="<?= ($view=='vus')?'active':'' ?>">Visités</a>
    <a href="index.php?view=avoir"   class="<?= ($view=='avoir')?'active':'' ?>">À voir</a>
    <a href="index.php?view=classement" class="<?= ($view=='classement')?'active':'' ?>">Classement</a>
    <a href="index.php?view=ajouter" class="<?= ($view=='ajouter')?'active':'' ?>">Ajouter</a>
    <?php
    $adminCheck = SQLgetUtilisateurById($_SESSION['idUser'] ?? 0);
    if (!empty($adminCheck['is_admin'])): ?>
        <a href="index.php?view=admin" class="<?= ($view=='admin')?'active':'' ?>" style="">Admin</a>
    <?php endif; ?>
</nav>


</div>
