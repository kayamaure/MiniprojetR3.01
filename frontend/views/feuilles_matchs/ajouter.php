<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Joueur à la Feuille de Match</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
    <?php include '../../views/header.php'; ?>
    
    <div class="table-container">
        <h1>Ajouter un Joueur à la Feuille de Match</h1>
        
        <!-- Message d'erreur -->
        <div id="error-message" style="color: red;"></div>
        <!-- Message de succès -->
        <div id="success-message" style="color: green;"></div>
        
        <a href="#" id="back-link" class="btn btn-back">Retour à la feuille de match</a>
        
        <!-- Informations du match -->
        <div id="match-details">
            <!-- Les détails du match seront insérés ici par JavaScript -->
            <p>Chargement des détails du match...</p>
        </div>
        
        <!-- Formulaire d'ajout de joueur -->
        <form id="ajout-joueur-form">
            <input type="hidden" id="id_match" name="id_match">
            
            <div>
                <label for="numero_licence">Joueur:</label>
                <select id="numero_licence" name="numero_licence" required>
                    <option value="">Sélectionnez un joueur</option>
                    <!-- Les joueurs disponibles seront insérés ici par JavaScript -->
                </select>
            </div>
            
            <div id="player-details">
                <p>Sélectionnez un joueur pour voir les détails.</p>
            </div>
            
            <div>
                <label for="role">Rôle:</label>
                <select id="role" name="role" required>
                    <option value="">Sélectionnez un rôle</option>
                    <option value="Titulaire">Titulaire</option>
                    <option value="Remplaçant">Remplaçant</option>
                </select>
            </div>
            
            <div>
                <label for="poste">Poste:</label>
                <select id="poste" name="poste" required>
    <option value="">Sélectionnez un poste</option>
    <option value="Gardien de But">Gardien de But</option>
    <option value="Défenseur Central">Défenseur Central</option>
    <option value="Défenseur Latéral">Défenseur Latéral</option>
    <option value="Arrière Latéral Offensif">Arrière Latéral Offensif</option>
    <option value="Libéro">Libéro</option>
    <option value="Milieu Défensif">Milieu Défensif</option>
    <option value="Milieu Central">Milieu Central</option>
    <option value="Milieu Offensif">Milieu Offensif</option>
    <option value="Milieu Latéral">Milieu Latéral</option>
    <option value="Attaquant Central">Attaquant Central</option>
    <option value="Avant-Centre">Avant-Centre</option>
    <option value="Ailier">Ailier</option>
    <option value="Second Attaquant">Second Attaquant</option>
</select>
            </div>
            
            <button type="submit" class="btn btn-add">Ajouter</button>
        </form>
    </div>
    
    <?php include '../../views/footer.php'; ?>
    
    <script>
let donneesJoueurs = []; // Données des joueurs (globales)

document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '/MiniprojetR3.01/frontend/views/connexion.php';
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const idMatch = urlParams.get('id_match');
    if (!idMatch) {
        afficherErreur("ID du match non spécifié.");
        return;
    }

    initialiserPage(idMatch, token);
});

function initialiserPage(idMatch, token) {
    document.getElementById('back-link').href = `index.php?id_match=${idMatch}`;
    document.getElementById('id_match').value = idMatch;

    recupererMatchEtJoueurs(idMatch, token);

    document.getElementById('ajout-joueur-form').addEventListener('submit', async e => {
        e.preventDefault();
        await ajouterJoueurDansFeuille(token);
    });

    document.getElementById('numero_licence').addEventListener('change', function () {
        afficherDetailsJoueur(this.value);
    });
}

function afficherErreur(message) {
    const erreurDiv = document.getElementById('error-message');
    erreurDiv.textContent = message;
    erreurDiv.style.color = 'red';
}

function afficherSucces(message) {
    const succesDiv = document.getElementById('success-message');
    succesDiv.textContent = message;
    succesDiv.style.color = 'green';
}

async function recupererMatchEtJoueurs(idMatch, token) {
    try {
        const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=feuille_match&sub_action=ajouter&id_match=${idMatch}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        const data = await response.json();
        if (data.error) return afficherErreur(data.error);

        afficherDetailsMatch(data.match);
        remplirListeJoueurs(data.joueursNonSelectionnes);

    } catch (error) {
        console.error('Erreur:', error);
        afficherErreur('Erreur lors de la récupération des données du match et des joueurs.');
    }
}

function afficherDetailsMatch(match) {
    if (!match) {
        afficherErreur('Match non trouvé.');
        return;
    }

    document.getElementById('match-details').innerHTML = `
        <h2>Match du ${match.date_match} à ${match.heure_match}</h2>
        <p><strong>Contre:</strong> ${match.nom_equipe_adverse}</p>
        <p><strong>Lieu:</strong> ${match.lieu_de_rencontre}</p>
    `;
}

function remplirListeJoueurs(joueurs) {
    donneesJoueurs = joueurs || [];
    const select = document.getElementById('numero_licence');
    select.innerHTML = '<option value="">Sélectionnez un joueur</option>';

    if (donneesJoueurs.length === 0) {
        afficherErreur("Aucun joueur disponible pour ce match.");
    } else {
        donneesJoueurs.forEach(j => {
            const option = document.createElement('option');
            option.value = j.numero_licence;
            option.textContent = `${j.nom} ${j.prenom}`;
            select.appendChild(option);
        });
    }
}

function afficherDetailsJoueur(numero_licence) {
    const container = document.getElementById('player-details');
    if (!numero_licence) {
        container.innerHTML = '<p>Sélectionnez un joueur pour voir les détails.</p>';
        return;
    }

    const joueur = donneesJoueurs.find(p => p.numero_licence === numero_licence);
    if (!joueur) {
        container.innerHTML = '<p>Aucune information disponible pour ce joueur.</p>';
        return;
    }

    container.innerHTML = `
        <p><strong>Taille:</strong> ${joueur.taille || 'N/A'} m</p>
        <p><strong>Poids:</strong> ${joueur.poids || 'N/A'} kg</p>
        <p><strong>Dernier Commentaire:</strong> ${joueur.commentaire?.texte_commentaire || 'Pas de commentaire'}</p>
        <p><strong>Moyenne des Évaluations:</strong> ${joueur.moyenne_evaluation ? parseFloat(joueur.moyenne_evaluation).toFixed(2) : 'Aucune évaluation'}</p>
    `;
}

async function ajouterJoueurDansFeuille(token) {
    const formulaire = document.getElementById('ajout-joueur-form');
    const formData = new FormData(formulaire);
    const data = Object.fromEntries(formData.entries());
    const idMatch = data.id_match;

    console.log("📤 Données envoyées :", data); 

    try {
        const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=feuille_match&sub_action=ajouter&id_match=${idMatch}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.message || result.success) {
            afficherSucces('✅ Joueur ajouté avec succès à la feuille de match.');
            formulaire.reset();
            document.getElementById('player-details').innerHTML = '<p>Sélectionnez un joueur pour voir les détails.</p>';

            setTimeout(() => {
                recupererMatchEtJoueurs(idMatch, token);
                document.getElementById('success-message').textContent = '';
            }, 2000);
        } else {
            afficherErreur(result.error || 'Erreur lors de l\'ajout du joueur.');
        }
    } catch (error) {
        console.error('Erreur:', error);
        afficherErreur('Erreur lors de l\'envoi des données.');
    }
}
</script>


</body>
</html>