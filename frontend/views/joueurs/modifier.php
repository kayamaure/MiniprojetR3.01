<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Joueur</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
    <?php 
    // Vérification de l'authentification JWT
    include '../../views/header.php';
    ?>
    
    <div class="table-container">
        <h1>Modifier le Joueur</h1>
        
        <!-- Message d'erreur -->
        <div id="error-message" style="color: red;"></div>
        
        <form id="joueur-form">
            <input type="hidden" id="numero_licence" name="numero_licence">
            
            <label for="nom">Nom :</label>
            <input type="text" name="nom" id="nom" required><br>

            <label for="prenom">Prénom :</label>
            <input type="text" name="prenom" id="prenom" required><br>

            <label for="date_naissance">Date de Naissance :</label>
            <input type="date" name="date_naissance" id="date_naissance" required><br>

            <label for="taille">Taille (m) :</label>
            <input type="number" name="taille" id="taille" step="0.01" min="1.00" max="2.50" required><br>

            <label for="poids">Poids (kg) :</label>
            <input type="number" name="poids" id="poids" required><br>

            <label for="statut">Statut :</label>
            <select name="statut" id="statut" required>
                <option value="">Sélectionnez un statut</option>
                <option value="Actif">Actif</option>
                <option value="Blessé">Blessé</option>
                <option value="Suspendu">Suspendu</option>
                <option value="Inactif">Inactif</option>
            </select><br>
            
            <input type="submit" value="Modifier" class="btn btn-edit">
            <a href="index.php" class="btn btn-back">Retour</a>
        </form>
    </div>

    <?php include '../../views/footer.php'; ?>
    
    <script>
        // Vérifier l'authentification au chargement de la page
        document.addEventListener('DOMContentLoaded', async function() {
            const token = localStorage.getItem('authToken');
            if (!token) {
                // Rediriger vers la page de connexion si pas de token
                window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
                return;
            }
            
            // Récupérer l'ID du joueur depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const joueurId = urlParams.get('numero_licence');
            
            if (!joueurId) {
                document.getElementById('error-message').textContent = 'Numéro de licence non spécifié.';
                return;
            }
            
            // Récupérer les informations du joueur
            try {
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=joueur&numero_licence=${joueurId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const data = await response.json();
                
                if (data.error) {
                    document.getElementById('error-message').textContent = data.error;
                    return;
                }
                
                // Remplir le formulaire avec les données du joueur
                document.getElementById('numero_licence').value = data.numero_licence;
                document.getElementById('nom').value = data.nom;
                document.getElementById('prenom').value = data.prenom;
                document.getElementById('date_naissance').value = data.date_naissance;
                document.getElementById('taille').value = data.taille;
                document.getElementById('poids').value = data.poids;
                document.getElementById('statut').value = data.statut;
                
            } catch (error) {
                console.error('Error fetching joueur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la récupération des données du joueur.';
            }
            
            // Gestion du formulaire de modification
            document.getElementById('joueur-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                try {
                    const token = localStorage.getItem('authToken');
                    const formData = new FormData(this);
                    const joueurData = {};
                    
                    // Convertir FormData en objet
                    for (const [key, value] of formData.entries()) {
                        joueurData[key] = value;
                    }
                    
                    const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=modifier_joueur', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`
                        },
                        body: JSON.stringify(joueurData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Rediriger vers la liste des joueurs
                        window.location.href = 'index.php';
                    } else {
                        document.getElementById('error-message').textContent = data.error || 'Erreur lors de la modification du joueur.';
                    }
                } catch (error) {
                    console.error('Error updating joueur:', error);
                    document.getElementById('error-message').textContent = 'Erreur lors de la modification du joueur.';
                }
            });
        });
    </script>
</body>
</html>