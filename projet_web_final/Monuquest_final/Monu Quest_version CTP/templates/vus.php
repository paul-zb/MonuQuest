<?php
include_once "libs/modele.php";
include_once "libs/maLibForms.php"; 

if (!isset($_SESSION['idUser'])) { header("Location: index.php?view=login"); die(""); }

$idUser = $_SESSION["idUser"];

// SOLUTION RADICALE : Jointure directe et extraction via PDO pour éviter "parcoursResultat"
$requeteVisites = "SELECT v.*, m.nom, m.ville, m.pays 
                   FROM visites v 
                   INNER JOIN monuments m ON v.monument_id = m.id 
                   WHERE v.utilisateur_id = " . intval($idUser);

// On exécute la requête avec la fonction globale du framework
$listeBrute = SQLSelect($requeteVisites);

// Si c'est un objet PDOStatement, on extrait manuellement les lignes
$monumentsVus = [];
if ($listeBrute) {
    if (method_exists($listeBrute, 'fetchAll')) {
        $monumentsVus = $listeBrute->fetchAll(PDO::FETCH_ASSOC);
    } elseif (function_exists('parcoursResultat')) {
        $monumentsVus = parcoursResultat($listeBrute);
    } else {
        // Sécurité si le framework renvoie déjà un tableau
        $monumentsVus = $listeBrute;
    }
}

// Calculs de progression
$userInfos = SQLgetUtilisateurById($idUser);
$objectif = $userInfos['objectif'] ?? 50;
$totalVisites = SQLcompterVisites($idUser);
$pourcentage = ($objectif > 0) ? min(($totalVisites / $objectif) * 100, 100) : 0;
?>

<div class="main-interface">
    <h1>Monu Quest - Visités</h1>
    <div class="full-content-layout">
        <div class="sidebar-full">
            <h3>Progression</h3>
            <div class="progress-container">
                <div class="progress-bar" style="width: <?php echo $pourcentage; ?>%; height: 10px; background: #27ae60;"></div>
            </div>
            <p><strong><?php echo $totalVisites; ?></strong> / <?php echo $objectif; ?> monuments</p>
            
            <div class="tabs-nav">
                <a href="index.php?view=accueil" class="tab-link">CARTE</a>
                <a href="index.php?view=vus" class="tab-link active">VUS</a>
                <a href="index.php?view=avoir" class="tab-link">À VOIR</a>
                <a href="index.php?view=classement" class="tab-link">CLASSEMENT</a>
            </div>
        </div>

        <div class="tab-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h2 style="margin:0;">Mes Monuments Visités</h2>
                <?php if (!empty($monumentsVus)): ?>
                    <button onclick="alternerTri('table-visites')" style="background:#000000; color:white; border:1px solid #263d59; padding:8px 14px; border-radius:6px; cursor:pointer; font-size:0.85rem; font-weight:500; transition: background 0.2s;">
                        Trier par : Pays
                    </button>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($monumentsVus) && is_array($monumentsVus)): ?>
            <table id="table-visites">
                <thead>
                    <tr>
                        <th>Monument</th>
                        <th>Ville</th>
                        <th>Pays</th>
                        <th>Note / Avis</th>
                        <th>Commentaire</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monumentsVus as $m): ?>
                    <tr>
                        <td class="classe-monument"><?= htmlspecialchars($m['nom'] ?? 'Monument inconnu') ?></td>
                        <td><?= htmlspecialchars($m['ville'] ?? '-') ?></td>
                        <td class="classe-pays"><?= htmlspecialchars($m['pays'] ?? '-') ?></td>
                        <td>
                            <?= ($m['note'] !== null) ? str_repeat('⭐', $m['note']) . ' (' . $m['note'] . '/5)' : 'Non noté' ?>
                        </td>
                        <td>
                            <?= !empty($m['commentaire']) ? htmlspecialchars($m['commentaire']) : '<span style="color:#8a9bb5;font-style:italic;">—</span>' ?>
                        </td>
                        <td><?= $m['date_visite'] ? date('d/m/Y', strtotime($m['date_visite'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>Aucun monument visité pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let typeTriActuel = 'pays';

function alternerTri(idTableau) { // trier les élements
    const table = document.getElementById(idTableau);
    if (!table) return;
    const tbody = table.querySelector("tbody");
    const lignes = Array.from(tbody.querySelectorAll("tr"));
    const bouton = event.target;

    lignes.sort((ligneA, ligneB) => {
        let selecteur = typeTriActuel === 'pays' ? '.classe-pays' : '.classe-monument';
        const cellA = ligneA.querySelector(selecteur);
        const cellB = ligneB.querySelector(selecteur);
        if (!cellA || !cellB) return 0;

        return cellA.textContent.trim().localeCompare(cellB.textContent.trim());
    });

    tbody.innerHTML = "";
    lignes.forEach(ligne => tbody.appendChild(ligne));

    if (typeTriActuel === 'pays') {
        typeTriActuel = 'monument';
        bouton.textContent = "Trier par : Monument";
    } else {
        typeTriActuel = 'pays';
        bouton.textContent = "Trier par : Pays";
    }
}
</script>