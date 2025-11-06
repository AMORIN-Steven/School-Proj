<?php
require_once 'cnc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom_filiere']);
    
    if (!empty($nom)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO filiere (nom) VALUES (?)");
            $stmt->execute([$nom]);
            
            header('Location: ../liste.html?success=1');
            exit();
        } catch(PDOException $e) {
            die("Erreur lors de l'ajout : " . $e->getMessage());
        }
    } else {
        header('Location: ../ajouter.html?error=1');
        exit();
    }
}
?>