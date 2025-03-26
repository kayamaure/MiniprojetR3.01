<?php require_once 'header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Créer un Compte</h2>
        <div id="error-message" style="color: red;"></div>
        
        <form id="inscription-form">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" name="nom_utilisateur" id="nom_utilisateur" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required>

            <button type="submit" class="btn btn-add">Créer le Compte</button>
            <a href="connexion.php" class="btn btn-back">Retour</a>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('inscription-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Réinitialiser le message d'erreur
                const errorElement = document.getElementById('error-message');
                errorElement.textContent = '';
                
                try {
                    const formData = new FormData(this);
                    const userData = {};
                    
                    // Convertir FormData en objet
                    for (const [key, value] of formData.entries()) {
                        userData[key] = value;
                    }
                    
                    const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-auth/index.php?action=register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(userData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Rediriger vers la page de connexion
                        window.location.href = 'connexion.php';
                    } else {
                        errorElement.textContent = data.error || 'Erreur lors de l\'inscription.';
                    }
                } catch (error) {
                    console.error('Error during registration:', error);
                    errorElement.textContent = 'Erreur lors de l\'inscription.';
                }
            });
        });
    </script>
</body>
</html>
