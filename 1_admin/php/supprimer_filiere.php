<?php
require_once 'cnc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    
    if (!empty($id)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM filiere WHERE Id_fil = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID manquant']);
    }
}
?>