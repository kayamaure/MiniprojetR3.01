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
                <label for="nom_equipe_adverse">Nom de l'équipe adverse :</label>
                <input
                    type="text"
                    name="nom_equipe_adverse"
                    id="nom_equipe_adverse"
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
                <label for="lieu_de_rencontre">Lieu du match :</label>
                <select name="lieu_de_rencontre" id="lieu_de_rencontre" required>
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
    document.addEventListener('DOMContentLoaded', function () {
        const token = localStorage.getItem('authToken');
        if (!token) {
            window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
            return;
        }

        // Gestion du formulaire d'ajout
        document.getElementById('match-form').addEventListener('submit', async function (e) {
            e.preventDefault();

            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = '';
            errorDiv.style.color = 'red';

            const formData = new FormData(this);
            const matchData = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=matchs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(matchData)
                });

                const data = await response.json();

                if (data.success || data.message) {
                    errorDiv.style.color = 'green';
                    errorDiv.textContent = '✅ Match ajouté avec succès ! Redirection...';
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    errorDiv.textContent = data.error || 'Erreur lors de l\'ajout du match.';
                }

            } catch (error) {
                console.error('Error adding match:', error);
                errorDiv.textContent = 'Erreur lors de l\'ajout du match.';
            }
        });
    });
</script>

</body>

</html>