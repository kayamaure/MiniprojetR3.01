<?php 
// statistiques.php - Updated to use JWT token authentication
// We no longer use sessions as authentication is handled via JWT tokens

include '../../views/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
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

    <div class="container" id="statistics-content" style="display: none;">
        <h1>Statistiques</h1>
        <p>Choisissez le type de statistiques que vous souhaitez visualiser :</p>

        <div class="stat-buttons">
        <a href="statistiques_matchs.php" class="btn btn-stat">Statistiques des Matchs</a>
        <a href="statistiques_joueurs.php" class="btn btn-stat">Statistiques des Joueurs</a>
        <a href="../dashboard.php" class="btn btn-back">Retour</a>
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
                    window.location.href = '../connexion.php';
                }, 2000);
            } else {
                // User is authenticated, show statistics content
                document.getElementById('statistics-content').style.display = 'block';
            }
        });
    </script>
</body>

</html>