<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php") {
    header("Location:../index.php");
    die("");
}
?>

</div></div><footer id="footer">
    <div class="container">
        <p class="credit">
            <?php if (valider("connecte", "SESSION")): ?>
                <span>Connecté en tant que <b><?= htmlspecialchars($_SESSION['pseudo']) ?></b></span>
                <a href="controleur.php?action=Logout">
                    Se déconnecter
                </a>
            <?php else: ?>
                <span>Non connecté</span>
                <a href="index.php?view=login">Se connecter</a>
            <?php endif; ?>
        </p>
        <span class="footer-brand">Monu Quest &copy; <?= date('Y') ?></span>
    </div>
</footer>

</body>
</html>