<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Compte</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container">
    <h1>Mon Compte</h1>

    <div id="infos-utilisateur">
        <p><strong>Nom d’utilisateur :</strong> <span id="nom-utilisateur">Chargement...</span></p>
    </div>

    <h2>Changer mon mot de passe</h2>
    <div id="message-retour" style="margin-bottom: 1rem;"></div>

    <form id="form-changement-mdp">
        <div class="form-group">
            <label for="mot_de_passe_actuel">Mot de passe actuel :</label>
            <input type="password" id="mot_de_passe_actuel" required>
        </div>

        <div class="form-group">
            <label for="nouveau_mot_de_passe">Nouveau mot de passe :</label>
            <input type="password" id="nouveau_mot_de_passe" required>
        </div>

        <div class="form-group">
            <label for="confirmation_mot_de_passe">Confirmer le nouveau mot de passe :</label>
            <input type="password" id="confirmation_mot_de_passe" required>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    </form>

    <p>
        <a href="../views/dashboard.php" class="btn btn-back">Retour au tableau de bord</a>
    </p>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
        return;
    }

    // Décoder le token JWT pour afficher le nom d'utilisateur
    const payload = JSON.parse(atob(token.split('.')[1]));
    document.getElementById('nom-utilisateur').textContent = payload.nom_utilisateur ?? '(non défini)';

    const formulaire = document.getElementById('form-changement-mdp');
    const messageRetour = document.getElementById('message-retour');

    formulaire.addEventListener('submit', async (e) => {
        e.preventDefault();
        messageRetour.innerHTML = '';

        const motAncien = document.getElementById('mot_de_passe_actuel').value;
        const motNouveau = document.getElementById('nouveau_mot_de_passe').value;
        const motConfirmation = document.getElementById('confirmation_mot_de_passe').value;

        if (motNouveau !== motConfirmation) {
            messageRetour.innerHTML = `<p class="error-message">Les mots de passe ne correspondent pas.</p>`;
            return;
        }

        try {
            const reponse = await fetch('http://localhost/MiniprojetR3.01/api-auth/public/modifier', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    ancien_mot_de_passe: motAncien,
                    nouveau_mot_de_passe: motNouveau
                })
            });

            const resultat = await reponse.json();

            if (resultat.success) {
                messageRetour.innerHTML = `<p class="success-message">${resultat.success}</p>`;
                formulaire.reset();
            } else {
                messageRetour.innerHTML = `<p class="error-message">${resultat.error || "Une erreur est survenue."}</p>`;
            }
        } catch (erreur) {
            console.error(erreur);
            messageRetour.innerHTML = `<p class="error-message">Erreur lors de la communication avec le serveur.</p>`;
        }
    });
});
</script>
</body>
</html>
