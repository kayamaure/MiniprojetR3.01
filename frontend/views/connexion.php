<?php
// connexion.php (in frontend/views)
// L'authentification est gérée via l'API avec des tokens
include 'header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>

        <div id="error-message" class="error-message"></div>

        <form id="login-form">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" name="nom_utilisateur" id="nom_utilisateur" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required>

            <button type="submit" class="btn-submit">Se connecter</button>
            <div class="loader" id="loader"></div>
            <p class="info-message">Note : La session expirera automatiquement après 15 minutes d'inactivité.</p>
        </form>

        <p class="create-account-container">
            Pas encore de compte ? <a href="inscription.php" class="btn btn-create-compte">Créer un compte</a>
        </p>
    </div>

    <!-- Inclusion du gestionnaire d'authentification -->
    <script src="../assets/js/auth.js"></script>
    <script>
        // Vérification de la connexion au chargement de la page
        if (authManager.isLoggedIn()) {
            window.location.href = 'dashboard.php';
        }

        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const errorElement = document.getElementById('error-message');
            const loader = document.getElementById('loader');
            errorElement.style.display = 'none';
            loader.style.display = 'block';

            try {
                const formData = new FormData(this);
                const userData = Object.fromEntries(formData.entries());

                const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-auth/index.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(userData)
                });

                const data = await response.json();

                if (data.success) {
                    // Sauvegarde du token avec expiration après 15 minutes
                    authManager.setToken(data.token);
                    window.location.href = 'dashboard.php';
                } else {
                    errorElement.textContent = data.error || 'Erreur lors de la connexion';
                    errorElement.style.display = 'block';
                }
            } catch (error) {
                errorElement.textContent = 'Erreur de connexion au serveur';
                errorElement.style.display = 'block';
                console.error('Erreur:', error);
            } finally {
                loader.style.display = 'none';
            }
            
            // Masquer les messages d'erreur précédents
            document.getElementById('error-message').innerText = '';

            // Retrieve user input
            const username = document.getElementById('nom_utilisateur').value;
            const password = document.getElementById('mot_de_passe').value;
            
            try {
                // Send a POST request to the login endpoint
                const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-auth/index.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nom_utilisateur: username,
                        mot_de_passe: password
                    })
                });
                
                // Parse the JSON response
                const data = await response.json();

                if (data.success) {
                    // Save the JWT token in local storage
                    localStorage.setItem('authToken', data.token);
                    // Redirect the user to the dashboard
                    window.location.href = 'dashboard.php';
                } else {
                    // Masquer l'indicateur de chargement
                    loader.style.display = 'none';
                    // Display error message
                    document.getElementById('error-message').innerText = data.error || 'Erreur lors de la connexion.';
                }
            } catch (error) {
                // Masquer l'indicateur de chargement
                loader.style.display = 'none';
                // Afficher l'erreur
                document.getElementById('error-message').innerText = 'Erreur de connexion au serveur. Veuillez réessayer.';
                console.error('Erreur:', error);
            }
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>

                    debugInfo.innerHTML = `<h4>Informations de performance</h4>
                        <p>Temps total de réponse: ${responseTime.toFixed(2)} ms</p>
                        <p>Temps total coûté serveur: ${(data.debug.total_time * 1000).toFixed(2)} ms</p>
                        <p>Temps de connexion à la base de données: ${(data.debug.db_connection_time * 1000).toFixed(2)} ms</p>
                        <p>Temps de vérification utilisateur: ${(data.debug.user_verification_time * 1000).toFixed(2)} ms</p>`;
                    
                    // Afficher les détails de vérification utilisateur si disponibles
                    if (data.debug.user_verification_details) {
                        const details = data.debug.user_verification_details;
                        debugInfo.innerHTML += `<p>Détails de vérification utilisateur:</p>
                            <ul>
                                <li>Préparation de la requête: ${(details.query_prep_time * 1000).toFixed(2)} ms</li>
                                <li>Exécution de la requête: ${(details.query_execute_time * 1000).toFixed(2)} ms</li>
                                <li>Récupération des données: ${(details.fetch_time * 1000).toFixed(2)} ms</li>
                                <li>Vérification du mot de passe: ${(details.password_verify_time * 1000).toFixed(2)} ms</li>
                            </ul>`;
                    }
                }

                if (data.success) {
                    // Save the JWT token in local storage
                    localStorage.setItem('authToken', data.token);
                    // Redirect the user to the dashboard
                    window.location.href = 'dashboard.php';
                } else {
                    // Masquer l'indicateur de chargement
                    loader.style.display = 'none';
                    // Display error message
                    document.getElementById('error-message').innerText = data.error || 'Erreur lors de la connexion.';
                }
            } catch (error) {
                // Masquer l'indicateur de chargement
                loader.style.display = 'none';
                // Afficher l'erreur
                document.getElementById('error-message').innerText = 'Erreur de connexion au serveur. Veuillez réessayer.';
                console.error('Erreur:', error);
            }
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
