<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Joueurs</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* ✅ Scrollbar sur le conteneur */
        #table-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #ccc;
        }

        /* ✅ Barre de recherche */
        #search-bar {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .loading-message {
            text-align: center;
            font-style: italic;
        }
    </style>
</head>

<body>
<?php include '../../views/header.php'; ?>

<div class="table-container">
    <h1>Liste des Joueurs</h1>

    <!-- ✅ Barre de recherche -->
    <input type="text" id="search-bar" placeholder="Rechercher un joueur...">

    <!-- Message d'erreur -->
    <div id="error-message" style="color: red;"></div>

    <a href="ajouter.php" class="btn btn-add">Ajouter un Joueur</a>
    <a href="../dashboard.php" class="btn btn-back">Retour</a>

    <div id="table-container">
        <table id="joueurs-table">
            <thead>
                <tr>
                    <th>Numéro Licence</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Date de Naissance</th>
                    <th>Taille</th>
                    <th>Poids</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="joueurs-tbody">
                <tr>
                    <td colspan="8" class="loading-message">Chargement des joueurs...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Message quand aucun joueur n'est disponible -->
    <div id="no-data-message" style="display: none; margin-top: 20px;">
        <p>Aucune donnée disponible. Veuillez ajouter des joueurs.</p>
    </div>
</div>

<?php include '../../views/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '../connexion.php';
        return;
    }

    fetchJoueurs(token);

    // 🔍 Barre de recherche
    document.getElementById('search-bar').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#joueurs-tbody tr');

        rows.forEach(row => {
            const contenu = row.textContent.toLowerCase();
            row.style.display = contenu.includes(query) ? '' : 'none';
        });
    });
});

// ✅ Chargement des joueurs
async function fetchJoueurs(token) {
    try {
        const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=joueurs', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 403) {
            document.getElementById('error-message').textContent = "Accès refusé. Token invalide ou expiré.";
            return;
        }

        const data = await response.json();
        const tableBody = document.getElementById('joueurs-tbody');
        const tableContainer = document.getElementById('table-container');
        const noDataMessage = document.getElementById('no-data-message');

        if (Array.isArray(data) && data.length > 0) {
            tableContainer.style.display = 'block';
            noDataMessage.style.display = 'none';
            tableBody.innerHTML = '';

            data.forEach(joueur => {
                const row = document.createElement('tr');
                row.dataset.id = joueur.numero_licence;

                row.innerHTML = `
                    <td>${joueur.numero_licence}</td>
                    <td>${joueur.nom}</td>
                    <td>${joueur.prenom}</td>
                    <td>${joueur.date_naissance}</td>
                    <td>${joueur.taille} m</td>
                    <td>${joueur.poids} kg</td>
                    <td>${joueur.statut}</td>
                    <td>
                        <a href="modifier.php?numero_licence=${joueur.numero_licence}" class="btn btn-edit">Modifier</a>
                        <a href="#" class="btn btn-delete" onclick="deleteJoueur('${joueur.numero_licence}'); return false;">Supprimer</a>
                        <a href="../commentaires/ajouter.php?numero_licence=${joueur.numero_licence}" class="btn btn-add-commentaire">Ajouter Commentaire</a>
                    </td>
                `;
                tableBody.appendChild(row);
            });

        } else {
            tableContainer.style.display = 'none';
            noDataMessage.style.display = 'block';
        }

    } catch (error) {
        console.error('Erreur de fetchJoueurs:', error);
        document.getElementById('error-message').textContent = 'Erreur lors de la récupération des joueurs.';
    }
}

// ✅ Suppression d’un joueur
async function deleteJoueur(numeroLicence) {
    const token = localStorage.getItem('authToken');
    if (!token) {
        alert("Vous devez être connecté.");
        return;
    }

    if (!confirm('Êtes-vous sûr de vouloir supprimer ce joueur ?')) return;

    try {
        const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/public/index.php?action=joueurs&numero_licence=${numeroLicence}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();
        if (data.success || data.message) {
            const messageDiv = document.getElementById('error-message');
            messageDiv.style.color = 'green';
            messageDiv.textContent = '✅ Joueur supprimé avec succès.';

            // Recharger les joueurs après une petite pause (optionnel)
            setTimeout(() => {
                fetchJoueurs(token);
                messageDiv.textContent = ''; // Efface le message après 2 secondes
            }, 2000);

} else {
    document.getElementById('error-message').style.color = 'red';
    document.getElementById('error-message').textContent = data.error || 'Erreur lors de la suppression.';
}

    } catch (error) {
        console.error('Erreur de suppression:', error);
        document.getElementById('error-message').textContent = 'Erreur lors de la suppression du joueur.';
    }
}
</script>

</body>
</html>
