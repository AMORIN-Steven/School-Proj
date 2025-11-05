<?php
include 'db.php';
$filiere = $_GET['filiere'];
$sql = "SELECT nom FROM etudiants WHERE filiere = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $filiere);
$stmt->execute();
$result = $stmt->get_result();
$etudiants = [];
while ($row = $result->fetch_assoc()) {
  $etudiants[] = $row['nom'];
}
echo json_encode($etudiants);
?>