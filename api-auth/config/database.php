<?php
class Database {
    private $host = '127.0.0.1'; // Utiliser l'adresse IP directe au lieu de 'localhost'
    private $db_name = 'evalsport_gestion_equipe_football';
    private $username = 'root';
    private $password = '';  
    public $conn;

    // Méthode pour établir la connexion
    public function getConnection() {
        $this->conn = null;

        try {
            // Création de la connexion PDO avec des options d'optimisation
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Active le mode d'erreur d'exception
                    PDO::ATTR_EMULATE_PREPARES => false,  // Désactive l'émulation des requêtes préparées
                    PDO::ATTR_PERSISTENT => true,  // Utilise des connexions persistantes pour améliorer les performances
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Mode de récupération par défaut
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",  // Définit l'encodage UTF-8
                    PDO::ATTR_TIMEOUT => 5  // Définit un timeout de 5 secondes pour éviter les attentes trop longues
                ]
            );
        } catch (PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>