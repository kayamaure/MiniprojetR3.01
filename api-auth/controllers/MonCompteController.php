<?php
/**
 * Contrôleur pour la gestion de la page "Mon Compte"
 * 
 * Ce fichier gère les actions liées à la gestion du compte utilisateur, 
 * comme l'affichage des informations de l'utilisateur connecté 
 * et la modification du mot de passe.
 * 
 * Fonctionnement :
 * 1. Vérifie si l'utilisateur est connecté en utilisant la session.
 * 2. Inclut les modèles nécessaires pour interagir avec la base de données.
 * 3. Permet deux actions principales :
 *    - `afficher` : Affiche les informations de l'utilisateur.
 *    - `updatePassword` : Permet de changer le mot de passe après vérification des données.
 * 
 * Si une action non reconnue est demandée, un message d'erreur est affiché.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../views/connexion.php');
    exit();
}

require_once '../config/database.php';
require_once '../models/Utilisateur.php';

$database = new Database();
$db = $database->getConnection();
$utilisateurModel = new Utilisateur($db);

$action = $_GET['action'] ?? 'afficher';

switch ($action) {
    case 'afficher':
        $id_utilisateur = (int)$_SESSION['id_utilisateur'];
        $user = $utilisateurModel->getUtilisateurParId($id_utilisateur);

        require '../views/mon_compte.php';
        break;

    case 'updatePassword':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_utilisateur = (int)$_SESSION['id_utilisateur'];

            $user = $utilisateurModel->getUtilisateurParId($id_utilisateur);
            if (!$user) {
                $error = "Utilisateur introuvable.";
                require '../views/mon_compte.php';
                break;
            }

            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (!$utilisateurModel->verifierUtilisateur($user['nom_utilisateur'], $current_password)) {
                $error = "Le mot de passe actuel est incorrect.";
            } elseif ($new_password !== $confirm_password) {
                $error = "Les mots de passe ne correspondent pas.";
            } elseif (strlen($new_password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $update_success = $utilisateurModel->updatePassword($id_utilisateur, $hashed_password);

                if ($update_success) {
                    $success = "Mot de passe changé avec succès.";
                } else {
                    $error = "Erreur lors de la mise à jour du mot de passe.";
                }
            }

            $user = $utilisateurModel->getUtilisateurParId($id_utilisateur);
            require '../views/mon_compte.php';
        }
        break;

    default:
        echo "Action non reconnue.";
        break;
}
?>
