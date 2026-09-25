<?php
include_once "libs/modele.php";
include_once "libs/maLibForms.php";

// Protection de page
if (!isset($_SESSION['idUser'])) { 
    header("Location: index.php?view=login"); 
    die(""); 
}

$idUser = $_SESSION["idUser"];

// Calculs pour la sidebar de progression (pour garder le même style que tes autres pages)
$userInfos = SQLgetUtilisateurById($idUser);
$objectif = $userInfos['objectif'] ?? 50;
$totalVisites = SQLcompterVisites($idUser);
$pourcentage = ($objectif > 0) ? min(($totalVisites / $objectif) * 100, 100) : 0;

// Récupération du classement (Uniquement les monuments visités et notés par l'utilisateur connecté)
$monumentsClasses = SQLlisterMonumentsClassesUtilisateur($idUser);

// Préparation des données pour mkTable
$donneesTableau = [];
$rang = 1;

foreach ($monumentsClasses as $m) {
    // Création du lien dynamique avec mkLien
    // Ici, le lien renvoie vers l'accueil en passant l'id du monument en paramètre URL
    $lienMonument = "<a href='index.php?view=accueil&idMon=" . $m['id'] . "'>" . htmlspecialchars($m['nom']) . "</a>";

    $donneesTableau[] = [
        "Rang"        => "🏆 " . $rang++,
        "Monument"    => htmlspecialchars($m['nom']),
        "Ville"       => htmlspecialchars($m['ville'] ?? '-'),
        "Note"        => str_repeat('⭐', $m['note']) . ' (' . $m['note'] . '/5)',
        "Commentaire" => !empty($m['commentaire']) ? htmlspecialchars($m['commentaire']) : '<span style="color:#8a9bb5;font-style:italic;">—</span>'
        ];
    }
?>

<div class="main-interface">
    <h1>Monu Quest - Classement</h1>
    <div class="full-content-layout">
        <div class="sidebar-full">
            <h3>Progression</h3>
            <div class="progress-container">
                <div class="progress-bar" style="width: <?php echo $pourcentage; ?>%; height: 10px; background: #27ae60;"></div>
            </div>
            <p><strong><?php echo $totalVisites; ?></strong> / <?php echo $objectif; ?> monuments</p>
            
            <div class="tabs-nav">
                <a href="index.php?view=accueil" class="tab-link">CARTE</a>
                <a href="index.php?view=vus" class="tab-link">VUS</a>
                <a href="index.php?view=avoir" class="tab-link">À VOIR</a>
                <a href="index.php?view=classement" class="tab-link active">CLASSEMENT</a>
            </div>
        </div>

        <div class="tab-content">
            <h2>Classement de vos monuments préférés</h2>
            <p>Voici la liste de tes monuments triées de la meilleure à la moins bonne note.</p>
            <?php 
            if (!empty($donneesTableau)) {
                mkTable($donneesTableau);
            } else {
                echo "<p class='empty-msg'>Donne une note (sur 5) aux monuments que tu visites pour générer ton classement personnalisé !</p>";
            }
            ?>
        </div>
    </div>
</div>