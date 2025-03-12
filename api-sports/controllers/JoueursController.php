<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
    /**
 * Contrôleur des joueurs
 *
 * Ce fichier gère les différentes actions liées à la gestion des joueurs, 
 * telles que l'affichage de la liste des joueurs, l'ajout, la modification 
 * et la suppression d'un joueur.
 *
 * Actions disponibles :
 * - `liste` : Affiche tous les joueurs.
 * - `ajouter` : Permet d'ajouter un nouveau joueur.
 * - `modifier` : Permet de modifier les informations d'un joueur existant.
 * - `supprimer` : Supprime un joueur spécifique.
 *
 * Les actions utilisent les méthodes du modèle `Joueur` pour interagir avec la base de données.
 */
} ?>
<?php include '../views/header.php'; ?>

<?php
require_once '../config/database.php';
require_once '../models/Joueur.php';
$database = new Database();
$db = $database->getConnection();
$joueur = new Joueur($db);

// Déterminer l'action à exécuter
$action = $_GET['action'] ?? 'liste';

switch ($action) {
    case 'liste':
        $joueurs = $joueur->obtenirTousLesJoueurs();
        require '../views/joueurs/index.php';
        break;

    case 'ajouter':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'numero_licence' => $_POST['numero_licence'],
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'date_naissance' => $_POST['date_naissance'],
                'taille' => $_POST['taille'],
                'poids' => $_POST['poids'],
                'statut' => $_POST['statut']
            ];
            $joueur->ajouterJoueur($data);
            header("Location: JoueursController.php?action=liste");
            exit();
        }
        require '../views/joueurs/ajouter.php';
        break;

    case 'modifier':
        $numero_licence = $_GET['numero_licence'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'numero_licence' => $numero_licence,
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'date_naissance' => $_POST['date_naissance'],
                'taille' => $_POST['taille'],
                'poids' => $_POST['poids'],
                'statut' => $_POST['statut']
            ];
            $joueur->mettreAJourJoueur($data);
            header("Location: JoueursController.php?action=liste");
            exit();
        }
        $joueur_info = $joueur->obtenirJoueur($numero_licence);
        require '../views/joueurs/modifier.php';
        break;

    case 'supprimer':
        $numero_licence = $_GET['numero_licence'];
        $joueur->supprimerJoueur($numero_licence);
        header("Location: JoueursController.php?action=liste");
        exit();
        break;

    default:
        $joueurs = $joueur->obtenirTousLesJoueurs();
        require '../views/joueurs/index.php';
        break;
}
?>
