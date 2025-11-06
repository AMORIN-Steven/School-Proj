<?php
session_start();
include('../includes/bdd.php');

// Vérifier si utilisateur connecté et rôle parent
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'parent') {
    header('Location: ../index.php');
    exit();
}

// Récupérer les enfants liés au parent connecté
$id_parent = $_SESSION['id_user'];

$sql = "SELECT e.Id_etud, e.nom, e.prenom
        FROM parent_enfant pe
        JOIN etud e ON pe.Id_etud = e.Id_etud
        WHERE pe.Id_parent = ?";
$stmt = $bdd->prepare($sql);
$stmt->execute([$id_parent]);
$enfants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Liste des enfants</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="text-center mb-4">Liste des enfants</h2>

    <?php if (!empty($enfants)): ?>
      <table class="table table-striped table-bordered text-center">
        <thead class="table-dark">
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Consulter notes</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($enfants as $enf): ?>
          <tr>
            <td><?= htmlspecialchars($enf['nom']) ?></td>
            <td><?= htmlspecialchars($enf['prenom']) ?></td>
            <td>
              <a href="../consulter_notes.php?id=<?= $enf['Id_etud'] ?>" class="btn btn-primary btn-sm">Voir notes</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert alert-warning text-center">
        Aucun enfant trouvé.
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
