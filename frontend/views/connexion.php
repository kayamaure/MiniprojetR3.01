<?php
// connexion.php (in frontend/views)
// We no longer use sessions here because authentication is handled via the API.
include 'header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <!-- Updated path for the stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>

        <!-- Container for displaying error messages -->
        <div id="error-message" style="color: red;"></div>

        <!-- Login form -->
        <form id="login-form">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" name="nom_utilisateur" id="nom_utilisateur" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required>

            <button type="submit" class="btn-submit">Se connecter</button>
        </form>

        <!-- Link to registration page -->
        <p class="create-account-container">
            Pas encore de compte ? <a href="inscription.php" class="btn btn-create-compte">Créer un compte</a>
        </p>
    </div>

    <script>
        // Attach a submit event listener to the login form
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Retrieve user input
            const username = document.getElementById('nom_utilisateur').value;
            const password = document.getElementById('mot_de_passe').value;

            // Send a POST request to the login endpoint
            const response = await fetch('http://localhost/MiniprojetR3.01/api-auth/index.php?action=login', {
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
                // Display error message
                document.getElementById('error-message').innerText = data.error || 'Erreur lors de la connexion.';
            }
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
