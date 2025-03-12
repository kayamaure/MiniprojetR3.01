<?php
/**
 * Script de déconnexion
 *
 * Ce fichier gère la déconnexion de l'utilisateur en détruisant la session
 * active et en redirigeant vers la page de connexion.
 */
session_start();
session_unset();
session_destroy();
header("Location: ../views/connexion.php");
exit();
