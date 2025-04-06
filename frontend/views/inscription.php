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

        .message {
    display: none;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    font-weight: bold;
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
        <div id="message" class="message"></div>     
        <form id="inscription-form">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" name="username" id="username" required 
                   minlength="3" maxlength="50">
            <p class="info-message">Le nom d'utilisateur doit contenir entre 3 et 50 caractères.</p>

            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required minlength="6">
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

    const messageBox = document.getElementById('message');
    const loader = document.getElementById('loader');

    function showMessage(text, isSuccess = false) {
        messageBox.textContent = text;
        messageBox.className = 'message ' + (isSuccess ? 'success' : 'error');
        messageBox.style.display = 'block';
    }

    document.getElementById('inscription-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        messageBox.style.display = 'none';
        loader.style.display = 'block';

        try {
            const formData = new FormData(this);
            const userData = Object.fromEntries(formData.entries());

            const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-auth/public/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(userData)
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Compte créé avec succès ! Redirection...', true);
                setTimeout(() => {
                    window.location.href = 'connexion.php?registered=true';
                }, 2000);
            } else {
                showMessage(data.message || data.error || 'Erreur lors de l\'inscription.');
            }

        } catch (error) {
            console.error('Erreur:', error);
            showMessage('Erreur de connexion au serveur.');
        } finally {
            loader.style.display = 'none';
        }
    });

    // Validation en temps réel du nom d'utilisateur
    document.getElementById('username').addEventListener('input', function(e) {
        const value = this.value;

        if (value.length < 3) {
            showMessage('Le nom d\'utilisateur doit contenir au moins 3 caractères.');
        } else if (value.length > 50) {
            showMessage('Le nom d\'utilisateur ne peut pas dépasser 50 caractères.');
        } else if (!/^[a-zA-Z0-9_-]+$/.test(value)) {
            showMessage('Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, tirets et underscores.');
        } else {
            messageBox.style.display = 'none';
        }
    });

    // Validation en temps réel du mot de passe
    document.getElementById('password').addEventListener('input', function(e) {
        const value = this.value;

        if (value.length < 6) {
            showMessage('Le mot de passe doit contenir au moins 6 caractères.');
        } else {
            messageBox.style.display = 'none';
        }
    });
</script>

    </script>
</body>
</html>
