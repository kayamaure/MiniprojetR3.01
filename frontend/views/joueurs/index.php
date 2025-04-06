<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Joueurs</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
<<<<<<< Updated upstream
=======
    <style>
        /* Styles pour le conteneur avec scrollbar */
        #table-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #ccc;
        }

        /* Styles pour la barre de recherche */
        #search-bar {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        /* Style pour le message de chargement */
        .loading-message {
            text-align: center;
            font-style: italic;
        }
    </style>
>>>>>>> Stashed changes
</head>

<body>
    <?php 
    // Vérification de l'authentification JWT
    include '../../views/header.php';
    ?>
    
    <div class="table-container">
        <h1>Liste des Joueurs</h1>
        
        <!-- Message d'erreur -->
        <div id="error-message" style="color: red;"></div>

<<<<<<< Updated upstream
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
                    <!-- Contenu généré dynamiquement par JavaScript -->
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
        // Vérifier l'authentification au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('authToken');
            if (!token) {
                // Rediriger vers la page de connexion si pas de token
                window.location.href = '../connexion.php';
            } else {
                // Afficher la liste des joueurs
                fetchJoueurs();
            }
        });
        
        // Fonction pour récupérer les joueurs via l'API
        async function fetchJoueurs() {
            try {
                const token = localStorage.getItem('authToken');
                // Utilisation du routeur index.php au lieu d'appeler directement les contrôleurs
                const response = await fetch('http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=liste', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const data = await response.json();
                
                if (data.error) {
                    document.getElementById('error-message').textContent = data.error;
                    return;
                }
                
                const tableBody = document.getElementById('joueurs-tbody');
                const tableContainer = document.getElementById('table-container');
                const noDataMessage = document.getElementById('no-data-message');
                
                // Vérifier si nous avons des joueurs
                if (data.success && data.joueurs && data.joueurs.length > 0) {
                    // Afficher le tableau et masquer le message "pas de données"
                    tableContainer.style.display = 'block';
                    noDataMessage.style.display = 'none';
                    
                    // Vider le tableau existant
                    tableBody.innerHTML = '';
                    
                    // Remplir le tableau avec les données
                    data.joueurs.forEach(joueur => {
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
                    // Masquer le tableau et afficher le message "pas de données"
                    tableContainer.style.display = 'none';
                    noDataMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la récupération des joueurs.';
                
                // En cas d'erreur, masquer le tableau et afficher le message
                document.getElementById('table-container').style.display = 'none';
                document.getElementById('no-data-message').style.display = 'block';
            }
        }
        
        // Fonction pour supprimer un joueur
        async function deleteJoueur(numeroLicence) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce joueur ?')) {
                return;
            }
            
            try {
                const token = localStorage.getItem('authToken');
                const response = await fetch(`http://127.0.0.1/MiniprojetR3.01/api-sports/index.php?action=supprimer&numero_licence=${numeroLicence}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Actualiser la liste des joueurs
                    fetchJoueurs();
                } else {
                    document.getElementById('error-message').textContent = data.error || 'Erreur lors de la suppression.';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-message').textContent = 'Erreur lors de la suppression du joueur.';
            }
        }
    </script>
=======
<div class="table-container">
    <h1>Liste des Joueurs</h1>

    <!-- Barre de recherche -->
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
// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    // Vérification de l'authentification
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '../connexion.php';
        return;
    }

    // Chargement initial des joueurs
    fetchJoueurs(token);

    // Gestion de la recherche en temps réel
    document.getElementById('search-bar').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#joueurs-tbody tr');

        // Filtrage des lignes selon la recherche
        rows.forEach(row => {
            const contenu = row.textContent.toLowerCase();
            row.style.display = contenu.includes(query) ? '' : 'none';
        });
    });
});

/**
 * Récupère et affiche la liste des joueurs depuis l'API
 * @param {string} token - Token d'authentification
 */
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
        console.error('Erreur lors de la récupération des joueurs :', error);
        document.getElementById('error-message').textContent = 'Erreur lors de la récupération des joueurs.';
    }
}

/**
 * Supprime un joueur de la base de données
 * @param {string} numeroLicence - Numéro de licence du joueur à supprimer
 */
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
            messageDiv.textContent = 'Joueur supprimé avec succès.';

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

>>>>>>> Stashed changes
</body>
</html>