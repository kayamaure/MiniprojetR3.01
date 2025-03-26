<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Commentaire</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php 
    // Vérification de l'authentification JWT
    include '../../views/header.php';
    ?>
    
    <div class="table-container">
        <h1>Ajouter un Commentaire pour le Joueur</h1>

        <!-- Message d'erreur -->
        <div id="error-message" style="color: red;"></div>
        
        <!-- Formulaire pour ajouter un commentaire -->
        <form id="commentaire-form">
            <input type="hidden" id="numero_licence" name="numero_licence">
            
            <label for="sujet_commentaire">Sujet du Commentaire :</label>
            <input type="text" name="sujet_commentaire" id="sujet_commentaire" required><br>

            <label for="texte_commentaire">Texte du Commentaire :</label>
            <textarea name="texte_commentaire" id="texte_commentaire" rows="4" required></textarea><br>

            <input type="submit" value="Ajouter Commentaire" class="btn btn-add">
            <a href="../joueurs/index.php" class="btn btn-back">Retour à la Liste des Joueurs</a>
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
                return;
            }
            
            // Récupérer l'ID du joueur depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const numeroLicence = urlParams.get('numero_licence');
            
            if (!numeroLicence) {
                document.getElementById('error-message').textContent = 'Numéro de licence non spécifié.';
                return;
            }
            
            // Pré-remplir le champ caché avec le numéro de licence
            document.getElementById('numero_licence').value = numeroLicence;
            
            // Gestion du formulaire d'ajout de commentaire
            document.getElementById('commentaire-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                try {
                    const token = localStorage.getItem('authToken');
                    const formData = new FormData(this);
                    const commentaireData = {};
                    
                    // Convertir FormData en objet
                    for (const [key, value] of formData.entries()) {
                        commentaireData[key] = value;
                    }
                    
                    // Faire appel à l'API pour ajouter un commentaire
                    const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=ajouter_commentaire', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`
                        },
                        body: JSON.stringify(commentaireData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Rediriger vers la liste des joueurs
                        window.location.href = '../joueurs/index.php';
                    } else {
                        document.getElementById('error-message').textContent = data.error || 'Erreur lors de l\'ajout du commentaire.';
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    document.getElementById('error-message').textContent = 'Erreur lors de l\'ajout du commentaire.';
                }
            });
        });
    </script>
</body>
</html>
