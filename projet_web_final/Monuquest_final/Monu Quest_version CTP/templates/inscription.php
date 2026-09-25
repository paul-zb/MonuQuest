<?php
include_once("libs/maLibForms.php");

if (basename($_SERVER["PHP_SELF"]) != "index.php") {
    header("Location:../index.php?view=inscription");
    die("");
}
?>

<div class="login-container">
    <div class="form-card">
        <h2>Inscription à Monu Quest</h2>
        
        <?php
        if ($msg = valider("msg")) {
            echo '<p style="color: #e74c3c; background: #fdf2f2; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; text-align: center; font-weight: bold;">' . htmlspecialchars($msg) . '</p>';
        }

        mkForm("controleur.php", "POST");
        echo '<input type="hidden" name="action" value="Inscription">';
        
        echo '<div class="form-group">';
        echo '<label for="pseudo">Pseudo</label>';
        mkInput("text", "pseudo", "", "placeholder='Ex: Aventurier59' required");
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="email">Adresse Email</label>';
        mkInput("email", "email", "", "placeholder='exemple@domaine.com' required");
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="passe">Mot de passe</label>';
        mkInput("password", "passe", "", "placeholder='••••••••' required");
        echo '</div>';
        
        mkInput("submit", "action_submit", "Créer mon compte", "class='btn-submit'");
        
        endForm();
        ?>
        
        <div class="form-footer-link" style="margin-top: 15px; text-align: center;">
            Déjà inscrit ? <a href="index.php?view=login">Se connecter</a>
        </div>
    </div>
</div>