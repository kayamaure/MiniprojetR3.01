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
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('authToken');
    const navLinks = document.getElementById('nav-links');
    const logoutBtn = document.getElementById('logout-btn');

    // Cacher la navigation si pas connecté
    if (!token && navLinks) {
        navLinks.style.display = 'none';
    }

    // Déconnexion : appeler l'API logout
    if (token && logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault();

            try {
                const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-auth/public/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success || response.ok) {
                    localStorage.removeItem('authToken');
                    window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
                } else {
                    alert(data.error || "Erreur lors de la déconnexion.");
                }
            } catch (error) {
                console.error("Erreur lors de la déconnexion :", error);
                alert("Erreur de communication avec le serveur.");
            }
        });
    }
});
</script>
