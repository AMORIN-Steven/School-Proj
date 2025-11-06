<?php
// Fichier pour récupérer les données depuis la base de données
header('Content-Type: application/json');

// Configuration de la base de données
$host = 'localhost';
$dbname = 'ecole_db';
$user = 'root';
$pass = '';

// Connexion à la base de données
try {
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur de connexion à la base de données']);
    exit;
}

// Récupérer l'action demandée
$action = $_GET['action'] ?? '';

// Traitement des différentes actions
if ($action === 'filieres') {
    // Récupérer toutes les filières
    try {
        $stmt = $bdd->query("SELECT Id_fil, nom FROM filiere ORDER BY nom");
        $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($filieres);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erreur lors du chargement des filières']);
    }
    
} else if ($action === 'matieres') {
    // Récupérer les matières d'une filière
    $filiereId = $_GET['filiereId'] ?? '';
    
    if (empty($filiereId)) {
        echo json_encode(['error' => 'ID filière manquant']);
        exit;
    }
    
    try {
        $sql = "SELECT DISTINCT m.Id_mat, m.nom 
                FROM matiere m
                INNER JOIN fil_mat fm ON m.Id_mat = fm.Id_mat
                WHERE fm.Id_fil = ?
                ORDER BY m.nom";
        
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$filiereId]);
        $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($matieres);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erreur lors du chargement des matières']);
    }
    
} else if ($action === 'etudiants') {
    // Récupérer les étudiants avec leurs notes
    $filiereId = $_GET['filiereId'] ?? '';
    $matiereId = $_GET['matiereId'] ?? '';
    
    if (empty($filiereId) || empty($matiereId)) {
        echo json_encode(['error' => 'ID filière ou matière manquant']);
        exit;
    }
    
    try {
        $sql = "SELECT 
                    e.nom, 
                    e.prenom, 
                    e.mail,
                    e.matricule,
                    n.cc,
                    n.exam
                FROM etud e
                LEFT JOIN note n ON e.Id_etud = n.Id_etud AND n.Id_mat = ?
                WHERE e.Id_fil = ?
                ORDER BY e.nom, e.prenom";
        
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$matiereId, $filiereId]);
        $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($etudiants);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erreur lors du chargement des étudiants']);
    }
    
} else {
    // Action non reconnue
    echo json_encode(['error' => 'Action non reconnue']);
}
?>