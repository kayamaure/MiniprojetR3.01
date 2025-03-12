<?php
require_once __DIR__ . '/../config/database.php';
// Modèle Utilisateur

class Utilisateur {
    private $conn;
    private $table_name = "utilisateur";

    public function __construct($db) {
        $this->conn = $db;
    }

    
    // Vérifie les informations de connexion d'un utilisateur
    public function verifierUtilisateur($nom_utilisateur, $mot_de_passe) {
        $query = "SELECT mot_de_passe 
                  FROM " . $this->table_name . " 
                  WHERE nom_utilisateur = :nom_utilisateur
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom_utilisateur', $nom_utilisateur, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie le hash du mot de passe si la requête a renvoyé un résultat
        return $row && password_verify($mot_de_passe, $row['mot_de_passe']);
    }

    
    // Récupère un utilisateur par son ID (entier auto-incrémenté)
    public function getUtilisateurParId($id_utilisateur) {
        $query = "SELECT * FROM utilisateur WHERE id_utilisateur = :id_utilisateur LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
     // Vérifie si un nom d'utilisateur existe déjà
    public function existeUtilisateur($nom_utilisateur) {
        $query = "SELECT id_utilisateur 
                  FROM " . $this->table_name . " 
                  WHERE nom_utilisateur = :nom_utilisateur
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom_utilisateur', $nom_utilisateur, PDO::PARAM_STR);
        $stmt->execute();

        // Retourne true si on a trouvé un enregistrement, false sinon
        return ($stmt->fetch(PDO::FETCH_ASSOC) !== false);
    }

    
     // Ajoute un nouvel utilisateur avec un mot de passe haché
  
    public function ajouterUtilisateur($nom_utilisateur, $mot_de_passe) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nom_utilisateur, mot_de_passe) 
                  VALUES (:nom_utilisateur, :mot_de_passe)";
        $stmt = $this->conn->prepare($query);

        // Hachage du mot de passe pour la sécurité
        $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        $stmt->bindParam(':nom_utilisateur', $nom_utilisateur, PDO::PARAM_STR);
        $stmt->bindParam(':mot_de_passe', $mot_de_passe_hache, PDO::PARAM_STR);

        return $stmt->execute();
    }
    public function updatePassword($id_utilisateur, $hashed_password) {
        try {
            
            $id_utilisateur = (int) $id_utilisateur;    
           
            $query = "UPDATE utilisateur SET mot_de_passe = :mot_de_passe WHERE id_utilisateur = :id_utilisateur";
            $stmt = $this->conn->prepare($query);
    
            $stmt->bindParam(':mot_de_passe', $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    
            if ($stmt->execute()) {
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Database error: " . $errorInfo[2]);
            }
        } catch (Exception $e) {
          
            error_log($e->getMessage()); 
            return false; 
        }
    }
    
    
}
