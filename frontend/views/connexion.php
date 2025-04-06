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
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById('login-form');
        const errorMessage = document.getElementById('error-message');
        const loader = document.getElementById('loader');

        // Redirection si déjà connecté
        if (localStorage.getItem('authToken')) {
            window.location.href = 'dashboard.php';
            return;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorMessage.style.display = 'none';
            loader.style.display = 'block';

            const username = document.getElementById('nom_utilisateur').value;
            const password = document.getElementById('mot_de_passe').value;

            try {
                const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-auth/public/auth', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nom_utilisateur: username, mot_de_passe: password })
                });

                const data = await response.json();

                if (data.success) {
                    localStorage.setItem('authToken', data.token);
                    window.location.href = 'dashboard.php';
                } else {
                    errorMessage.textContent = data.error || 'Erreur lors de la connexion.';
                    errorMessage.style.display = 'block';
                }

                // Debug info (si disponible)
                if (data.debug) {
                    console.log("Infos debug : ", data.debug);
                    // Tu peux aussi afficher ça dans un élément HTML si besoin
                }

            } catch (err) {
                errorMessage.textContent = 'Erreur de connexion au serveur.';
                errorMessage.style.display = 'block';
                console.error('Erreur:', err);
            } finally {
                loader.style.display = 'none';
            }
        });
    });
</script>

    <?php include 'footer.php'; ?>
</body>
</html>
