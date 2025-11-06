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

// Récupérer les informations spécifiques selon le statut
$info_specifique = '';
if ($user_status === 'etudiant') {
    $req_etud = $bdd->prepare("SELECT e.matricule, f.nom as filiere 
                             FROM etud e 
                             LEFT JOIN filiere f ON e.Id_fil = f.Id_fil 
                             WHERE e.Id_user = ?");
    $req_etud->execute([$user_id]);
    $etud_data = $req_etud->fetch();
    $matricule = isset($etud_data['matricule']) ? $etud_data['matricule'] : 'Non assigné';
    $filiere = isset($etud_data['filiere']) ? $etud_data['filiere'] : 'Non assignée';
    $info_specifique = "
        <div class='row mb-2'>
            <div class='col-md-4'><strong>🎓 Matricule:</strong></div>
            <div class='col-md-8'>$matricule</div>
        </div>
        <div class='row mb-2'>
            <div class='col-md-4'><strong>🏫 Filière:</strong></div>
            <div class='col-md-8'>$filiere</div>
        </div>
    ";
} elseif ($user_status === 'enseignant') {
    $req_ens = $bdd->prepare("SELECT COUNT(*) as nb_matieres 
                            FROM matiere 
                            WHERE Id_ens IN (SELECT Id_ens FROM enseignant WHERE Id_user = ?)");
    $req_ens->execute([$user_id]);
    $ens_data = $req_ens->fetch();
    $nb_matieres = isset($ens_data['nb_matieres']) ? $ens_data['nb_matieres'] : 0;
    $info_specifique = "
        <div class='row mb-2'>
            <div class='col-md-4'><strong>📚 Matières enseignées:</strong></div>
            <div class='col-md-8'>$nb_matieres</div>
        </div>
    ";
} elseif ($user_status === 'parent') {
    $req_parent = $bdd->prepare("SELECT COUNT(*) as nb_enfants 
                               FROM parent_enfant 
                               WHERE Id_parent IN (SELECT Id_parent FROM parent WHERE Id_user = ?)");
    $req_parent->execute([$user_id]);
    $parent_data = $req_parent->fetch();
    $nb_enfants = isset($parent_data['nb_enfants']) ? $parent_data['nb_enfants'] : 0;
    $info_specifique = "
        <div class='row mb-2'>
            <div class='col-md-4'><strong>👶 Enfants suivis:</strong></div>
            <div class='col-md-8'>$nb_enfants</div>
        </div>
    ";
} elseif ($user_status === 'admin') {
    $info_specifique = "
        <div class='row mb-2'>
            <div class='col-md-4'><strong>⚡ Rôle:</strong></div>
            <div class='col-md-8'>Administrateur système</div>
        </div>
        <div class='row mb-2'>
            <div class='col-md-4'><strong>🔐 Accès:</strong></div>
            <div class='col-md-8'>Complet</div>
        </div>
    ";
}

// Préparer les données pour l'affichage
$user_tel_display = isset($user_data['tel']) && !empty($user_data['tel']) ? $user_data['tel'] : 'Non renseigné';
$created_at = isset($user_data['created_at']) ? $user_data['created_at'] : 'now';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Plateforme Scolaire</title>
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
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px 10px 0 0;
        }
        .info-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .btn-action {
            padding: 12px 30px;
            font-size: 16px;
            margin: 0 10px;
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
            <a class="nav-link active" href="profile.php">
                <span>👤 Mon Profil</span>
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
                <span class="navbar-brand">Mon Profil</span>
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
                <!-- Carte principale du profil -->
                <div class="card">
                    <div class="profile-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">👤 Profil de <?= htmlspecialchars($user_prenom . ' ' . $user_nom) ?></h2>
                                <p class="mb-0">Gérez vos informations personnelles et la sécurité de votre compte</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-light text-dark fs-6"><?= $user_status ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Section Informations Personnelles -->
                        <div class="mb-4">
                            <h4 class="text-primary mb-3">📋 Informations Personnelles</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="row">
                                            <div class="col-md-4"><strong>Nom:</strong></div>
                                            <div class="col-md-8"><?= htmlspecialchars($user_data['nom']) ?></div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="row">
                                            <div class='col-md-4'><strong>Prénom:</strong></div>
                                            <div class='col-md-8'><?= htmlspecialchars($user_data['prenom']) ?></div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="row">
                                            <div class='col-md-4'><strong>Email:</strong></div>
                                            <div class='col-md-8'><?= htmlspecialchars($user_data['mail']) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="row">
                                            <div class='col-md-4'><strong>Téléphone:</strong></div>
                                            <div class='col-md-8'><?= htmlspecialchars($user_tel_display) ?></div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="row">
                                            <div class='col-md-4'><strong>Statut:</strong></div>
                                            <div class='col-md-8'>
                                                <span class='badge bg-primary'><?= htmlspecialchars($user_data['status']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="row">
                                            <div class='col-md-4'><strong>Membre depuis:</strong></div>
                                            <div class='col-md-8'><?= date('d/m/Y', strtotime($created_at)) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Informations Spécifiques -->
                        <?php if ($info_specifique): ?>
                        <div class="mb-4">
                            <h4 class="text-info mb-3">🎯 Informations Spécifiques</h4>
                            <?= $info_specifique ?>
                        </div>
                        <?php endif; ?>

                        <!-- Section Sécurité -->
                        <div class="mb-4">
                            <h4 class="text-success mb-3">🛡️ Sécurité du Compte</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="row">
                                            <div class="col-md-4"><strong>Statut:</strong></div>
                                            <div class="col-md-8 text-success">
                                                <strong>✓ Compte actif et sécurisé</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="row">
                                            <div class="col-md-4"><strong>Dernière connexion:</strong></div>
                                            <div class="col-md-8"><?= date('d/m/Y à H:i') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="text-center mt-5 pt-4 border-top">
                            <h5 class="text-muted mb-4">Actions du profil</h5>
                            <div class="d-flex justify-content-center flex-wrap gap-3">
                                <a href="modifier_profil.php" class="btn btn-primary btn-action">
                                    ✏️ Modifier mes informations
                                </a>
                                <a href="modifier_motdepasse.php" class="btn btn-danger btn-action">
                                    🔑 Modifier mon mot de passe
                                </a>
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