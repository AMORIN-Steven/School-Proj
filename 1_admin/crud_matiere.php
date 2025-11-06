<?php
class GestionMatieres {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO('mysql:host=localhost;dbname=gestion_matieres;charset=utf8', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
    
    public function getMatieres() {
        $sql = "SELECT 
                    m.id,
                    m.nom AS matiere_nom,
                    GROUP_CONCAT(f.nom ORDER BY f.nom SEPARATOR ', ') AS filieres,
                    m.created_at,
                    m.updated_at
                FROM matieres m
                LEFT JOIN matiere_filiere mf ON m.id = mf.matiere_id
                LEFT JOIN filieres f ON mf.filiere_id = f.id
                GROUP BY m.id, m.nom
                ORDER BY m.id";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getFilieres() {
        $sql = "SELECT * FROM filieres ORDER BY nom";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function ajouterMatiere($nom, $filieres_ids) {
        try {
            $this->pdo->beginTransaction();
            
            $sql = "INSERT INTO matieres (nom) VALUES (?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nom]);
            $matiere_id = $this->pdo->lastInsertId();
            
            $sql = "INSERT INTO matiere_filiere (matiere_id, filiere_id) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($filieres_ids as $filiere_id) {
                $stmt->execute([$matiere_id, $filiere_id]);
            }
            
            $this->pdo->commit();
            return $matiere_id;
        } catch(Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function getMatiereById($id) {
        $sql = "SELECT 
                    m.id,
                    m.nom AS matiere_nom,
                    GROUP_CONCAT(f.id) AS filieres_ids
                FROM matieres m
                LEFT JOIN matiere_filiere mf ON m.id = mf.matiere_id
                LEFT JOIN filieres f ON mf.filiere_id = f.id
                WHERE m.id = ?
                GROUP BY m.id, m.nom";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $matiere = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($matiere) {
            $matiere['filieres_ids'] = $matiere['filieres_ids'] ? explode(',', $matiere['filieres_ids']) : [];
        }
        
        return $matiere;
    }
    
    public function modifierMatiere($id, $nom, $filieres_ids) {
        try {
            $this->pdo->beginTransaction();
            
            $sql = "UPDATE matieres SET nom = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nom, $id]);
            
            $sql = "DELETE FROM matiere_filiere WHERE matiere_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            
            $sql = "INSERT INTO matiere_filiere (matiere_id, filiere_id) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($filieres_ids as $filiere_id) {
                $stmt->execute([$id, $filiere_id]);
            }
            
            $this->pdo->commit();
            return true;
        } catch(Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function supprimerMatiere($id) {
        try {
            $sql = "DELETE FROM matieres WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch(Exception $e) {
            throw $e;
        }
    }
}

$gestion = new GestionMatieres();
$message = '';
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'ajouter':
                    $nom = trim($_POST['nom']);
                    $filieres = isset($_POST['filieres']) ? $_POST['filieres'] : [];
                    
                    if (empty($nom)) throw new Exception("Le nom de la matière est obligatoire");
                    if (empty($filieres)) throw new Exception("Veuillez sélectionner au moins une filière");
                    
                    $gestion->ajouterMatiere($nom, $filieres);
                    $message = "Matière ajoutée avec succès!";
                    $message_type = "success";
                    break;
                    
                case 'modifier':
                    $id = $_POST['id'];
                    $nom = trim($_POST['nom']);
                    $filieres = isset($_POST['filieres']) ? $_POST['filieres'] : [];
                    
                    if (empty($nom)) throw new Exception("Le nom de la matière est obligatoire");
                    if (empty($filieres)) throw new Exception("Veuillez sélectionner au moins une filière");
                    
                    $gestion->modifierMatiere($id, $nom, $filieres);
                    $message = "Matière modifiée avec succès!";
                    $message_type = "success";
                    break;
                    
                case 'supprimer':
                    $id = $_POST['id'];
                    $gestion->supprimerMatiere($id);
                    $message = "Matière supprimée avec succès!";
                    $message_type = "success";
                    break;
            }
        } catch(Exception $e) {
            $message = "Erreur : " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

$matieres = $gestion->getMatieres();
$filieres = $gestion->getFilieres();
$matiere_edition = null;

if (isset($_GET['editer'])) {
    $matiere_edition = $gestion->getMatiereById($_GET['editer']);
}
?>