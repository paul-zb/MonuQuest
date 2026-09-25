<?php
session_start();

include_once "libs/maLibUtils.php";
include_once "libs/maLibSQL.pdo.php";
include_once "libs/modele.php"; 

$qs = "";

if ($action = valider("action")) {
  ob_start();

  switch($action) {
    case 'Connexion':
      $email = valider("email");
      $passe = valider("passe");

      if ($email && $passe) {
        $user = SQLconnexionUtilisateur($email, $passe);
        if ($user) {
          $_SESSION["idUser"] = $user['id'];
          $_SESSION["pseudo"] = $user['pseudo'];
          $_SESSION["connecte"] = true;
          header("Location: index.php?view=accueil");
          die("");
        } else {
          $qs = "?view=login&msg=" . urlencode("Identifiant ou mot de passe incorrect");
        }
      } else {
        $qs = "?view=login&msg=" . urlencode("Champs incomplets");
      }
    break;

    case 'Inscription':
      $pseudo = valider("pseudo");
      $email = valider("email");
      $passe = valider("passe");

      if ($pseudo && $email && $passe) {
        $id = SQLcreerUtilisateur($pseudo, $email, $passe);
        if ($id) {
          $_SESSION["idUser"] = $id;
          $_SESSION["pseudo"] = $pseudo;
          $_SESSION["connecte"] = true;
          header("Location: index.php?view=accueil");
          die("");
        } else {
          $qs = "?view=inscription&msg=" . urlencode("Erreur d'inscription (Email déjà utilisé)");
        }
      } else {
        $qs = "?view=inscription&msg=" . urlencode("Veuillez remplir tous les champs");
      }
    break;

    case 'ModifierObjectif':
      if (isset($_SESSION["idUser"])) {
        $nouvelObjectif = valider("objectif");
        $currentView = valider("current_view"); 
        
        if ($nouvelObjectif && is_numeric($nouvelObjectif) && $nouvelObjectif > 0) {
          SQLmettreAJourObjectif($_SESSION["idUser"], $nouvelObjectif);
        }
      }
      $qs = "?view=" . ($currentView ? $currentView : "vus"); 
    break;

    case 'logout':
    case 'Logout':   
    case 'deconnexion':
      session_destroy(); 
      $qs = "?view=login&msg=" . urlencode("Déconnecté avec succès !"); 
    break;

    case 'AjouterMonument':
      if (isset($_SESSION["idUser"])) { //
        // 1. Récupération des données techniques du monument
        $nom = valider("nom"); //[cite: 12]
        $pays = valider("pays"); //[cite: 12]
        $ville = valider("ville"); //[cite: 12]
        $lat = valider("lat"); //[cite: 12]
        $lng = valider("lng"); //[cite: 12]
        $emoji = valider("emoji") ? valider("emoji") : "🗺️"; //[cite: 12]
        $desc = valider("description"); //[cite: 12]
        $catId = valider("categorie_id"); //[cite: 12]

        // Sécurité pour la clé étrangère de catégorie
        if (empty($catId)) {
            $catId = 1; 
        } //[cite: 12]

        // Création de la fiche du monument dans la base de données
        $idMonument = SQLajouterMonumentCustom($nom, $pays, $ville, $lat, $lng, $emoji, $desc, $catId, $_SESSION["idUser"]); //[cite: 12]

        // 2. Traitement selon le bouton radio sélectionné
        $statutVisite = valider("statut_visite"); 
        
        if ($statutVisite == "deja_visite") {
            // L'utilisateur a déjà visité le lieu : on récupère la note
            $note = valider("note");
            
            // Si aucune note n'est sélectionnée, on prépare le mot-clé NULL pour MySQL
            $noteSQL = (!empty($note)) ? "'$note'" : "NULL";
            
            // On appelle la fonction de visite avec la note et le commentaire ($desc)
            SQLajouterVisite($_SESSION["idUser"], $idMonument, $noteSQL, $desc); 
        } else {
            // L'utilisateur ne l'a pas encore visité : on l'ajoute à la liste d'envies
            SQLajouterEnvie($_SESSION["idUser"], $idMonument); //[cite: 12]
        }
        
        header("Location: index.php?view=accueil"); //[cite: 12]
        die(""); //[cite: 12]
      }
    break;

    case 'MarquerCommeVisite':
      if (isset($_SESSION["idUser"])) {
          $idUser = $_SESSION["idUser"];
          $idMonument = valider("idMon");
          $note = valider("note");
          $commentaire = valider("commentaire") ?? "";

          if ($idMonument) {
              SQLsupprimerEnvie($idUser, $idMonument);
              $noteSQL = (!empty($note) && is_numeric($note)) ? "'$note'" : "NULL";
              SQLajouterVisite($idUser, $idMonument, $noteSQL, $commentaire);
          }

          if (valider("ajax")) {
              $totalVisites = SQLcompterVisites($idUser);
              header('Content-Type: application/json');
              echo json_encode(['success' => true, 'totalVisites' => $totalVisites]);
              die("");
          }

          header("Location: index.php?view=vus");
          die("");
      }
    break;

    case 'AjouterEnvie':
    if (isset($_SESSION["idUser"])) {
        $idMonument = valider("idMon");
        if ($idMonument) {
            SQLajouterEnvie($_SESSION["idUser"], $idMonument);
        }
        header("Location: index.php?view=accueil");
        die("");
    }
  break;



   case 'AdminAjouterMonument':
      // Vérif admin
      if (!isset($_SESSION["idUser"])) { $qs = "?view=login"; break; }
      $adminInfos = SQLgetUtilisateurById($_SESSION["idUser"]);
      if (empty($adminInfos['is_admin'])) { $qs = "?view=accueil"; break; }
 
      $nom      = valider("nom");
      $pays     = valider("pays");
      $ville    = valider("ville") ?: "";
      $lat      = valider("lat");
      $lng      = valider("lng");
      $emoji    = valider("emoji") ?: "📍"; // on met des émojis mais on ne les afficheras pas dans les tableaux
      $desc     = valider("description") ?: "";
      $catId    = valider("categorie_id") ?: 1;
 
      if ($nom && $pays && $lat && $lng) {
          $id = SQLadminAjouterMonument($nom, $pays, $ville, $lat, $lng, $emoji, $desc, $catId);
          if ($id) {
              $qs = "?view=admin&msg=" . urlencode("Monument \"$nom\" ajouté avec succès !") . "&type=ok";
          } else {
              $qs = "?view=admin&msg=" . urlencode("Erreur lors de l'ajout.") . "&type=error";
          }
      } else {
          $qs = "?view=admin&msg=" . urlencode("Champs obligatoires manquants (nom, pays, coordonnées).") . "&type=error";
      }
    break;
 
    case 'AdminSupprimerMonument':
      // Vérif admin
      if (!isset($_SESSION["idUser"])) { $qs = "?view=login"; break; }
      $adminInfos = SQLgetUtilisateurById($_SESSION["idUser"]);
      if (empty($adminInfos['is_admin'])) { $qs = "?view=accueil"; break; }
 
      $idMon = valider("idMon");
      if ($idMon) {
          // Récupérer le nom avant suppression pour le message
          $mon = SQLgetMonumentById($idMon);
          $nomMon = $mon ? $mon['nom'] : "Monument #$idMon";
          $ok = SQLadminSupprimerMonument($idMon);
          if ($ok !== false) {
              $qs = "?view=admin&msg=" . urlencode("\"$nomMon\" supprimé avec succès.") . "&type=ok";
          } else {
              $qs = "?view=admin&msg=" . urlencode("Erreur lors de la suppression.") . "&type=error";
          }
      } else {
          $qs = "?view=admin&msg=" . urlencode("ID monument invalide.") . "&type=error";
      }
    break;


  }
}

header("Location: index.php" . $qs);
die("");
?>