<?php
class Database {
    private $host = 'localhost'; 
    private $db_name = 'evalsport_gestion_equipe_football';
    private $username = 'root';
    private $password = '';  
    public $conn;

    // Méthode pour établir la connexion
    public function getConnection() {
        $this->conn = null;

        try {
            // Création de la connexion PDO avec des options de sécurité
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");  // Définit l'encodage UTF-8
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Active le mode d'erreur d'exception
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);  // Désactive l'émulation des requêtes préparées (meilleure sécurité)
        } catch (PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>