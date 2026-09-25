<?php
include_once("libs/maLibForms.php");
?>


<div class="login-container">
    <div class="form-card">
        <h2>Connexion à Monu'Quest</h2>
        
        <?php
        // Ton code PHP existant pour mkForm ou messages d'erreur...
        mkForm("controleur.php", "POST");
        echo '<input type="hidden" name="action" value="Connexion">';
        
        echo '<div class="form-group">';
        echo '<label for="email">Adresse Email</label>';
        mkInput("text", "email", "", "placeholder='exemple@domaine.com' required");
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="passe">Mot de passe</label>';
        mkInput("password", "passe", "", "placeholder='••••••••' required");
        echo '</div>';
        
        // On lui donne la classe .btn-submit personnalisée
        mkInput("submit", "action_submit", "Se connecter", "class='btn-submit'");
        
        endForm();
        ?>
        
        <div class="form-footer-link">
            Pas encore inscrit ? <a href="index.php?view=inscription">Créer un compte</a>
        </div>
    </div>
</div>