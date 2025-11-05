<?php
include 'db.php';
$etudiant = $_POST['etudiant'];
$filiere = $_POST['filiere'];
$matiere = $_POST['matiere'];
$note = $_POST['note'];
$sql = "INSERT INTO notes (etudiant, filiere, matiere, note) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssd", $etudiant, $filiere, $matiere, $note);
$stmt->execute();
echo "Note enregistrée";
?>