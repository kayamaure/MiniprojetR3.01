<?php
// Vérification de l'authentification JWT
$authToken = isset($_COOKIE['authToken']) ? $_COOKIE['authToken'] : null;

// Déterminer le chemin de base relatif en fonction de l'emplacement du script
$baseUrl = '/MiniprojetR3.01/frontend';
?>
<header class="main-header">
    <div class="logo">
        <a href="<?php echo $baseUrl; ?>/views/dashboard.php"><img src="<?php echo $baseUrl; ?>/assets/img/logo.PNG" alt="Logo" /></a>
    </div>

    <nav class="nav-links" id="nav-links">
        <!-- La navigation sera affichée par JavaScript si le token JWT est présent -->
        <a href="<?php echo $baseUrl; ?>/views/dashboard.php">Accueil</a>
        <a href="<?php echo $baseUrl; ?>/views/joueurs/index.php">Joueurs</a>
        <a href="<?php echo $baseUrl; ?>/views/matchs/index.php">Matchs</a>
        <a href="<?php echo $baseUrl; ?>/views/statistiques/statistiques.php">Statistiques</a>
        <a href="<?php echo $baseUrl; ?>/views/mon_compte.php" class="btn btn-account">Mon Compte</a>
        <a href="#" class="btn-logout" id="logout-btn">Déconnexion</a>
    </nav>
</header>

<script>
    // Vérifier si l'utilisateur est authentifié avec JWT
    document.addEventListener('DOMContentLoaded', function() {
        const token = localStorage.getItem('authToken');
        const navLinks = document.getElementById('nav-links');
        const logoutBtn = document.getElementById('logout-btn');
        
        // Afficher/masquer la navigation selon la présence du token
        if (!token) {
            // Masquer les liens de navigation si pas de token
            if (navLinks) {
                navLinks.style.display = 'none';
            }
        } else {
            // Configurer le bouton de déconnexion
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Supprimer le token JWT
                    localStorage.removeItem('authToken');
                    // Rediriger vers la page de connexion
                    window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
                });
            }
        }
    });
</script>