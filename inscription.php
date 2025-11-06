<?php
session_start();
require_once 'includes/bdd.php';

// Vérifier si un matricule étudiant existe (pour la validation parent)
function etudiantExiste($matricule, $bdd) {
    $req = $bdd->prepare("SELECT Id_etud FROM etud WHERE matricule = ?");
    $req->execute([$matricule]);
    return $req->fetch() !== false;
}

// Générer un matricule unique
function genererMatricule($bdd) {
    do {
        $matricule = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        $req = $bdd->prepare("SELECT Id_etud FROM etud WHERE matricule = ?");
        $req->execute([$matricule]);
    } while ($req->fetch());
    
    return $matricule;
}

$message = '';
$type_inscription = isset($_GET['type']) ? $_GET['type'] : 'etudiant';

// Récupérer les filières UNE SEULE FOIS avant le traitement du formulaire
$filieres = $bdd->query("SELECT Id_fil, nom FROM filiere ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $mail = trim($_POST['mail']);
        $motdepasse = password_hash($_POST['motdepasse'], PASSWORD_DEFAULT);
        $tel = trim($_POST['tel']);
        $status = $type_inscription;

        // Validation des champs communs
        if (empty($nom) || empty($prenom) || empty($mail) || empty($_POST['motdepasse'])) {
            throw new Exception("Tous les champs obligatoires doivent être remplis.");
        }

        // Vérifier si l'email existe déjà
        $req = $bdd->prepare("SELECT Id_user FROM user WHERE mail = ?");
        $req->execute([$mail]);
        if ($req->fetch()) {
            throw new Exception("Cet email est déjà utilisé.");
        }

        // Commencer la transaction
        $bdd->beginTransaction();

        // 1. Insérer dans la table user
        $stmt = $bdd->prepare("INSERT INTO user (mail, nom, prenom, motdepasse, tel, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$mail, $nom, $prenom, $motdepasse, $tel, $status]);
        $id_user = $bdd->lastInsertId();

        if ($type_inscription === 'etudiant') {
            // Inscription étudiant
            $matricule = genererMatricule($bdd);
            $id_fil = !empty($_POST['filiere']) ? (int)$_POST['filiere'] : null;

            $stmt = $bdd->prepare("INSERT INTO etud (nom, prenom, mail, Id_fil, matricule, Id_user) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $mail, $id_fil, $matricule, $id_user]);

            $message = "✅ Inscription réussie ! Votre matricule est : <strong>$matricule</strong>";

        } elseif ($type_inscription === 'parent') {
            // Inscription parent
            if (empty($_POST['matricule_enfant'])) {
                throw new Exception("Le matricule de l'enfant est obligatoire.");
            }

            $matricule_enfant = trim($_POST['matricule_enfant']);

            // Vérifier si l'étudiant existe
            if (!etudiantExiste($matricule_enfant, $bdd)) {
                throw new Exception("Matricule étudiant non trouvé.");
            }

            // Insérer le parent
            $stmt = $bdd->prepare("INSERT INTO parent (nom, prenoms, mail, Id_user) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $mail, $id_user]);
            $id_parent = $bdd->lastInsertId();

            // Créer la relation parent-enfant
            $req = $bdd->prepare("SELECT Id_etud FROM etud WHERE matricule = ?");
            $req->execute([$matricule_enfant]);
            $etudiant = $req->fetch();
            $id_etud = $etudiant['Id_etud'];

            $stmt = $bdd->prepare("INSERT INTO parent_enfant (Id_parent, Id_etud) VALUES (?, ?)");
            $stmt->execute([$id_parent, $id_etud]);

            $message = "✅ Inscription parent réussie !";
        }

        $bdd->commit();

    } catch (Exception $e) {
        $bdd->rollBack();
        $message = "❌ Erreur : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Plateforme Scolaire</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Inscription</h4>
                            <div class="btn-group btn-group-sm">
                                <a href="?type=etudiant" class="btn <?= $type_inscription === 'etudiant' ? 'btn-warning' : 'btn-outline-light' ?>">Étudiant</a>
                                <a href="?type=parent" class="btn <?= $type_inscription === 'parent' ? 'btn-warning' : 'btn-outline-light' ?>">Parent</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= strpos($message, '✅') !== false ? 'success' : 'danger' ?>">
                                <?= $message ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="formInscription">
                            <!-- Champs communs -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required 
                                           value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="prenom" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" required
                                           value="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="mail" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="mail" name="mail" required
                                       value="<?= isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="tel" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="tel" name="tel"
                                       value="<?= isset($_POST['tel']) ? htmlspecialchars($_POST['tel']) : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="motdepasse" class="form-label">Mot de passe *</label>
                                <input type="password" class="form-control" id="motdepasse" name="motdepasse" required>
                                <div class="form-text">Minimum 6 caractères</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirmation" class="form-label">Confirmation du mot de passe *</label>
                                <input type="password" class="form-control" id="confirmation" name="confirmation" required>
                            </div>

                            <!-- Champs spécifiques -->
                            <?php if ($type_inscription === 'etudiant'): ?>
                                <div class="mb-3">
                                    <label for="filiere" class="form-label">Filière</label>
                                    <select class="form-select" id="filiere" name="filiere">
                                        <option value="">Sélectionnez une filière</option>
                                        <?php foreach ($filieres as $filiere): ?>
                                            <option value="<?= $filiere['Id_fil'] ?>" 
                                                <?= (isset($_POST['filiere']) && $_POST['filiere'] == $filiere['Id_fil']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($filiere['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php elseif ($type_inscription === 'parent'): ?>
                                <div class="mb-3">
                                    <label for="matricule_enfant" class="form-label">Matricule de l'enfant *</label>
                                    <input type="text" class="form-control" id="matricule_enfant" name="matricule_enfant" 
                                           placeholder="Ex: INF001" required
                                           value="<?= isset($_POST['matricule_enfant']) ? htmlspecialchars($_POST['matricule_enfant']) : '' ?>">
                                    <div class="form-text">Le matricule à 6 caractères de votre enfant</div>
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">S'inscrire</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <p>Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/custom_js/inscription.js"></script>
</body>
</html>