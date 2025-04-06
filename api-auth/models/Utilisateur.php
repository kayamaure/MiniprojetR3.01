<?php
<<<<<<< Updated upstream
require_once __DIR__ . '/../config/database.php';

=======
/**
 * Classe de gestion des utilisateurs
 * Gère les opérations liées aux utilisateurs dans la base de données
 */
>>>>>>> Stashed changes
class Utilisateur {
    private $conn;
    private $table_name = "utilisateur";
    private $table_tokens = "tokens";

    /**
     * Constructeur
     * @param PDO $db Instance de connexion à la base de données
     */
    public function __construct($db) {
        $this->conn = $db;
    }

<<<<<<< Updated upstream
    // Crée un nouveau token JWT pour l'utilisateur
    public function creerToken($id_utilisateur) {
        // Supprime les anciens tokens expirés
        $this->nettoyerTokensExpires();

        // Création du token avec expiration après 15 minutes
        $token = $this->genererToken();
        $date_expiration = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $query = "INSERT INTO " . $this->table_tokens . "
                 (id_utilisateur, token, date_expiration)
                 VALUES (:id_utilisateur, :token, :date_expiration)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->bindParam(':date_expiration', $date_expiration, PDO::PARAM_STR);

        if($stmt->execute()) {
            return $token;
        }
        return false;
    }

    // Vérifie si un token est valide
    public function verifierToken($token) {
        $query = "SELECT t.*, u.nom_utilisateur 
                 FROM " . $this->table_tokens . " t
                 JOIN " . $this->table_name . " u ON t.id_utilisateur = u.id_utilisateur
                 WHERE t.token = :token
                 AND t.date_expiration > NOW()
                 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Nettoie les tokens expirés
    private function nettoyerTokensExpires() {
        $query = "DELETE FROM " . $this->table_tokens . " WHERE date_expiration < NOW()";
        $this->conn->exec($query);
    }

    // Génère un token unique
    private function genererToken() {
        return bin2hex(random_bytes(32));
    }

    // Invalide un token en le supprimant de la base de données
    public function invaliderToken($token) {
        $query = "DELETE FROM " . $this->table_tokens . "
                 WHERE token = :token";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Met à jour la date de dernière connexion
    public function mettreAJourDerniereConnexion($id_utilisateur) {
        $query = "UPDATE " . $this->table_name . "
                 SET derniere_connexion = NOW()
                 WHERE id_utilisateur = :id_utilisateur";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
        return $stmt->execute();
    }

    
    // Vérifie les informations de connexion d'un utilisateur et retourne les données utilisateur
    public function verifierUtilisateur($nom_utilisateur, $mot_de_passe) {
        $query = "SELECT id_utilisateur, nom_utilisateur, mot_de_passe 
                  FROM " . $this->table_name . " 
                  WHERE nom_utilisateur = :nom_utilisateur
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom_utilisateur', $nom_utilisateur, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie le hash du mot de passe si la requête a renvoyé un résultat
        if ($row && password_verify($mot_de_passe, $row['mot_de_passe'])) {
            // Retourne les informations utilisateur
            return [
                'id_utilisateur' => $row['id_utilisateur'],
                'nom_utilisateur' => $row['nom_utilisateur']
            ];
        }

        return false;
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
=======
    /**
     * Crée un nouvel utilisateur dans la base de données
     * @param string $nom_utilisateur Nom d'utilisateur
     * @param string $mot_de_passe Mot de passe hashé
     * @return bool Succès de la création
     */
    public function creerUtilisateur($nom_utilisateur, $mot_de_passe) {
        $stmt = $this->pdo->prepare("INSERT INTO utilisateur (nom_utilisateur, mot_de_passe) VALUES (:nom, :mdp)");
        return $stmt->execute([
            'nom' => $nom_utilisateur,
            'mdp' => $mot_de_passe
        ]);
    }
    

    /**
     * Recherche un utilisateur par son nom d'utilisateur
     * @param string $nom_utilisateur Nom d'utilisateur à rechercher
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function trouverParNomUtilisateur($nom_utilisateur) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE nom_utilisateur = :username");
        $stmt->execute(['username' => $nom_utilisateur]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Enregistre un nouveau jeton d'authentification
     * @param int $id_utilisateur ID de l'utilisateur
     * @param string $jeton Token JWT
     * @param string $expiration Date d'expiration du token
     * @return bool Succès de l'enregistrement
     */
    public function enregistrerJeton($id_utilisateur, $jeton, $expiration) {
        $stmt = $this->pdo->prepare("INSERT INTO tokens (id_utilisateur, token, date_expiration) VALUES (:id_utilisateur, :token, :expiration)");
        return $stmt->execute([
            'id_utilisateur' => $id_utilisateur,
            'token' => $jeton,
            'expiration' => $expiration
        ]);
    }

    /**
     * Met à jour la date de dernière connexion d'un utilisateur
     * @param int $id_utilisateur ID de l'utilisateur
     */
    public function mettreAJourDerniereConnexion($id_utilisateur) {
        $stmt = $this->pdo->prepare("UPDATE utilisateur SET derniere_connexion = CURRENT_TIMESTAMP WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id_utilisateur]);
    }
    
    /**
     * Supprime un token d'authentification de la base de données
     * @param string $token Token JWT à supprimer
     * @return bool Succès de la suppression
     */
    public function supprimerToken($token) {
        $stmt = $this->pdo->prepare("DELETE FROM tokens WHERE token = :token");
        return $stmt->execute(['token' => $token]);
    }

    /**
     * Récupère les informations d'un utilisateur par son ID
     * @param int $id_utilisateur ID de l'utilisateur
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getById($id_utilisateur) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id_utilisateur]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Modifie le mot de passe d'un utilisateur
     * @param int $id_utilisateur ID de l'utilisateur
     * @param string $nouveau_hash Nouveau mot de passe hashé
     * @return bool Succès de la modification
     */
    public function changerMotDePasse($id_utilisateur, $nouveau_hash) {
        $stmt = $this->pdo->prepare("UPDATE utilisateur SET mot_de_passe = :mdp WHERE id_utilisateur = :id");
        return $stmt->execute([
            'mdp' => $nouveau_hash,
            'id' => $id_utilisateur
        ]);
>>>>>>> Stashed changes
    }
    
    
}
