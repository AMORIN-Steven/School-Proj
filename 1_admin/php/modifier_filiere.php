<?php
require_once 'cnc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nom = trim($_POST['nom_filiere']);
    
    if (!empty($nom) && !empty($id)) {
        try {
            $stmt = $pdo->prepare("UPDATE filiere SET nom = ? WHERE Id_fil = ?");
            $stmt->execute([$nom, $id]);
            
            echo json_encode(['success' => true]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    }
}
?>