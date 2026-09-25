<?php
include_once "libs/modele.php";
include_once "libs/maLibForms.php";

if (!isset($_SESSION['idUser'])) { header("Location: index.php?view=login"); die(""); }

$idUser = $_SESSION["idUser"];
$userInfos = SQLgetUtilisateurById($idUser);
$listeEnvies = SQLgetEnviesUtilisateur($idUser);

$objectif = $userInfos['objectif'] ?? 50;
$totalVisites = SQLcompterVisites($idUser);
$pourcentage = ($objectif > 0) ? min(($totalVisites / $objectif) * 100, 100) : 0;
?>

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

<div class="main-interface">
    <h1>Monu Quest - À visiter</h1>
    <div class="full-content-layout">

        <div class="sidebar-full">
            <h3>Progression</h3>
            <div class="progress-container">
                <div class="progress-bar" id="barre-fill" style="width: <?= $pourcentage ?>%;"></div>
            </div>
            <p><strong id="compteur-visites"><?= $totalVisites ?></strong> / <?= $objectif ?> monuments</p>

            <div class="tabs-nav">
                <a href="index.php?view=accueil"    class="tab-link">CARTE</a>
                <a href="index.php?view=vus"        class="tab-link">VUS</a>
                <a href="index.php?view=avoir"      class="tab-link active">À VOIR</a>
                <a href="index.php?view=classement" class="tab-link">CLASSEMENT</a>
            </div>
        </div>

        <div class="tab-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h4 style="margin:0;">Liste des monuments à découvrir</h4>
                <?php if (!empty($listeEnvies)): ?>
                    <button onclick="alternerTri('table-envies')" style="background:#000000; color:white; border:1px solid #263d59; padding:8px 14px; border-radius:6px; cursor:pointer; font-size:0.85rem; font-weight:500; transition: background 0.2s;">
                        Trier par : Pays
                    </button>
                <?php endif; ?>
            </div>

            <?php if (!empty($listeEnvies)): ?>
            <table id="table-envies">
                <thead>
                    <tr>
                        <th>Monument</th>
                        <th>Ville</th>
                        <th>Pays</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listeEnvies as $m): ?>
                    <tr id="ligne-<?= $m['id'] ?>">
                        <td class="classe-monument"><?= htmlspecialchars($m['nom']) ?></td>
                        <td><?= htmlspecialchars($m['ville'] ?? '-') ?></td>
                        <td class="classe-pays"><?= htmlspecialchars($m['pays'] ?? 'Non spécifié') ?></td>
                        <td>
                            <a href="#" onclick="ouvrirModal(<?= $m['id'] ?>, '<?= addslashes(htmlspecialchars($m['nom'])) ?>'); return false;"
                               class="btn-valider-visite">
                                ✏️ J'ai visité
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="empty-msg" id="msg-vide">Ta liste d'envies est vide pour le moment.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
var idEnCours = null;
var objectf  = <?= $objectif ?>;

let typeTriActuel = 'pays';

function alternerTri(idTableau) { // tri les élements
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

    var noteInput   = document.querySelector('input[name="modal-note"]:checked');
    var note        = noteInput ? noteInput.value : '';
    var commentaire = document.getElementById('modal-commentaire').value;

    var url = 'controleur.php?action=MarquerCommeVisite&idMon=' + idEnCours + '&ajax=1'
            + '&note=' + encodeURIComponent(note)
            + '&commentaire=' + encodeURIComponent(commentaire);

    fetch(url)
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (data.success) {
                var ligne = document.getElementById('ligne-' + idEnCours);
                if (ligne) ligne.remove();

                var newCount = data.totalVisites;
                var pct = Math.min((newCount / objectf) * 100, 100);
                document.getElementById('barre-fill').style.width = pct + '%';
                document.getElementById('compteur-visites').textContent = newCount;

                var tbody = document.querySelector('#table-envies tbody');
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                    document.getElementById('table-envies').style.display = 'none';
                    var msg = document.createElement('p');
                    msg.textContent = "Ta liste d'envies est vide pour le moment.";
                    msg.className = 'empty-msg';
                    document.querySelector('.tab-content').appendChild(msg);
                }

                fermerModal();
            }
        })
        .catch(function(err){ console.error('Erreur:', err); });
}
</script>