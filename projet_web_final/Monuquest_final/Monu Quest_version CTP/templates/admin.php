<?php
// ============================================================
// PAGE ADMIN — Gestion des monuments globaux (est_custom = 0)
// ============================================================
include_once "libs/maLibForms.php"; //inclus la lib pour pouvoir utiliser mkTable

// Protection : connecté + is_admin = 1
if (!isset($_SESSION['idUser'])) {
    header("Location: index.php?view=login"); die("");
}
$adminInfos = SQLgetUtilisateurById($_SESSION['idUser']);
if (empty($adminInfos['is_admin'])) {
    header("Location: index.php?view=accueil"); die("");
}

// récupère toutes les données nécessaires au bon affichage de la vue admin
$categories = SQLlisterCategories();
$monuments  = SQLlisterTousMonumentsGlobaux();
$msg        = valider("msg", "GET") ? htmlspecialchars(valider("msg", "GET")) : "";
$msgType    = valider("type", "GET") === "ok" ? "success" : "error";

// Assets Leaflet GeoSearch pour la mini-carte du formulaire
echo '<link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch@3.11.0/dist/geosearch.css"/>';
echo '<script src="https://unpkg.com/leaflet-geosearch@3.11.0/dist/geosearch.umd.js"></script>';
?>

<div class="admin-wrap">

    <div class="admin-header">
        <h1>Administration — Monuments globaux</h1>
    </div>

    <?php if ($msg): ?>
    <p class="flash <?= $msgType ?>"><?= $msg ?></p>
    <?php endif; ?>

    <div class="admin-grid">

        <!-- ===== FORMULAIRE AJOUT ===== -->
        <div class="admin-card">
            <h2>Ajouter un monument global</h2>

            <!-- Mini-carte : cliquer pour remplir lat/lng automatiquement -->
            <div id="admin-map" style="height:220px; margin-bottom:15px;"></div>

            <?php mkForm("controleur.php", "POST"); ?>
            <input type="hidden" name="action" value="AdminAjouterMonument">

            <div class="form-group">
                <label>Nom *</label>
                <?php mkInput("text", "nom", "", "required placeholder='ex: Tour Eiffel'"); ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Pays *</label>
                    <?php mkInput("text", "pays", "", "required placeholder='France'"); ?>
                </div>
                <div class="form-group">
                    <label>Ville</label>
                    <?php mkInput("text", "ville", "", "placeholder='Paris'"); ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Latitude *</label>
                    <input type="text" id="admin-lat" name="lat" readonly required placeholder="cliquer sur la carte">
                </div>
                <div class="form-group">
                    <label>Longitude *</label>
                    <input type="text" id="admin-lng" name="lng" readonly required placeholder="cliquer sur la carte">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Catégorie *</label>
                    <?php mkSelect("categorie_id", $categories, "id", "nom"); ?>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="2" placeholder="Courte description..."></textarea>
            </div>

            <?php mkInput("submit", "action_submit", "Ajouter à la carte globale", "class='btn-submit'"); ?>
            <?php endForm(); ?>
        </div>

        <!-- ===== LISTE MONUMENTS ===== -->
        <div class="admin-card">
            <h2>
                Monuments globaux
                <small>(<?= count($monuments) ?>)</small>
            </h2>

            <?php if (count($monuments) < 50): ?>
            <p class="flash error">
                Attention : il ne reste que <strong><?= count($monuments) ?></strong> monuments globaux,
                ce qui est inférieur à l'objectif par défaut de 50.
            </p>
            <?php endif; ?>

            <?php
            // Préparer les données pour mkTable : on ajoute une colonne "Action" avec le bouton supprimer
            $tableData = [];
            foreach ($monuments as $m) {
                $tableData[] = [
                    'Nom'        => htmlspecialchars($m['nom']),
                    'Ville'      => htmlspecialchars($m['ville'] ?? '-'),
                    'Pays'       => htmlspecialchars($m['pays']),
                    'Catégorie'  => htmlspecialchars($m['nom_categorie'] ?? '-'),
                    'Action'     => '<button class="btn-delete" onclick="confirmerSuppression('
                                    . $m['id'] . ', \'' . addslashes(htmlspecialchars($m['nom'])) . '\')">'
                                    . 'Supprimer</button>',
                ];
            }
            mkTable($tableData, ['Nom', 'Ville', 'Pays', 'Catégorie', 'Action']);
            ?>
        </div>

    </div>
</div>

<!-- Formulaire caché pour la suppression (envoi POST propre) -->
<form id="form-delete" action="controleur.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="AdminSupprimerMonument">
    <input type="hidden" name="idMon" id="delete-id">
</form>

<script>
// Initialisation de la mini-carte Leaflet avec barre de recherche GeoSearch
var adminMap = L.map('admin-map').setView([46.6, 2.3], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(adminMap);

var adminProvider = new window.GeoSearch.OpenStreetMapProvider();
adminMap.addControl(new window.GeoSearch.GeoSearchControl({
    provider: adminProvider,
    style: 'bar',
    showMarker: false
}));

// Pose un marqueur et remplit les champs lat/lng au clic ou après une recherche
var adminMarker;
function setAdminCoords(lat, lng) {
    document.getElementById('admin-lat').value = lat.toFixed(6);
    document.getElementById('admin-lng').value = lng.toFixed(6);
    if (adminMarker) adminMap.removeLayer(adminMarker);
    adminMarker = L.marker([lat, lng]).addTo(adminMap);
}
adminMap.on('click', function(e) { setAdminCoords(e.latlng.lat, e.latlng.lng); });
adminMap.on('geosearch/showlocation', function(r) { setAdminCoords(r.location.y, r.location.x); });

// Demande confirmation avant de soumettre le formulaire de suppression
function confirmerSuppression(id, nom) {
    if (confirm('Supprimer "' + nom + '" ? Cette action est irréversible.')) {
        document.getElementById('delete-id').value = id;
        document.getElementById('form-delete').submit();
    }
}
</script>