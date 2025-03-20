<?php 
// Vérification de l'authentification JWT
include '../../views/header.php'; 
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Match</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

    <div class="container">
        <h1>Ajouter un Match</h1>
        
        <!-- Message d'erreur -->
        <div id="error-message" style="color: red;"></div>
        
        <form id="match-form">
            <!-- Équipe adverse -->
            <div>
                <label for="nom_adversaire">Nom de l'équipe adverse :</label>
                <input
                    type="text"
                    name="nom_adversaire"
                    id="nom_adversaire"
                    placeholder="Ex: CF Exemple"
                    required>
            </div>

            <!-- Date et heure du match -->
            <div>
                <label for="date_match">Date du match :</label>
                <input
                    type="date"
                    name="date_match"
                    id="date_match"
                    required>

                <label for="heure_match">Heure du match :</label>
                <input
                    type="time"
                    name="heure_match"
                    id="heure_match"
                    required>
            </div>

            <!-- Lieu de rencontre -->
            <div>
                <label for="lieu_match">Lieu du match :</label>
                <select name="lieu_match" id="lieu_match" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="Domicile">Domicile</option>
                    <option value="Extérieur">Extérieur</option>
                </select>
            </div>

            <!-- Bouton de soumission -->
            <button type="submit" class="btn btn-add">Ajouter</button>
            <a href="index.php" class="btn btn-back">Retour</a>
        </form>
    </div>

    <?php include '../../views/footer.php'; ?>

    <script>
        // Vérifier l'authentification au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('authToken');
            if (!token) {
                // Rediriger vers la page de connexion si pas de token
                window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
            }
            
            // Gestion du formulaire d'ajout
            document.getElementById('match-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                try {
                    const token = localStorage.getItem('authToken');
                    const formData = new FormData(this);
                    const matchData = {};
                    
                    // Convertir FormData en objet
                    for (const [key, value] of formData.entries()) {
                        matchData[key] = value;
                    }
                    
                    const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=ajouter_match', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`
                        },
                        body: JSON.stringify(matchData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Rediriger vers la liste des matchs
                        window.location.href = 'index.php';
                    } else {
                        document.getElementById('error-message').textContent = data.error || 'Erreur lors de l\'ajout du match.';
                    }
                } catch (error) {
                    console.error('Error adding match:', error);
                    document.getElementById('error-message').textContent = 'Erreur lors de l\'ajout du match.';
                }
            });
        });
    </script>
</body>

</html>