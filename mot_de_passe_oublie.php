<?php
require_once 'includes/bdd.php';
require_once 'includes/auth_functions.php';

// Rediriger si déjà connecté
if (estConnecte()) {
    header('Location: dashboard.php');
    exit();
}

$message = '';

if ($_POST) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = "Veuillez entrer votre email";
    } else {
        // Vérifier si l'email existe dans la base
        $req = $bdd->prepare("SELECT * FROM user WHERE mail = ?");
        $req->execute([$email]);
        $user = $req->fetch();
        
        if ($user) {
            // Email existe - simuler l'envoi d'email
            $message = "✅ Un lien de réinitialisation a été envoyé à votre adresse email";
        } else {
            // Email n'existe pas - message générique pour la sécurité
            $message = "✅ Si l'email existe, un lien de réinitialisation a été envoyé";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Plateforme Scolaire</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .password-card {
            max-width: 450px;
            margin: 0 auto;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .password-header {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            border-radius: 15px 15px 0 0;
            padding: 2rem;
        }
        .btn-password {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card password-card">
                    <div class="card-header password-header text-dark text-center">
                        <h3 class="mb-2">🔑 Mot de passe oublié</h3>
                        <p class="mb-0">Réinitialisez votre mot de passe</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($message): ?>
                            <div class="alert alert-info">
                                <?= $message ?>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mb-4">
                            <p class="text-muted">
                                Entrez votre adresse email pour recevoir un lien de réinitialisation.
                            </p>
                        </div>

                        <form method="POST" id="formMotDePasseOublie">
                            <div class="mb-3">
                                <label class="form-label">Adresse email *</label>
                                <input type="email" name="email" class="form-control" required 
                                       placeholder="votre@email.com"
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-password text-dark btn-lg">
                                    📧 Envoyer le lien de réinitialisation
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <a href="connexion.php" class="text-decoration-none">
                                ← Retour à la page de connexion
                            </a>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <small class="text-muted">
                                <strong>Note :</strong> Pour cette démonstration, aucun email n'est réellement envoyé.
                                En production, un lien de réinitialisation serait envoyé à votre adresse email.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-white mb-0">
                        &copy; <?= date('Y') ?> Plateforme Scolaire - Tous droits réservés
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formMotDePasseOublie');
            const submitBtn = form.querySelector('button[type="submit"]');

            // Animation de chargement
            form.addEventListener('submit', function() {
                submitBtn.innerHTML = 'Envoi en cours...';
                submitBtn.disabled = true;
            });

            // Focus sur le champ email
            document.querySelector('input[name="email"]').focus();
        });
    </script>
</body>
</html>