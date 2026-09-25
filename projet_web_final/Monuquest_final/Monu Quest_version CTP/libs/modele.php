<?php
include_once("maLibSQL.pdo.php");

/* ==========================================
   MODULE : UTILISATEURS
   ========================================== */

function SQLcreerUtilisateur($pseudo, $email, $motDePasse, $objectif = 50) {
  $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
  $SQL = "INSERT INTO utilisateurs(pseudo, email, mot_de_passe, objectif) 
          VALUES ('$pseudo', '$email', '$hash', '$objectif')";
  return SQLInsert($SQL);
}

function SQLconnexionUtilisateur($email, $motDePasse) {
  $SQL = "SELECT * FROM utilisateurs WHERE email='$email'";
  $res = parcoursRs(SQLSelect($SQL));
  if (count($res) > 0) {
    $user = $res[0];
    if (password_verify($motDePasse, $user['mot_de_passe']) || $motDePasse == $user['mot_de_passe']) {
      return $user;
    }
  }
  return false;
}

function SQLgetUtilisateurById($id) {
  $SQL = "SELECT id, pseudo, email, objectif, badge, is_admin FROM utilisateurs WHERE id='$id'";
  $res = parcoursRs(SQLSelect($SQL));
  return (count($res) > 0) ? $res[0] : false;
}

function SQLmettreAJourBadge($idUtilisateur, $badge) {
  $SQL = "UPDATE utilisateurs SET badge='$badge' WHERE id='$idUtilisateur'";
  return (SQLUpdate($SQL) >= 0);
}

function SQLmettreAJourObjectif($idUtilisateur, $nouvelObjectif) {
  $SQL = "UPDATE utilisateurs SET objectif='$nouvelObjectif' WHERE id='$idUtilisateur'";
  return (SQLUpdate($SQL) >= 0);
}

/* ==========================================
   MODULE : MONUMENTS
   ========================================== */

function SQLlisterMonumentsAvecStatut($idUtilisateur) {
  // CORRECTION : On sélectionne les monuments officiels de base (est_custom = 0)
  // OU UNIQUELEMENT ceux créés par l'utilisateur connecté (created_by = '$idUtilisateur')
  $SQL = "SELECT m.id, m.nom, m.pays, m.ville, m.latitude as lat, m.longitude as lng, 
                 m.emoji, m.categorie_id, m.est_custom, 
                 IF(v.utilisateur_id IS NOT NULL, 1, 0) as visite, v.note,
                 IF(e.utilisateur_id IS NOT NULL, 1, 0) as envie
          FROM monuments m 
          LEFT JOIN visites v ON m.id = v.monument_id AND v.utilisateur_id = '$idUtilisateur'
          LEFT JOIN liste_envies e ON m.id = e.monument_id AND e.utilisateur_id = '$idUtilisateur'
          WHERE m.est_custom = 0 OR m.created_by = '$idUtilisateur'
          ORDER BY m.nom ASC";
  return parcoursRs(SQLSelect($SQL));
}

function SQLgetMonumentById($id) {
  $SQL = "SELECT m.*, c.nom as nom_categorie 
          FROM monuments m 
          LEFT JOIN categories c ON m.categorie_id = c.id 
          WHERE m.id = '$id'";
  $res = parcoursRs(SQLSelect($SQL));
  return (count($res) > 0) ? $res[0] : false;
}

function SQLajouterMonumentCustom($nom, $pays, $ville, $lat, $lng, $emoji, $description, $categorie_id, $idCreateur) {
  $SQL = "INSERT INTO monuments(nom, pays, ville, latitude, longitude, emoji, description, categorie_id, est_custom, created_by) 
          VALUES ('$nom', '$pays', '$ville', '$lat', '$lng', '$emoji', '$description', '$categorie_id', 1, '$idCreateur')";
  return SQLInsert($SQL);
}

function JSONgetMonumentsEnJson($idUtilisateur) {
  return json_encode(SQLlisterMonumentsAvecStatut($idUtilisateur));
}

function SQLlisterCategories() {
  $SQL = "SELECT id, nom FROM categories ORDER BY nom ASC";
  return parcoursRs(SQLSelect($SQL));
}

function SQLlisterMonumentsClassesUtilisateur($idUtilisateur) {
    $SQL = "SELECT m.id, m.nom, m.ville, m.emoji, v.note, v.commentaire
            FROM monuments m
            INNER JOIN visites v ON m.id = v.monument_id
            WHERE v.utilisateur_id = '$idUtilisateur' AND v.note IS NOT NULL
            ORDER BY v.note DESC, m.nom ASC";
    return parcoursRs(SQLSelect($SQL));
}

/* ==========================================
   MODULE : VISITES
   ========================================== */

function SQLajouterVisite($idUtilisateur, $idMonument, $noteSQL, $description) {
  // $noteSQL contient déjà la valeur nettoyée par le contrôleur (ex: '5' ou NULL)
  // On utilise tes vraies colonnes : utilisateur_id, monument_id, note, commentaire
  $SQL = "INSERT INTO visites(utilisateur_id, monument_id, note, commentaire, date_visite) 
          VALUES ('$idUtilisateur', '$idMonument', $noteSQL, '" . addslashes($description) . "', NOW())";
  return SQLInsert($SQL);
}

