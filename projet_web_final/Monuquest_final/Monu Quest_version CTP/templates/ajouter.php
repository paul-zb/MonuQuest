<?php
include_once "libs/maLibForms.php";
// Protection de page
if (!isset($_SESSION['idUser'])) { header("Location: index.php?view=login"); die(""); }

// Assets pour la carte Leaflet
echo '<link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch@3.11.0/dist/geosearch.css"/>';
echo '<script src="https://unpkg.com/leaflet-geosearch@3.11.0/dist/geosearch.umd.js"></script>';
?>

<div class="main-interface">
    <div class="dashboard-layout">
        <div class="sidebar">
            <h3>Ajouter un monument</h3>
            <?php 
            mkForm("controleur.php", "POST"); 
            echo "<input type='hidden' name='action' value='AjouterMonument'>";
            
            echo "<div class='form-group'><label>Nom :</label>"; mkInput("text", "nom", "", "required"); echo "</div>";
            echo "<div class='form-group'><label>Pays :</label>"; mkInput("text", "pays", "", "required"); echo "</div>";
            echo "<div class='form-group'><label>Ville :</label>"; mkInput("text", "ville", "", "required"); echo "</div>";
            
            // Champs GPS remplis automatiquement par clic sur la carte
            echo "<div style='display:flex; gap:10px;'>";
            echo "<div class='form-group' style='flex:1;'><label>Lat</label><input type='text' id='lat' name='lat' readonly required></div>";
            echo "<div class='form-group' style='flex:1;'><label>Lng</label><input type='text' id='lng' name='lng' readonly required></div>";
            echo "</div>";

            // Liste déroulante des catégories
            echo "<div class='form-group'><label>Catégorie :</label><select name='categorie_id' required style='width:100%; padding:8px; border: 1px solid #ccc; border-radius: 5px; font-size:15px;'>";
            foreach(SQLlisterCategories() as $cat) {
                echo "<option value='".$cat['id']."'>".$cat['nom']."</option>";
            }
            echo "</select></div>";

            // =========================================================================
            // STATUT DE LA VISITE
            // =========================================================================
            echo "<div class='form-group' style='margin-top:15px; margin-bottom: 15px;'>";
            echo "<label style='font-weight: bold; display: block; margin-bottom: 5px;'>Statut du monument :</label>";
            
            // Premier bouton : À visiter (coché par défaut)
            echo "<label style='font-weight:normal; margin-right:20px; cursor: pointer;'>";
            echo "<input type='radio' name='statut_visite' value='non_visite' checked onchange='toggleChampsVisite()'> ";
            echo "Je n'ai pas encore visité (À voir)";
            echo "</label><br>";
            
            // Deuxième bouton : Déjà visité
            echo "<label style='font-weight:normal; cursor: pointer;'>";
            echo "<input type='radio' name='statut_visite' value='deja_visite' onchange='toggleChampsVisite()'> ";
            echo "J'ai déjà visité";
            echo "</label>";
            echo "</div>";

            // BLOC DE NOTATION & COMMENTAIRE (Masqué par défaut)
            echo "<div id='blocVisite' style='display:none; border-left: 3px solid #3498db; padding-left: 15px; margin-bottom: 20px; background-color: #f9f9f9; padding-top: 10px; padding-bottom: 10px; border-radius: 0 5px 5px 0;'>";
                
                // Le sélecteur de Note (de 1 à 5)
                echo "<div class='form-group'>";
                echo "<label>Ma note :</label>";
                echo "<select name='note' style='width:100%; padding:8px; border: 1px solid #ccc; border-radius: 5px;'>";
                echo "<option value=''>Choisir une note (optionnel)</option>";
                for($i=5; $i>=1; $i--) { 
                    echo "<option value='$i'>$i / 5</option>"; 
                }
                echo "</select>";
                echo "</div>";

                // Le champ Commentaire / Souvenir
                echo "<div class='form-group' style='margin-bottom: 0;'>";
                echo "<label>Mon souvenir / Commentaire :</label>";
                echo "<textarea name='description' style='width:100%; border-radius:5px; padding:8px; height:80px; box-sizing: border-box; border: 1px solid #ccc; font-family:inherit; font-size:15px;' placeholder='Décrivez votre visite...'></textarea>";
                echo "</div>";

            echo "</div>";
            // =========================================================================
            
            // Utilisation de la classe universelle .btn-submit
            mkInput("submit", "action_submit", "Ajouter le monument", "class='btn-submit'");
            endForm(); 
            ?>
        </div>

        <div class="map-view">
            <div id="map" style="height: 100%; width: 100%;"></div>
        </div>
    </div>
</div>

<script>
    // Fonction JavaScript pour afficher/masquer dynamiquement le bloc Note/Commentaire
    function toggleChampsVisite() {
        var statut = document.querySelector('input[name="statut_visite"]:checked').value;
        var blocVisite = document.getElementById('blocVisite');
        
        if (statut === 'deja_visite') {
            blocVisite.style.display = 'block';
        } else {
            blocVisite.style.display = 'none';
        }
    }

    // Initialisation de la carte Leaflet
    var map = L.map('map').setView([46.603354, 1.888334], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    const provider = new window.GeoSearch.OpenStreetMapProvider();
    const search = new window.GeoSearch.GeoSearchControl({ provider: provider, style: 'bar', showMarker: false });
    map.addControl(search);

    var marker;
    function setCoordinates(lat, lng) {
        document.getElementById('lat').value = lat.toFixed(6);
        document.getElementById('lng').value = lng.toFixed(6);
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
    }

    map.on('click', (e) => setCoordinates(e.latlng.lat, e.latlng.lng));
    map.on('geosearch/showlocation', (result) => setCoordinates(result.location.y, result.location.x));
</script>