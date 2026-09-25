<?php
if (!isset($_SESSION['idUser'])) { 
    header("Location: index.php?view=login"); 
    die(""); 
}

$idUser = $_SESSION["idUser"];
$userInfos = SQLgetUtilisateurById($idUser);
$objectif = isset($userInfos['objectif']) ? $userInfos['objectif'] : 50;
$totalVisites = SQLcompterVisites($idUser);
$pourcentage = ($objectif > 0) ? min(($totalVisites / $objectif) * 100, 100) : 0;
$monuments = SQLlisterMonumentsAvecStatut($idUser);
$monumentsJSON = json_encode($monuments, JSON_UNESCAPED_UNICODE);
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch@3.11.0/dist/geosearch.css"/>
<script src="https://unpkg.com/leaflet-geosearch@3.11.0/dist/geosearch.umd.js"></script>

<div id="modal-visite" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#1c2d42;border:1px solid #263d59;border-radius:12px;padding:2rem;width:100%;max-width:420px;box-shadow:0 8px 32px rgba(0,0,0,0.5);">
        <h3 id="modal-titre" style="font-family:'Playfair Display',serif;color:#c8963e;margin-bottom:1.2rem;font-size:1.2rem;text-align:center;"></h3>

        <label style="display:block;font-size:0.78rem;color:#8a9bb5;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Note (1 à 5)</label>
        <div style="display:flex;gap:8px;margin-bottom:1.2rem;">
            <?php for($i=1;$i<=5;$i++): ?>
            <label style="flex:1;text-align:center;cursor:pointer;">
                <input type="radio" name="modal-note" value="<?= $i ?>" style="display:none;">
                <span class="etoile" data-val="<?= $i ?>" style="display:block;font-size:1.4rem;filter:grayscale(1);transition:filter .15s;cursor:pointer;">⭐</span>
            </label>
            <?php endfor; ?>
        </div>

        <label style="display:block;font-size:0.78rem;color:#8a9bb5;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Commentaire (optionnel)</label>
        <textarea id="modal-commentaire" rows="3" placeholder="Décrivez votre visite..." style="width:100%;background:#0f1b2d;border:1px solid #263d59;border-radius:6px;color:#dde4ee;padding:8px 10px;font-family:'DM Sans',sans-serif;font-size:0.88rem;resize:vertical;margin-bottom:1.2rem;"></textarea>

        <div style="display:flex;gap:10px;">
            <button onclick="fermerModal()" style="flex:1;padding:10px;background:transparent;border:1px solid #263d59;border-radius:6px;color:#8a9bb5;cursor:pointer;font-size:0.88rem;">Annuler</button>
            <button onclick="confirmerVisite()" style="flex:1;padding:10px;background:#27ae60;border:none;border-radius:6px;color:white;cursor:pointer;font-size:0.88rem;font-weight:500;">✔ Confirmer</button>
        </div>
    </div>
</div>

<div class="main-container">
    <div class="sidebar-gauche">
        <h3>Progression</h3>
        <div class="barre-progres">
            <div class="barre-progres-fill" id="barre-fill" style="width: <?= $pourcentage ?>%;"></div>
        </div>
        <p id="compteur-visites" style="font-weight: bold; margin-bottom: 1.5rem;"><?= $totalVisites ?> / <?= $objectif ?> monuments</p>
        
        <p style="color: #8a9bb5; font-size: 0.9rem; line-height: 1.4; margin-bottom: 1.5rem;">
            Explorez la carte du monde et planifiez vos prochaines aventures culturelles.
        </p>

        <h4 style="font-size: 0.85rem; color: #c8963e; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.8rem;">Légende de la carte</h4>
        
        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #e74c3c; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></span>
                <span>Monuments de base</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #27ae60; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></span>
                <span>Monuments visités</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #8e44ad; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></span>
                <span>Monuments à voir</span>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div id="map" style="height:500px;width:100%;border-radius:8px;"></div>
    </div>
</div>

<script>
var map = L.map('map').setView([20, 10], 2);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// Barre de recherche GeoSearch
const provider = new window.GeoSearch.OpenStreetMapProvider(); // ouvre l'API pour pouvoir rechercher les adresses
const search = new window.GeoSearch.GeoSearchControl({
    provider: provider,
    style: 'bar',
    showMarker: false
});
map.addControl(search);

var monuments   = <?= $monumentsJSON ?>; // convertir en JSON pour ensuite créer les pins 
var objectif    = <?= $objectif ?>;
var markersMap  = {};
var idEnCours   = null;

function makeIcon(color) { // crée les pins (qui sont en fait des petites div en html)
    return L.divIcon({
        className: '',
        html: '<div style="width:14px;height:14px;border-radius:50%;background:' + color + ';border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.5);"></div>',
        iconSize: [14, 14], iconAnchor: [7, 7], popupAnchor: [0, -10]
    });
}

