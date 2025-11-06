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

// Récupérer les données de l'utilisateur
$req = $bdd->prepare("SELECT * FROM user WHERE Id_user = ?");
$req->execute([$user_id]);
$user_data = $req->fetch();

$message = '';
$erreur = '';

// Traitement du changement de mot de passe
if ($_POST) {
    $ancien_motdepasse = $_POST['ancien_motdepasse'];
    $nouveau_motdepasse = $_POST['nouveau_motdepasse'];
    $confirmation = $_POST['confirmation'];
    
    if (empty($ancien_motdepasse) || empty($nouveau_motdepasse) || empty($confirmation)) {
        $erreur = "Tous les champs sont obligatoires";
    } elseif ($nouveau_motdepasse !== $confirmation) {
        $erreur = "Les nouveaux mots de passe ne correspondent pas";
    } elseif (strlen($nouveau_motdepasse) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères";
    } else {
        // Vérifier l'ancien mot de passe
        if ($ancien_motdepasse === $user_data['motdepasse']) {
            // Mettre à jour le mot de passe
            try {
                $stmt = $bdd->prepare("UPDATE user SET motdepasse = ? WHERE Id_user = ?");
                $stmt->execute([$nouveau_motdepasse, $user_id]);
                $message = "✅ Mot de passe modifié avec succès";
                
            } catch (Exception $e) {
                $erreur = "❌ Erreur lors de la modification: " . $e->getMessage();
            }
        } else {
            $erreur = "❌ Ancien mot de passe incorrect";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le mot de passe - Plateforme Scolaire</title>
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
        .password-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px 10px 0 0;
        }
        .form-control {
            padding: 12px 15px;
        }
        .btn-password {
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
            <a class="nav-link" href="modifier_profil.php">
                <span>✏️ Modifier Profil</span>
            </a>
            <a class="nav-link active" href="modifier_motdepasse.php">
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
                <span class="navbar-brand">Modifier le mot de passe</span>
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
                            <div class="password-header text-center">
                                <h2 class="mb-2">🔑 Modifier mon mot de passe</h2>
                                <p class="mb-0">Pour des raisons de sécurité, veuillez confirmer votre ancien mot de passe</p>
                            </div>
                            
                            <div class="card-body p-4">
                                <form method="POST">
                                    <div class="mb-4">
                                        <label class="form-label fs-5">
                                            <strong>Ancien mot de passe *</strong>
                                        </label>
                                        <input type="password" name="ancien_motdepasse" class="form-control form-control-lg" required 
                                               placeholder="Entrez votre ancien mot de passe">
                                        <div class="form-text">Mot de passe actuel de votre compte</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fs-5">
                                            <strong>Nouveau mot de passe *</strong>
                                        </label>
                                        <input type="password" name="nouveau_motdepasse" class="form-control form-control-lg" required 
                                               placeholder="Choisissez un nouveau mot de passe">
                                        <div class="form-text">Minimum 6 caractères</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fs-5">
                                            <strong>Confirmation du nouveau mot de passe *</strong>
                                        </label>
                                        <input type="password" name="confirmation" class="form-control form-control-lg" required 
                                               placeholder="Confirmez votre nouveau mot de passe">
                                        <div class="form-text">Doit être identique au nouveau mot de passe</div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-danger btn-password">
                                            🔒 Changer le mot de passe
                                        </button>
                                        <a href="profile.php" class="btn btn-outline-secondary">
                                            ↩️ Retour au profil
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Conseils de sécurité -->
                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">💡 Conseils de sécurité</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li>✅ Utilisez au moins 8 caractères</li>
                                    <li>✅ Combinez lettres, chiffres et caractères spéciaux</li>
                                    <li>✅ Et tout les autre conseils evident (en mode n'ecris pas ton prenom) etc etc...</li>
                                </ul>
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
</body>
</html>