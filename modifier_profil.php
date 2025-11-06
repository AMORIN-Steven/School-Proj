<?php
require_once 'includes/bdd.php';
require_once 'includes/auth_functions.php';

// Vérifier la connexion
verifierConnexion();

// Récupérer les informations utilisateur
$user_id = $_SESSION['user_id'];
$user_nom = $_SESSION['user_nom'];
$user_prenom = $_SESSION['user_prenom'];
$user_status = $_SESSION['user_status'];
$user_mail = $_SESSION['user_mail'];
$user_tel = isset($_SESSION['user_tel']) ? $_SESSION['user_tel'] : '';

// Récupérer les données complètes de l'utilisateur
$req = $bdd->prepare("SELECT * FROM user WHERE Id_user = ?");
$req->execute([$user_id]);
$user_data = $req->fetch();

$message = '';
$erreur = '';

// Traitement de la modification du profil
if ($_POST) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $tel = trim($_POST['tel']);
    
    if (empty($nom) || empty($prenom)) {
        $erreur = "Le nom et le prénom sont obligatoires";
    } else {
        try {
            $stmt = $bdd->prepare("UPDATE user SET nom = ?, prenom = ?, tel = ? WHERE Id_user = ?");
            $stmt->execute([$nom, $prenom, $tel, $user_id]);
            
            // Mettre à jour la session
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_prenom'] = $prenom;
            $_SESSION['user_tel'] = $tel;
            
            $message = "✅ Profil mis à jour avec succès";
            
        } catch (Exception $e) {
            $erreur = "❌ Erreur lors de la mise à jour: " . $e->getMessage();
        }
    }
}

$user_tel_display = isset($user_data['tel']) && !empty($user_data['tel']) ? $user_data['tel'] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mon profil - Plateforme Scolaire</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            border-left: 3px solid #fff;
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            border-left: 3px solid #fff;
        }
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .content-wrapper {
            flex: 1;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .profile-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px 10px 0 0;
        }
        .form-control {
            padding: 12px 15px;
        }
        .btn-profile {
            padding: 12px 30px;
            font-size: 16px;
        }
        footer {
            margin-top: auto;
            background: #343a40;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header text-center py-4">
            <h4>Plateforme Scolaire</h4>
            <small>Système de gestion</small>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link" href="dashboard.php">
                <span>📊 Dashboard</span>
            </a>
            <a class="nav-link" href="profile.php">
                <span>👤 Mon Profil</span>
            </a>
            <a class="nav-link active" href="modifier_profil.php">
                <span>✏️ Modifier Profil</span>
            </a>
            <a class="nav-link" href="modifier_motdepasse.php">
                <span>🔑 Modifier MDP</span>
            </a>
            <a class="nav-link" href="logout.php">
                <span>🚪 Déconnexion</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-light bg-white">
            <div class="container-fluid">
                <span class="navbar-brand">Modifier mon profil</span>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text">
                            <strong><?= htmlspecialchars($user_prenom . ' ' . $user_nom) ?></strong>
                            <span class="badge bg-primary ms-1"><?= $user_status ?></span>
                        </span>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Content -->
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= $message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($erreur): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $erreur ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="profile-header text-center">
                                <h2 class="mb-2">✏️ Modifier mes informations</h2>
                                <p class="mb-0">Mettez à jour vos informations personnelles</p>
                            </div>
                            
                            <div class="card-body p-4">
                                <form method="POST">
                                    <div class="mb-4">
                                        <label class="form-label fs-5">
                                            <strong>Nom *</strong>
                                        </label>
                                        <input type="text" name="nom" class="form-control form-control-lg" required 
                                               value="<?= htmlspecialchars($user_data['nom']) ?>"
                                               placeholder="Votre nom">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fs-5">
                                            <strong>Prénom *</strong>
                                        </label>
                                        <input type="text" name="prenom" class="form-control form-control-lg" required 
                                               value="<?= htmlspecialchars($user_data['prenom']) ?>"
                                               placeholder="Votre prénom">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fs-5">
                                            <strong>Email</strong>
                                        </label>
                                        <input type="email" class="form-control form-control-lg" 
                                               value="<?= htmlspecialchars($user_data['mail']) ?>" readonly>
                                        <div class="form-text text-muted">L'email ne peut pas être modifié</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fs-5">
                                            <strong>Téléphone</strong>
                                        </label>
                                        <input type="tel" name="tel" class="form-control form-control-lg" 
                                               value="<?= htmlspecialchars($user_tel_display) ?>" 
                                               placeholder="+229 XX XX XX XX">
                                        <div class="form-text">Numéro optionnel</div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fs-5">
                                            <strong>Statut</strong>
                                        </label>
                                        <input type="text" class="form-control form-control-lg" 
                                               value="<?= htmlspecialchars($user_data['status']) ?>" readonly>
                                        <div class="form-text text-muted">Le statut ne peut pas être modifié</div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-profile">
                                            💾 Enregistrer les modifications
                                        </button>
                                        <a href="profile.php" class="btn btn-outline-secondary">
                                            ↩️ Retour au profil
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-auto">
            <div class="container-fluid py-3">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Plateforme Scolaire</strong>
                        <small class="d-block">Système de gestion scolaire - Bénin</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small>&copy; <?= date('Y') ?> Tous droits réservés</small>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = form.querySelector('button[type="submit"]');

            // Animation de chargement
            form.addEventListener('submit', function() {
                submitBtn.innerHTML = 'Enregistrement en cours...';
                submitBtn.disabled = true;
            });
        });
    </script>
</body>
</html>