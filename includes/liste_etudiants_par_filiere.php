<?php
include('bdd.php'); // ✅ bdd.php est dans le même dossier "includes"

$sql = "SELECT e.Id_etud, e.nom, e.prenom, f.nom AS filiere
        FROM etud e
        LEFT JOIN filiere f ON e.Id_fil = f.Id_fil
        ORDER BY f.nom, e.nom";
$stmt = $bdd->query($sql);
$etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Liste des étudiants par filière</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="text-center mb-4">Liste des étudiants par filière</h2>
    <table class="table table-striped table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Filière</th>
          <th>Consulter notes</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($etudiants as $et): ?>
        <tr>
          <td><?= htmlspecialchars($et['nom']) ?></td>
          <td><?= htmlspecialchars($et['prenom']) ?></td>
          <td><?= htmlspecialchars($et['filiere']) ?></td>
          <!-- ✅ Le lien reste local au dossier includes -->
          <td>
            <a href="consulter_notes.php?id=<?= $et['Id_etud'] ?>" class="btn btn-primary btn-sm">
              Voir notes
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
