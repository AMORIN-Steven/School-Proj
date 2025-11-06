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

    public function matiereExists($nom, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM matieres WHERE nom = ?";
        $params = [$nom];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}

function sendJsonResponse($success, $data = null, $error = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'error' => $error
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gestion = new GestionMatieres();
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'ajouter':
                $nom = trim($_POST['nom'] ?? '');
                $filieres = $_POST['filieres'] ?? [];
                
                if (empty($nom)) {
                    throw new Exception("Le nom de la matière est obligatoire");
                }
                
                if (empty($filieres)) {
                    throw new Exception("Veuillez sélectionner au moins une filière");
                }
                
                if ($gestion->matiereExists($nom)) {
                    throw new Exception("Une matière avec ce nom existe déjà");
                }
                
                $id = $gestion->ajouterMatiere($nom, $filieres);
                sendJsonResponse(true, ['id' => $id], "Matière ajoutée avec succès");
                break;
                
            case 'modifier':
                $id = $_POST['id'] ?? '';
                $nom = trim($_POST['nom'] ?? '');
                $filieres = $_POST['filieres'] ?? [];
                
                if (empty($id)) {
                    throw new Exception("ID de la matière manquant");
                }
                
                if (empty($nom)) {
                    throw new Exception("Le nom de la matière est obligatoire");
                }
                
                if (empty($filieres)) {
                    throw new Exception("Veuillez sélectionner au moins une filière");
                }
                
                if ($gestion->matiereExists($nom, $id)) {
                    throw new Exception("Une matière avec ce nom existe déjà");
                }
                
                $gestion->modifierMatiere($id, $nom, $filieres);
                sendJsonResponse(true, null, "Matière modifiée avec succès");
                break;
                
            case 'supprimer':
                $id = $_POST['id'] ?? '';
                
                if (empty($id)) {
                    throw new Exception("ID de la matière manquant");
                }
                
                $gestion->supprimerMatiere($id);
                sendJsonResponse(true, null, "Matière supprimée avec succès");
                break;
                
            case 'lister':
                $matieres = $gestion->getMatieres();
                sendJsonResponse(true, $matieres);
                break;
                
            case 'get':
                $id = $_POST['id'] ?? '';
                
                if (empty($id)) {
                    throw new Exception("ID de la matière manquant");
                }
                
                $matiere = $gestion->getMatiereById($id);
                sendJsonResponse(true, $matiere);
                break;
                
            default:
                throw new Exception("Action non reconnue");
        }
        
    } catch (Exception $e) {
        sendJsonResponse(false, null, $e->getMessage());
    }
}

if (basename($_SERVER['PHP_SELF']) == 'gestion_matieres.php' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $gestion = new GestionMatieres();
    $matieres = $gestion->getMatieres();
    
    echo "<h1>Gestion des Matières - Debug</h1>";
    echo "<pre>";
    print_r($matieres);
    echo "</pre>";
}
?>