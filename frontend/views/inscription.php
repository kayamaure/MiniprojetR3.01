<?php require_once 'header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .loader {
            display: none;
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 30px;
            height: 30px;
            margin: 10px auto;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            background-color: rgba(255, 0, 0, 0.1);
            display: none;
        }

        .info-message {
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .password-requirements {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Créer un Compte</h2>
        <div id="error-message" class="error-message"></div>
        
        <form id="inscription-form">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" name="nom_utilisateur" id="nom_utilisateur" required 
                   minlength="3" maxlength="50" pattern="[a-zA-Z0-9_-]+">
            <p class="info-message">Le nom d'utilisateur doit contenir entre 3 et 50 caractères.</p>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required minlength="6">
            <p class="password-requirements">Le mot de passe doit contenir au moins 6 caractères.</p>

            <button type="submit" class="btn btn-add">Créer le Compte</button>
            <a href="connexion.php" class="btn btn-back">Retour</a>
            <div class="loader" id="loader"></div>
        </form>
    </div>
    
    <!-- Inclusion du gestionnaire d'authentification -->
    <script src="../assets/js/auth.js"></script>
    <script>
        // Vérification de la connexion au chargement de la page
        if (authManager.isLoggedIn()) {
            window.location.href = 'dashboard.php';
        }

        document.getElementById('inscription-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const errorElement = document.getElementById('error-message');
            const loader = document.getElementById('loader');
            errorElement.style.display = 'none';
            loader.style.display = 'block';
            
            try {
                const formData = new FormData(this);
                const userData = Object.fromEntries(formData.entries());
                
                const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-auth/index.php?action=register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(userData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirection vers la page de connexion avec un message de succès
                    window.location.href = 'connexion.php?registered=true';
                } else {
                    errorElement.textContent = data.message || data.error || 'Erreur lors de l\'inscription.';
                    errorElement.style.display = 'block';
                }
            } catch (error) {
                    console.error('Erreur:', error);
                    errorElement.textContent = 'Erreur de connexion au serveur.';
                    errorElement.style.display = 'block';
                } finally {
                    loader.style.display = 'none';
                }
            });

        // Validation en temps réel du nom d'utilisateur
        document.getElementById('nom_utilisateur').addEventListener('input', function(e) {
            const value = this.value;
            const errorElement = document.getElementById('error-message');
            
            if (value.length < 3) {
                errorElement.textContent = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
                errorElement.style.display = 'block';
            } else if (value.length > 50) {
                errorElement.textContent = 'Le nom d\'utilisateur ne peut pas dépasser 50 caractères.';
                errorElement.style.display = 'block';
            } else if (!/^[a-zA-Z0-9_-]+$/.test(value)) {
                errorElement.textContent = 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, tirets et underscores.';
                errorElement.style.display = 'block';
            } else {
                errorElement.style.display = 'none';
            }
        });

        // Validation en temps réel du mot de passe
        document.getElementById('mot_de_passe').addEventListener('input', function(e) {
            const value = this.value;
            const errorElement = document.getElementById('error-message');
            
            if (value.length < 6) {
                errorElement.textContent = 'Le mot de passe doit contenir au moins 6 caractères.';
                errorElement.style.display = 'block';
            } else {
                errorElement.style.display = 'none';
            }
        });
    </script>
</body>
</html>