function SQLmettreAJourVisite($idUtilisateur, $idMonument, $note, $commentaire, $dateVisite) {
  $SQL = "UPDATE visites 
          SET note = '$note', commentaire = '$commentaire', date_visite = '$dateVisite' 
          WHERE utilisateur_id = '$idUtilisateur' AND monument_id = '$idMonument'";
  return (SQLUpdate($SQL) >= 0);
}

function SQLsupprimerVisite($idUtilisateur, $idMonument) {
  $SQL = "DELETE FROM visites WHERE utilisateur_id = '$idUtilisateur' AND monument_id = '$idMonument'";
  return (SQLDelete($SQL) >= 0);
}

function SQLgetVisitesUtilisateur($idUtilisateur) {
  $SQL = "SELECT v.monument_id as id_monument, m.nom, m.emoji, v.note, v.commentaire, v.date_visite 
          FROM visites v 
          JOIN monuments m ON v.monument_id = m.id 
          WHERE v.utilisateur_id = '$idUtilisateur' 
          ORDER BY v.date_visite DESC";
  return parcoursRs(SQLSelect($SQL));
}

function SQLcompterVisites($idUtilisateur) {
  $SQL = "SELECT COUNT(*) FROM visites WHERE utilisateur_id = '$idUtilisateur'";
  return (int) SQLGetChamp($SQL);
}

function SQLgetClassementNotes($idUtilisateur) {
  $SQL = "SELECT m.nom, m.emoji, v.note 
          FROM visites v 
          JOIN monuments m ON v.monument_id = m.id 
          WHERE v.utilisateur_id = '$idUtilisateur' AND v.note IS NOT NULL
          ORDER BY v.note DESC, m.nom ASC";
  return parcoursRs(SQLSelect($SQL));
}

/* ==========================================
   MODULE : LISTE D'ENVIES (À VOIR)
   ========================================== */

function SQLajouterEnvie($idUtilisateur, $idMonument) {
  // On insère le monument dans la liste d'envies de l'utilisateur
  // Note : Vérifie bien dans ta table 'liste_envies' si les colonnes s'appellent aussi utilisateur_id et monument_id
  $SQL = "INSERT INTO liste_envies(utilisateur_id, monument_id) 
          VALUES ('$idUtilisateur', '$idMonument')";
  return SQLInsert($SQL);
}

function SQLsupprimerEnvie($idUtilisateur, $idMonument) {
  // Supprime la ligne correspondante dans la table liste_envies
  // Ajuste utilisateur_id et monument_id si tes colonnes portent un autre nom
  $SQL = "DELETE FROM liste_envies 
          WHERE utilisateur_id = '$idUtilisateur' AND monument_id = '$idMonument'";
  return SQLDelete($SQL);
}

function SQLgetEnviesUtilisateur($idUtilisateur) {
  $SQL = "SELECT m.id, m.nom, m.ville, m.pays, m.emoji 
          FROM monuments m
          INNER JOIN liste_envies e ON m.id = e.monument_id
          WHERE e.utilisateur_id = '$idUtilisateur'";
  return parcoursRs(SQLSelect($SQL));
}

?>




<?php
// ============================================================
// ADMIN
// ============================================================

// Récupérer un utilisateur avec son champ is_admin
// REMPLACE SQLgetUtilisateurById dans modele.php par cette version :

// Lister tous les monuments globaux (est_custom = 0) avec leur catégorie
function SQLlisterTousMonumentsGlobaux() {
  $SQL = "SELECT m.id, m.nom, m.pays, m.ville, m.latitude, m.longitude,
                 m.emoji, m.description, m.categorie_id,
                 c.nom as nom_categorie
          FROM monuments m
          LEFT JOIN categories c ON m.categorie_id = c.id
          WHERE m.est_custom = 0
          ORDER BY m.nom ASC";
  return parcoursRs(SQLSelect($SQL));
}

// Ajouter un monument global (admin seulement, est_custom = 0)
function SQLadminAjouterMonument($nom, $pays, $ville, $lat, $lng, $emoji, $description, $categorieId) {
  $SQL = "INSERT INTO monuments(nom, pays, ville, latitude, longitude, emoji, description, categorie_id, est_custom, created_by)
          VALUES ('" . addslashes($nom) . "', '" . addslashes($pays) . "', '" . addslashes($ville) . "',
                  '$lat', '$lng', '" . addslashes($emoji) . "', '" . addslashes($description) . "',
                  '$categorieId', 0, NULL)";
  return SQLInsert($SQL);
}

// Supprimer un monument global (les visites/envies liées sont supprimées par CASCADE)
function SQLadminSupprimerMonument($idMonument) {
  $SQL = "DELETE FROM monuments WHERE id='$idMonument' AND est_custom = 0";
  return SQLDelete($SQL);
}