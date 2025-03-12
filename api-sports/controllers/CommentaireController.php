<?php
/**
 * Contrôleur des commentaires
 *
 * Ce fichier gère les actions liées aux commentaires des joueurs dans l'application.
 * Il permet notamment d'ajouter un commentaire pour un joueur donné.
 *
 * Fonctionnalités principales :
 *  - Afficher un formulaire pour ajouter un commentaire.
 *  - Enregistrer un nouveau commentaire dans la base de données.
 *  - Rediriger vers la liste des joueurs après ajout.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fichiers de configuration et les modèles nécessaires
require_once '../config/database.php';
require_once '../models/Commentaire.php';

// Initialiser la connexion à la base de données et l'instance du modèle Commentaire
$database = new Database();
$db = $database->getConnection();
$commentaire = new Commentaire($db);

// Récupérer l'action depuis les paramètres GET ou définir une action par défaut
$action = $_GET['action'] ?? 'ajouter_commentaire';

// Gérer les différentes actions possibles
switch ($action) {
    case 'ajouter_commentaire':
        /**
         * Ajouter un commentaire
         *
         * Cette action permet d'afficher un formulaire pour ajouter un commentaire
         * pour un joueur, et de traiter la soumission du formulaire.
         */

        // Assurer la présence du numéro de licence dans les paramètres GET
        $numero_licence = $_GET['numero_licence'] ?? null;

        // Si la méthode HTTP est POST, traiter le formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $data = [
                'sujet_commentaire' => $_POST['sujet_commentaire'], // Sujet du commentaire
                'texte_commentaire' => $_POST['texte_commentaire'], // Contenu du commentaire
                'numero_licence' => $numero_licence // Référence au joueur (numéro de licence)
            ];

            // Ajouter le commentaire dans la base de données
            $commentaire->ajouterCommentaire($data);

            // Rediriger vers la liste des joueurs après l'ajout du commentaire
            header("Location: ../controllers/JoueursController.php?action=lister");
            exit();
        }

        // Si aucune soumission, afficher le formulaire d'ajout de commentaire
        require '../views/commentaires/ajouter.php';
        break;

    default:
        // Action par défaut : afficher une erreur si l'action n'est pas supportée
        echo "Action non supportée.";
        break;
}
?>
