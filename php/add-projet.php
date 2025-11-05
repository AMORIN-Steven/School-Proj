<?php
include 'db.php';
$titre = $_POST['titre'];
$description = $_POST['description'];
$sql = "INSERT INTO projets (titre, description) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $titre, $description);
$stmt->execute();
echo "Projet ajouté";
?>