<?php
include 'db.php';
$filiere = $_POST['filiere'];
$matiere = $_POST['matiere'];
$sql = "INSERT INTO matieres_filieres (filiere, matiere) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $filiere, $matiere);
$stmt->execute();
echo "Association enregistrée";
?>