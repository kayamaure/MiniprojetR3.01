<?php
// dashboard.php - Updated to use JWT token authentication
// We no longer use sessions as authentication is handled via JWT tokens

include 'header.php';   
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .btn-stat {
            background-color: #12c2f3;
        }

        .btn-stat:hover {
            background-color: #22a1e6; /* Orange plus foncé */
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <!-- Container for authentication check message -->
    <div id="auth-message" style="display: none; color: red; text-align: center; margin-top: 20px;">
        Vous n'êtes pas connecté. Redirection vers la page de connexion...
    </div>

    <div class="table-container" id="dashboard-content" style="display: none;">
        <div class="dashboard-container">
            <h1>Bienvenue sur le Tableau de Bord</h1>
            <p>Bonjour, <span id="username"></span> ! Vous êtes connecté.</p>
            
            <a href="joueurs/index.php" class="btn btn-gestjou">Gestion des Joueurs</a>
            <a href="matchs/index.php" class="btn btn-gestmatch">Gestion des Matchs</a>
            <a href="statistiques/statistiques.php" class="btn btn-stat">Statistiques</a>
            <button id="logout-btn" class="btn btn-deco">Déconnexion</button>
        </div>
    </div>

    <script>
        // Check if user is authenticated
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('authToken');
            
            if (!token) {
                // User is not authenticated
                document.getElementById('auth-message').style.display = 'block';
                // Redirect to login page after a short delay
                setTimeout(() => {
                    window.location.href = 'connexion.php';
                }, 2000);
            } else {
                // User is authenticated, show dashboard content
                document.getElementById('dashboard-content').style.display = 'block';
                
                // Parse the JWT token to get user information
                try {
                    const payload = JSON.parse(atob(token.split('.')[1]));
                    document.getElementById('username').textContent = payload.nom_utilisateur || 'Utilisateur';
                } catch (e) {
                    console.error('Error parsing token:', e);
                    document.getElementById('username').textContent = 'Utilisateur';
                }
                
                // Handle logout
                document.getElementById('logout-btn').addEventListener('click', function() {
                    // Clear the token from localStorage
                    localStorage.removeItem('authToken');
                    // Redirect to login page
                    window.location.href = 'connexion.php';
                });
            }
        });
    </script>
</body>

</html>

<?php include 'footer.php'; ?>