monuments.forEach(function(m) {
    if (!m.lat || !m.lng) return;

    var color = m.visite == 1 ? '#27ae60' : (m.envie == 1 ? '#8e44ad' : (m.est_custom == 1 ? '#8e44ad' : '#e74c3c'));
    var emoji = m.emoji || '📍';

    // --- SÉCURISATION DU BLOC DES BOUTONS DE LA POPUP ---
    var blocBoutons = '';
    
    if (m.visite == 1) {
        // Si déjà visité, on verrouille : pas d'action, juste le texte de confirmation vert
        blocBoutons = '<span style="display:block;text-align:center;margin-top:8px;color:#27ae60;font-size:0.85rem;font-weight:bold;">✔ Déjà exploré</span>';
    } else {
        // S'il n'est pas visité, on donne l'option d'enregistrer la visite
        blocBoutons = '<button onclick="ouvrirModal(' + m.id + ',\'' + m.nom.replace(/'/g,"\\'") + '\')" style="margin-top:10px;width:100%;padding:6px;background:#27ae60;color:white;border:none;border-radius:5px;cursor:pointer;font-size:0.85rem;">✔ Marquer comme visité</button>';
        
        // Et on n'autorise l'ajout à la liste "À voir" QUE s'il n'y est pas déjà
        if (m.envie != 1) {
            blocBoutons += '<a href="controleur.php?action=AjouterEnvie&idMon=' + m.id + '" style="display:block;margin-top:6px;padding:6px;background:#8e44ad;color:white;border-radius:5px;font-size:0.85rem;text-align:center;text-decoration:none;">🔖 À voir</a>';
        }
    }

    var popupContent = '<div style="font-family:sans-serif;min-width:160px;">'
        + '<div style="font-size:1.6rem;text-align:center;">' + emoji + '</div>'
        + '<b style="display:block;text-align:center;margin:4px 0;">' + m.nom + '</b>'
        + '<span style="display:block;text-align:center;color:#666;font-size:0.85rem;">' + (m.ville || '') + '</span>'
        + blocBoutons
        + '</div>';

    var marker = L.marker([m.lat, m.lng], { icon: makeIcon(color) })
        .addTo(map).bindPopup(popupContent);

    markersMap[m.id] = { marker: marker, data: m };
});

// --- MODALE ---
function ouvrirModal(idMonument, nomMonument) { //pour afficher la fenêtre pop up
    idEnCours = idMonument;
    document.getElementById('modal-titre').textContent = '📍 ' + nomMonument;
    document.getElementById('modal-commentaire').value = '';
    document.querySelectorAll('input[name="modal-note"]').forEach(function(r){ r.checked = false; });
    document.querySelectorAll('.etoile').forEach(function(e){ e.style.filter = 'grayscale(1)'; });
    var modal = document.getElementById('modal-visite');
    modal.style.display = 'flex';
}

function fermerModal() { //pour enlever la fenêtre pop up
    document.getElementById('modal-visite').style.display = 'none';
    idEnCours = null;
}

document.querySelectorAll('.etoile').forEach(function(el) {
    el.addEventListener('click', function() {
        var val = parseInt(this.dataset.val);
        document.querySelectorAll('input[name="modal-note"]').forEach(function(r){
            if (parseInt(r.value) === val) r.checked = true;
        });
        document.querySelectorAll('.etoile').forEach(function(e) {
            e.style.filter = parseInt(e.dataset.val) <= val ? 'none' : 'grayscale(1)';
        });
    });
});

document.getElementById('modal-visite').addEventListener('click', function(e) {
    if (e.target === this) fermerModal();
});

function confirmerVisite() { // pour cliquer sur le bouton "marqué comme visité"
    if (!idEnCours) return;

    var noteInput = document.querySelector('input[name="modal-note"]:checked');
    var note      = noteInput ? noteInput.value : '';
    var commentaire = document.getElementById('modal-commentaire').value;

    var url = 'controleur.php?action=MarquerCommeVisite&idMon=' + idEnCours + '&ajax=1'
            + '&note=' + encodeURIComponent(note)
            + '&commentaire=' + encodeURIComponent(commentaire);

    fetch(url)
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (data.success) {
                var entry = markersMap[idEnCours];
                if (entry) {
                    entry.marker.setIcon(makeIcon('#27ae60'));
                    // Modification dynamique suite au clic : on verrouille le popup immédiatement sans recharger
                    entry.marker.setPopupContent(
                        '<div style="font-family:sans-serif;min-width:160px;">'
                        + '<div style="font-size:1.6rem;text-align:center;">📍</div>'
                        + '<b style="display:block;text-align:center;margin:4px 0;">' + entry.data.nom + '</b>'
                        + '<span style="display:block;text-align:center;color:#666;font-size:0.85rem;">' + (entry.data.ville || '') + '</span>'
                        + '<span style="display:block;text-align:center;margin-top:8px;color:#27ae60;font-size:0.85rem;font-weight:bold;">✔ Monument visité</span>'
                        + '</div>'
                    );
                }
                var newCount = data.totalVisites;
                var pct = Math.min((newCount / objectif) * 100, 100);
                document.getElementById('barre-fill').style.width = pct + '%';
                document.getElementById('compteur-visites').textContent = newCount + ' / ' + objectif + ' monuments';
                fermerModal();
            }
        })
        .catch(function(err){ console.error('Erreur:', err); });
}
</script>