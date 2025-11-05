<?php
require_once 'includes/bdd.php';
require_once 'includes/auth_functions.php';

// Rediriger si déjà connecté
if (estConnecte()) {
    header('Location: dashboard.php');
    exit();
}

$erreur = '';

if ($_POST) {
    $mail = trim($_POST['mail']);
    $motdepasse = $_POST['motdepasse'];
    
    if (empty($mail) || empty($motdepasse)) {
        $erreur = "Veuillez remplir tous les champs";
    } else {
        if (connecterUtilisateur($mail, $motdepasse, $bdd)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $erreur = "Email ou mot de passe incorrect";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Plateforme Scolaire</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            max-width: 400px;
            margin: 0 auto;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px 15px 0 0;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card login-card">
                    <div class="card-header login-header text-white text-center">
                        <h3 class="mb-2">Plateforme Scolaire</h3>
                        <p class="mb-0 opacity-75">Système de gestion scolaire</p>
                    </div>
                    <div class="card-body p-4">
                        <h4 class="text-center mb-4">Connexion</h4>
                        
                        <?php if ($erreur): ?>
                            <div class="alert alert-danger"><?= $erreur ?></div>
                        <?php endif; ?>

                        <form method="POST" id="formConnexion">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="mail" class="form-control" required 
                                       value="<?= $_POST['mail'] ?? '' ?>" placeholder="votre@email.com">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="motdepasse" class="form-control" required 
                                       placeholder="Votre mot de passe">
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg">Se connecter</button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="mot_de_passe_oublie.php" class="text-decoration-none">Mot de passe oublié ?</a>
                        </div>

                        <hr>

                        <div class="text-center">
                            <p class="mb-2">Pas encore de compte ?</p>
                            <div class="btn-group w-100">
                                <a href="inscription.php?type=etudiant" class="btn btn-outline-primary">Étudiant</a>
                                <a href="inscription.php?type=parent" class="btn btn-outline-success">Parent</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formConnexion');
            const submitBtn = form.querySelector('button[type="submit"]');

            // Animation de chargement
            form.addEventListener('submit', function() {
                submitBtn.innerHTML = 'Connexion...';
                submitBtn.disabled = true;
            });

            // Focus sur le champ email au chargement
            document.querySelector('input[name="mail"]').focus();
        });
    </script>
</body>
</html>