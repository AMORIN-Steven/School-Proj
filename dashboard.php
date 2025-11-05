<?php
require_once 'includes/bdd.php';
require_once 'includes/auth_functions.php';

verifierConnexion();

$user_id = $_SESSION['user_id'];
$user_nom = $_SESSION['user_nom'];
$user_prenom = $_SESSION['user_prenom'];
$user_status = $_SESSION['user_status'];
$user_mail = $_SESSION['user_mail'];

// Fonction pour obtenir le nom du statut
function getStatusName($status) {
    $statusNames = [
        'admin' => 'Administrateur',
        'enseignant' => 'Enseignant', 
        'etudiant' => 'Étudiant',
        'parent' => 'Parent'
    ];
    return $statusNames[$status] ?? $status;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Plateforme Scolaire</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
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
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .content-wrapper {
            flex: 1;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .border-left-primary { border-left: 4px solid #4e73df !important; }
        .border-left-success { border-left: 4px solid #1cc88a !important; }
        .border-left-info { border-left: 4px solid #36b9cc !important; }
        .border-left-warning { border-left: 4px solid #f6c23e !important; }
        footer {
            margin-top: auto;
            background: #343a40;
            color: white;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                text-align: center;
            }
            .sidebar .nav-link span {
                display: none;
            }
            .sidebar .nav-link i {
                margin-right: 0;
            }
            .main-content {
                margin-left: 70px;
            }
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
            <a class="nav-link active" href="dashboard.php">
                <i>📊</i> <span>Dashboard</span>
            </a>
            
            <?php if ($user_status === 'admin'): ?>
                <a class="nav-link" href="#">
                    <i>🏫</i> <span>Filières</span>
                </a>
                <a class="nav-link" href="#">
                    <i>👨‍🏫</i> <span>Enseignants</span>
                </a>
                <a class="nav-link" href="#">
                    <i>👨‍🎓</i> <span>Étudiants</span>
                </a>
                <a class="nav-link" href="#">
                    <i>👨‍👩‍👧‍👦</i> <span>Parents</span>
                </a>
            <?php elseif ($user_status === 'enseignant'): ?>
                <a class="nav-link" href="#">
                    <i>📚</i> <span>Mes matières</span>
                </a>
                <a class="nav-link" href="#">
                    <i>📝</i> <span>Saisie des notes</span>
                </a>
                <a class="nav-link" href="#">
                    <i>👨‍🎓</i> <span>Mes étudiants</span>
                </a>
            <?php elseif ($user_status === 'etudiant'): ?>
                <a class="nav-link" href="#">
                    <i>📋</i> <span>Mes relevés</span>
                </a>
                <a class="nav-link" href="#">
                    <i>📊</i> <span>Mes notes</span>
                </a>
                <a class="nav-link" href="#">
                    <i>📅</i> <span>Emploi du temps</span>
                </a>
            <?php elseif ($user_status === 'parent'): ?>
                <a class="nav-link" href="#">
                    <i>👶</i> <span>Mes enfants</span>
                </a>
                <a class="nav-link" href="#">
                    <i>📊</i> <span>Suivi scolaire</span>
                </a>
            <?php endif; ?>
            
            <a class="nav-link" href="profile.php">
                <i>👤</i> <span>Mon profil</span>
            </a>
            <a class="nav-link" href="settings.php">
                <i>⚙️</i> <span>Paramètres</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-light bg-white">
            <div class="container-fluid">
                <button class="btn btn-outline-primary" id="sidebarToggle">
                    ☰ Menu
                </button>
                
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <strong><?= htmlspecialchars($user_prenom . ' ' . $user_nom) ?></strong>
                            <span class="badge bg-primary ms-1"><?= getStatusName($user_status) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">👤 Mon profil</a></li>
                            <li><a class="dropdown-item" href="settings.php">⚙️ Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">🚪 Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Content -->
        <div class="content-wrapper">
            <div class="container-fluid">
                <!-- En-tête -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h3 mb-1">Tableau de bord</h1>
                                <p class="text-muted mb-0">
                                    Bienvenue, <?= htmlspecialchars($user_prenom . ' ' . $user_nom) ?> 
                                    <span class="badge bg-primary"><?= getStatusName($user_status) ?></span>
                                </p>
                            </div>
                            <div class="text-end">
                                <small class="text-muted"><?= date('d/m/Y à H:i') ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques Admin -->
                <?php if ($user_status === 'admin'): ?>
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="text-primary">Étudiants</div>
                                            <div class="h4 mb-0">6</div>
                                        </div>
                                        <div class="align-self-center">
                                            <span style="font-size: 2rem;">👨‍🎓</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="text-success">Enseignants</div>
                                            <div class="h4 mb-0">3</div>
                                        </div>
                                        <div class="align-self-center">
                                            <span style="font-size: 2rem;">👨‍🏫</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="text-info">Filières</div>
                                            <div class="h4 mb-0">5</div>
                                        </div>
                                        <div class="align-self-center">
                                            <span style="font-size: 2rem;">🏫</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="text-warning">Parents</div>
                                            <div class="h4 mb-0">6</div>
                                        </div>
                                        <div class="align-self-center">
                                            <span style="font-size: 2rem;">👨‍👩‍👧‍👦</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="m-0">Actions rapides</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <a href="#" class="btn btn-outline-primary w-100">
                                                <span>🏫</span> Nouvelle filière
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="#" class="btn btn-outline-success w-100">
                                                <span>👨‍🏫</span> Ajouter enseignant
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="#" class="btn btn-outline-info w-100">
                                                <span>👨‍🎓</span> Voir étudiants
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="#" class="btn btn-outline-warning w-100">
                                                <span>⚙️</span> Paramètres
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Contenu pour autres statuts -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <h3>Bienvenue sur votre espace <?= getStatusName($user_status) ?></h3>
                                    <p class="text-muted">Votre contenu personnalisé sera affiché ici</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
        // Toggle sidebar sur mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (sidebar.style.width === '70px' || window.innerWidth <= 768) {
                if (sidebar.style.width === '250px') {
                    sidebar.style.width = '70px';
                    mainContent.style.marginLeft = '70px';
                } else {
                    sidebar.style.width = '250px';
                    mainContent.style.marginLeft = '250px';
                }
            }
        });
    </script>
</body>
</html>