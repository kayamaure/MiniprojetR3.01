<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Joueur</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php include '../../views/header.php'; ?>

<div class="table-container">
    <h1>Modifier le Joueur</h1>

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
document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('authToken');
    const errorDiv = document.getElementById('error-message');

    if (!token) {
        window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const joueurId = urlParams.get('numero_licence');

    if (!joueurId) {
        errorDiv.textContent = "Numéro de licence manquant dans l'URL.";
        return;
    }

    // Préremplir le formulaire avec les données du joueur
    chargerJoueur(joueurId, token);

    // Gérer la soumission du formulaire
    document.getElementById('joueur-form').addEventListener('submit', function (e) {
        e.preventDefault();
        modifierJoueur(token);
    });
});

// ✅ Fonction pour charger les données d’un joueur
async function chargerJoueur(numeroLicence, token) {
    const errorDiv = document.getElementById('error-message');

    try {
        const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=joueurs&numero_licence=${numeroLicence}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 403) {
            errorDiv.textContent = "Accès refusé. Token invalide ou expiré.";
            return;
        }

        const data = await response.json();
        console.log('Données joueur à modifier:', data);

        if (data && data.numero_licence) {
            document.getElementById('numero_licence').value = data.numero_licence;
            document.getElementById('nom').value = data.nom;
            document.getElementById('prenom').value = data.prenom;
            document.getElementById('date_naissance').value = data.date_naissance;
            document.getElementById('taille').value = data.taille;
            document.getElementById('poids').value = data.poids;
            document.getElementById('statut').value = data.statut;
        } else {
            errorDiv.textContent = "Joueur introuvable.";
        }

    } catch (error) {
        console.error('Erreur de chargement:', error);
        errorDiv.textContent = "Erreur lors du chargement du joueur.";
    }
}

// ✅ Fonction pour envoyer les modifications
async function modifierJoueur(token) {
    const errorDiv = document.getElementById('error-message');
    const form = document.getElementById('joueur-form');
    const formData = new FormData(form);
    const joueurData = Object.fromEntries(formData.entries());

    try {
        const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=joueurs&numero_licence=${joueurData.numero_licence}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(joueurData)
        });

        const data = await response.json();
        console.log('Résultat de la modification:', data);

        if (data.success || data.message) {
            errorDiv.style.color = 'green';
            errorDiv.textContent = "✅ Joueur modifié avec succès ! Redirection...";
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
        } else {
            errorDiv.style.color = 'red';
            errorDiv.textContent = data.error || "Erreur lors de la modification.";
        }

    } catch (error) {
        console.error('Erreur:', error);
        errorDiv.textContent = "Erreur lors de l'envoi de la modification.";
    }
}
</script>

</body>
</html>
