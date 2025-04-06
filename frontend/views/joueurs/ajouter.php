<?php  
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un joueur</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
    <?php 
    // Vérification de l'authentification JWT
    include '../../views/header.php';
    ?>
    
    <div class="table-container">
        <h1>Ajouter un nouveau joueur</h1>
        
        <!-- Message d'erreur -->
        <div id="error-message" style="color: red;"></div>
        
        <form id="joueur-form">
            <label for="numero_licence">Numéro de licence:</label>
            <input type="text" name="numero_licence" id="numero_licence" required><br>

            <label for="nom">Nom:</label>
            <input type="text" name="nom" id="nom" required><br>

            <label for="prenom">Prénom:</label>
            <input type="text" name="prenom" id="prenom" required><br>

            <label for="date_naissance">Date de naissance:</label>
            <input type="date" name="date_naissance" id="date_naissance" required><br>

            <label for="taille">Taille (m):</label>
            <input type="number" name="taille" id="taille" step="0.01" min="1.00" max="2.50" required><br>

            <label for="poids">Poids (kg):</label>
            <input type="number" name="poids" id="poids" required><br>

            <label for="statut">Statut:</label>
            <select name="statut" id="statut" required>
                <option value="">Sélectionnez un statut</option>
                <option value="Actif">Actif</option>
                <option value="Blessé">Blessé</option>
                <option value="Suspendu">Suspendu</option>
                <option value="Inactif">Inactif</option>
            </select><br>

            <input type="submit" value="Ajouter" class="btn btn-add">
            <a href="index.php" class="btn btn-back">Retour</a>
        </form>
    </div>

<<<<<<< Updated upstream
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
                    
                    // Utiliser la bonne URL avec le nouveau routage
                    const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=ajouter', {
                        method: 'POST',
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
                        document.getElementById('error-message').textContent = data.error || 'Erreur lors de l\'ajout du joueur.';
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    document.getElementById('error-message').textContent = 'Erreur lors de l\'ajout du joueur.';
                }
            });
        });
    </script>
=======
        <label for="poids">Poids (kg):</label>
        <input type="number" name="poids" id="poids" required><br>

        <label for="statut">Statut:</label>
        <select name="statut" id="statut" required>
            <option value="">Sélectionnez un statut</option>
            <option value="Actif">Actif</option>
            <option value="Blessé">Blessé</option>
            <option value="Suspendu">Suspendu</option>
            <option value="Inactif">Inactif</option>
        </select><br>

        <input type="submit" value="Ajouter" class="btn btn-add">
        <a href="index.php" class="btn btn-back">Retour</a>
    </form>
</div>

<?php include '../../views/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
        return;
    }

    ajouterJoueur(token);
});

// Gestion du formulaire avec le token en paramètre
function ajouterJoueur(token) {
    const form = document.getElementById('joueur-form');
    const errorDiv = document.getElementById('error-message');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorDiv.textContent = '';
        errorDiv.style.color = 'red';

        const formData = new FormData(form);
        const joueurData = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=joueurs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(joueurData)
            });

            const raw = await response.text();
            console.log("Réponse brute :", raw);

            let data;
            try {
                data = JSON.parse(raw);
            } catch (err) {
                errorDiv.textContent = 'Réponse invalide du serveur (non JSON).';
                console.error('Erreur JSON.parse() :', err);
                return;
            }

            if (data.success || data.message) {
                errorDiv.style.color = 'green';
                errorDiv.textContent = 'Joueur ajouté avec succès ! Redirection...';
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1500);
            } else {
                errorDiv.textContent = data.error || 'Erreur lors de l\'ajout du joueur.';
            }

        } catch (error) {
            console.error('Erreur fetch:', error);
            errorDiv.textContent = 'Erreur de connexion avec le serveur.';
        }
    });
}
</script>

>>>>>>> Stashed changes
</body>
</html>