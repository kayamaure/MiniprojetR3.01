<?php
// statistiques_matchs.php - Updated to use JWT token authentication
// We no longer use sessions as authentication is handled via JWT tokens

include '../../views/header.php'; 
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Matchs</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
    <!-- Container for authentication check message -->
    <div id="auth-message" style="display: none; color: red; text-align: center; margin-top: 20px;">
        Vous n'êtes pas connecté. Redirection vers la page de connexion...
    </div>

    <div class="container" id="statistics-content" style="display: none;">
        <h1>Statistiques des Matchs</h1>

        <div id="match-stats-loading">Chargement des statistiques...</div>
        <div id="match-stats-content" style="display: none;">
            <h2>Nombre Total de Matchs</h2>
            <ul>
                <li><strong>Matchs Gagnés :</strong> <span id="matchs-gagnes">0</span></li>
                <li><strong>Matchs Nuls :</strong> <span id="matchs-nuls">0</span></li>
                <li><strong>Matchs Perdus :</strong> <span id="matchs-perdus">0</span></li>
            </ul>

            <h2>Pourcentages des Matchs</h2>
            <ul>
                <li><strong>Pourcentage Gagnés :</strong> <span id="pourcentage-gagnes">0.00</span>%</li>
                <li><strong>Pourcentage Nuls :</strong> <span id="pourcentage-nuls">0.00</span>%</li>
                <li><strong>Pourcentage Perdus :</strong> <span id="pourcentage-perdus">0.00</span>%</li>
            </ul>
        </div>

        <!-- Bouton de retour -->
        <div class="return-button">
            <a href="statistiques.php" class="btn btn-back">Retour</a>
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
                
                // Fetch match statistics from the API
                fetchMatchStatistics(token);
            }
        });
        
        // Function to fetch match statistics from the API
        async function fetchMatchStatistics(token) {
            try {
                const response = await fetch('http://localhost/MiniprojetR3.01/api-sports/index.php?action=match_statistics', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to fetch match statistics');
                }
                
                const data = await response.json();
                
                // Update the UI with the fetched statistics
                document.getElementById('matchs-gagnes').textContent = data.totaux.matchs_gagnes || '0';
                document.getElementById('matchs-nuls').textContent = data.totaux.matchs_nuls || '0';
                document.getElementById('matchs-perdus').textContent = data.totaux.matchs_perdus || '0';
                
                document.getElementById('pourcentage-gagnes').textContent = 
                    (data.pourcentages.pourcentage_gagnes || 0).toFixed(2);
                document.getElementById('pourcentage-nuls').textContent = 
                    (data.pourcentages.pourcentage_nuls || 0).toFixed(2);
                document.getElementById('pourcentage-perdus').textContent = 
                    (data.pourcentages.pourcentage_perdus || 0).toFixed(2);
                
                // Hide loading message and show content
                document.getElementById('match-stats-loading').style.display = 'none';
                document.getElementById('match-stats-content').style.display = 'block';
                
            } catch (error) {
                console.error('Error fetching match statistics:', error);
                document.getElementById('match-stats-loading').textContent = 
                    'Erreur lors du chargement des statistiques. Veuillez réessayer plus tard.';
            }
        }
    </script>
</body>

</html>