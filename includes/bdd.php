<?php
/**
 * Script de connexion et initialisation de base de données scolaire (Afrique / Bénin)
 * Auteur : DG BLT
 * Date : 2025
 * Version INTELLIGENTE - Préserve les données
 */

$host = 'localhost';
$dbname = 'ecole_benin';
$user = 'root';
$pass = '';

try {
    // Connexion et création de la BDD
    $bdd = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $bdd->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // --- Création des tables ---
    $tablesSQL = <<<SQL
    SET FOREIGN_KEY_CHECKS=0;

    CREATE TABLE IF NOT EXISTS `user` (
      `Id_user` INT NOT NULL AUTO_INCREMENT,
      `mail` VARCHAR(255) NOT NULL UNIQUE,
      `nom` VARCHAR(100) NOT NULL,
      `prenom` VARCHAR(100) NOT NULL,
      `motdepasse` VARCHAR(255) NOT NULL,
      `tel` VARCHAR(20) DEFAULT NULL,
      `status` VARCHAR(50) DEFAULT NULL,
      PRIMARY KEY (`Id_user`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS `admin` (
      `Id_admin` INT NOT NULL AUTO_INCREMENT,
      `nom` VARCHAR(100) NOT NULL,
      `prenom` VARCHAR(100) NOT NULL,
      `Id_user` INT DEFAULT NULL,
      PRIMARY KEY (`Id_admin`),
      FOREIGN KEY (`Id_user`) REFERENCES `user`(`Id_user`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `enseignant` (
      `Id_ens` INT NOT NULL AUTO_INCREMENT,
      `nom` VARCHAR(100),
      `prenom` VARCHAR(100),
      `mail` VARCHAR(255) UNIQUE,
      `Id_user` INT DEFAULT NULL,
      PRIMARY KEY (`Id_ens`),
      FOREIGN KEY (`Id_user`) REFERENCES `user`(`Id_user`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `filiere` (
      `Id_fil` INT NOT NULL AUTO_INCREMENT,
      `nom` VARCHAR(100) UNIQUE,
      PRIMARY KEY (`Id_fil`)
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `etud` (
      `Id_etud` INT NOT NULL AUTO_INCREMENT,
      `nom` VARCHAR(100) NOT NULL,
      `prenom` VARCHAR(100),
      `mail` VARCHAR(255) UNIQUE,
      `Id_fil` INT DEFAULT NULL,
      `matricule` VARCHAR(50) UNIQUE,
      `Id_user` INT DEFAULT NULL,
      PRIMARY KEY (`Id_etud`),
      FOREIGN KEY (`Id_user`) REFERENCES `user`(`Id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
      FOREIGN KEY (`Id_fil`) REFERENCES `filiere`(`Id_fil`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `matiere` (
      `Id_mat` INT NOT NULL AUTO_INCREMENT,
      `nom` VARCHAR(100) UNIQUE,
      `Id_ens` INT DEFAULT NULL,
      PRIMARY KEY (`Id_mat`),
      FOREIGN KEY (`Id_ens`) REFERENCES `enseignant`(`Id_ens`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `filliere_matiere` (
      `Id_fil_mat` INT NOT NULL AUTO_INCREMENT,
      `Id_fil` INT DEFAULT NULL,
      `Id_mat` INT DEFAULT NULL,
      PRIMARY KEY (`Id_fil_mat`),
      UNIQUE KEY `unique_filiere_matiere` (`Id_fil`, `Id_mat`),
      FOREIGN KEY (`Id_fil`) REFERENCES `filiere`(`Id_fil`) ON DELETE CASCADE ON UPDATE CASCADE,
      FOREIGN KEY (`Id_mat`) REFERENCES `matiere`(`Id_mat`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `note` (
      `Id_note` INT NOT NULL AUTO_INCREMENT,
      `Id_ens` INT DEFAULT NULL,
      `Id_mat` INT DEFAULT NULL,
      `Id_etud` INT DEFAULT NULL,
      `cc` FLOAT DEFAULT NULL,
      `exam` FLOAT DEFAULT NULL,
      PRIMARY KEY (`Id_note`),
      UNIQUE KEY `unique_notes` (`Id_etud`, `Id_mat`),
      FOREIGN KEY (`Id_ens`) REFERENCES `enseignant`(`Id_ens`) ON DELETE SET NULL ON UPDATE CASCADE,
      FOREIGN KEY (`Id_mat`) REFERENCES `matiere`(`Id_mat`) ON DELETE SET NULL ON UPDATE CASCADE,
      FOREIGN KEY (`Id_etud`) REFERENCES `etud`(`Id_etud`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `parent` (
      `Id_parent` INT NOT NULL AUTO_INCREMENT,
      `nom` VARCHAR(100) NOT NULL,
      `prenoms` VARCHAR(100),
      `mail` VARCHAR(255) UNIQUE,
      `Id_user` INT DEFAULT NULL,
      PRIMARY KEY (`Id_parent`),
      FOREIGN KEY (`Id_user`) REFERENCES `user`(`Id_user`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `parent_enfant` (
      `Id_par_enf` INT NOT NULL AUTO_INCREMENT,
      `Id_parent` INT DEFAULT NULL,
      `Id_etud` INT DEFAULT NULL,
      `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`Id_par_enf`),
      UNIQUE KEY `unique_parent_enfant` (`Id_parent`, `Id_etud`),
      FOREIGN KEY (`Id_parent`) REFERENCES `parent`(`Id_parent`) ON DELETE CASCADE ON UPDATE CASCADE,
      FOREIGN KEY (`Id_etud`) REFERENCES `etud`(`Id_etud`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `programme` (
      `Id_prog` INT NOT NULL AUTO_INCREMENT,
      `Id_ens` INT DEFAULT NULL,
      `Id_fil` INT DEFAULT NULL,
      `titre` VARCHAR(255),
      `Id_user` INT DEFAULT NULL,
      `salle` VARCHAR(50),
      `date_debut` DATE DEFAULT NULL,
      `date_fin` DATE DEFAULT NULL,
      PRIMARY KEY (`Id_prog`),
      FOREIGN KEY (`Id_ens`) REFERENCES `enseignant`(`Id_ens`) ON DELETE SET NULL ON UPDATE CASCADE,
      FOREIGN KEY (`Id_fil`) REFERENCES `filiere`(`Id_fil`) ON DELETE SET NULL ON UPDATE CASCADE,
      FOREIGN KEY (`Id_user`) REFERENCES `user`(`Id_user`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    SET FOREIGN_KEY_CHECKS=1;
    SQL;

    $bdd->exec($tablesSQL);

    // --- Fonctions utilitaires ---
    function insertIfNotExists($bdd, $table, $data) {
        try {
            $fields = implode(',', array_keys($data));
            $placeholders = implode(',', array_fill(0, count($data), '?'));
            $stmt = $bdd->prepare("INSERT INTO $table ($fields) VALUES ($placeholders)");
            $stmt->execute(array_values($data));
            return true;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return false;
            } else {
                throw $e;
            }
        }
    }

    function shouldInsertDemoData($bdd) {
        // Insère les données de démo SEULEMENT si la base est vide
        $count = $bdd->query("SELECT COUNT(*) FROM user")->fetchColumn();
        return ($count == 0);
    }

    // --- INSERTION INTELLIGENTE ---
    if (shouldInsertDemoData($bdd)) {
        echo "🔄 Initialisation de la base avec les données de démo...<br>";

        // 1. UTILISATEURS (15 users - TOUS utilisés)
        $users = [
            // Admins (2) - AVEC profils admin
            ['mail' => 'admin.ecole@ub.bj', 'nom' => 'Admin', 'prenom' => 'Système', 'motdepasse' => 'pass123', 'tel' => '+22961000001', 'status' => 'admin'],
            ['mail' => 'direction@ub.bj', 'nom' => 'Directeur', 'prenom' => 'École', 'motdepasse' => 'pass123', 'tel' => '+22961000002', 'status' => 'admin'],
            
            // Enseignants (3) - AVEC profils enseignant
            ['mail' => 'marc.adjovi@ub.bj', 'nom' => 'Adjovi', 'prenom' => 'Marc', 'motdepasse' => 'pass123', 'tel' => '+22962000003', 'status' => 'enseignant'],
            ['mail' => 'jean.gbeto@ub.bj', 'nom' => 'Gbèto', 'prenom' => 'Jean', 'motdepasse' => 'pass123', 'tel' => '+22962000004', 'status' => 'enseignant'],
            ['mail' => 'clarisse.zinsou@ub.bj', 'nom' => 'Zinsou', 'prenom' => 'Clarisse', 'motdepasse' => 'pass123', 'tel' => '+22962000005', 'status' => 'enseignant'],
            
            // Étudiants (5) - AVEC profils étudiant
            ['mail' => 'nadine.ahouansou@ub.bj', 'nom' => 'Ahouansou', 'prenom' => 'Nadine', 'motdepasse' => 'pass123', 'tel' => '+22963000006', 'status' => 'etudiant'],
            ['mail' => 'franck.tossou@ub.bj', 'nom' => 'Tossou', 'prenom' => 'Franck', 'motdepasse' => 'pass123', 'tel' => '+22963000007', 'status' => 'etudiant'],
            ['mail' => 'rita.chabi@ub.bj', 'nom' => 'Chabi', 'prenom' => 'Rita', 'motdepasse' => 'pass123', 'tel' => '+22963000008', 'status' => 'etudiant'],
            ['mail' => 'karel.adjahoui@ub.bj', 'nom' => 'Adjahoui', 'prenom' => 'Karel', 'motdepasse' => 'pass123', 'tel' => '+22963000009', 'status' => 'etudiant'],
            ['mail' => 'prisca.soglo@ub.bj', 'nom' => 'Soglo', 'prenom' => 'Prisca', 'motdepasse' => 'pass123', 'tel' => '+22963000010', 'status' => 'etudiant'],
            
            // Parents (5) - AVEC profils parent
            ['mail' => 'jean.ahouansou@ub.bj', 'nom' => 'Ahouansou', 'prenom' => 'Jean', 'motdepasse' => 'pass123', 'tel' => '+22964000011', 'status' => 'parent'],
            ['mail' => 'martial.tossou@ub.bj', 'nom' => 'Tossou', 'prenom' => 'Martial', 'motdepasse' => 'pass123', 'tel' => '+22964000012', 'status' => 'parent'],
            ['mail' => 'julienne.chabi@ub.bj', 'nom' => 'Chabi', 'prenom' => 'Julienne', 'motdepasse' => 'pass123', 'tel' => '+22964000013', 'status' => 'parent'],
            ['mail' => 'pierre.adjahoui@ub.bj', 'nom' => 'Adjahoui', 'prenom' => 'Pierre', 'motdepasse' => 'pass123', 'tel' => '+22964000014', 'status' => 'parent'],
            ['mail' => 'rene.soglo@ub.bj', 'nom' => 'Soglo', 'prenom' => 'René', 'motdepasse' => 'pass123', 'tel' => '+22964000015', 'status' => 'parent'],
        ];
        
        foreach ($users as $user) {
            insertIfNotExists($bdd, 'user', $user);
        }

        // 2. ADMINS (2 admins - liés aux users admin)
        $admins = [
            ['nom' => 'Admin', 'prenom' => 'Système', 'Id_user' => 1],
            ['nom' => 'Directeur', 'prenom' => 'École', 'Id_user' => 2],
        ];
        
        foreach ($admins as $admin) {
            insertIfNotExists($bdd, 'admin', $admin);
        }

        // 3. FILIÈRES (5 filières)
        $filieres = ['Informatique', 'Gestion', 'Droit', 'Communication', 'Agronomie'];
        foreach ($filieres as $f) {
            insertIfNotExists($bdd, 'filiere', ['nom' => $f]);
        }

        // 4. ENSEIGNANTS (3 enseignants - liés aux users enseignant)
        $enseignants = [
            ['nom' => 'Adjovi', 'prenom' => 'Marc', 'mail' => 'marc.adjovi@ub.bj', 'Id_user' => 3],
            ['nom' => 'Gbèto', 'prenom' => 'Jean', 'mail' => 'jean.gbeto@ub.bj', 'Id_user' => 4],
            ['nom' => 'Zinsou', 'prenom' => 'Clarisse', 'mail' => 'clarisse.zinsou@ub.bj', 'Id_user' => 5],
        ];
        
        foreach ($enseignants as $e) {
            insertIfNotExists($bdd, 'enseignant', $e);
        }

        // 5. ÉTUDIANTS (5 étudiants - liés aux users étudiant)
        $etudiants = [
            ['nom' => 'Ahouansou', 'prenom' => 'Nadine', 'mail' => 'nadine.ahouansou@ub.bj', 'Id_fil' => 1, 'matricule' => 'INF001', 'Id_user' => 6],
            ['nom' => 'Tossou', 'prenom' => 'Franck', 'mail' => 'franck.tossou@ub.bj', 'Id_fil' => 2, 'matricule' => 'GES001', 'Id_user' => 7],
            ['nom' => 'Chabi', 'prenom' => 'Rita', 'mail' => 'rita.chabi@ub.bj', 'Id_fil' => 3, 'matricule' => 'DRO001', 'Id_user' => 8],
            ['nom' => 'Adjahoui', 'prenom' => 'Karel', 'mail' => 'karel.adjahoui@ub.bj', 'Id_fil' => 4, 'matricule' => 'COM001', 'Id_user' => 9],
            ['nom' => 'Soglo', 'prenom' => 'Prisca', 'mail' => 'prisca.soglo@ub.bj', 'Id_fil' => 5, 'matricule' => 'AGR001', 'Id_user' => 10],
        ];
        
        foreach ($etudiants as $e) {
            insertIfNotExists($bdd, 'etud', $e);
        }

        // 6. MATIÈRES (5 matières)
        $matieres = ['Mathématiques', 'Programmation', 'Comptabilité', 'Droit civil', 'Communication'];
        foreach ($matieres as $i => $m) {
            insertIfNotExists($bdd, 'matiere', ['nom' => $m, 'Id_ens' => ($i % 3) + 1]);
        }

        // 7. PARENTS (5 parents - liés aux users parent)
        $parents = [
            ['nom' => 'Ahouansou', 'prenoms' => 'Jean', 'mail' => 'jean.ahouansou@ub.bj', 'Id_user' => 11],
            ['nom' => 'Tossou', 'prenoms' => 'Martial', 'mail' => 'martial.tossou@ub.bj', 'Id_user' => 12],
            ['nom' => 'Chabi', 'prenoms' => 'Julienne', 'mail' => 'julienne.chabi@ub.bj', 'Id_user' => 13],
            ['nom' => 'Adjahoui', 'prenoms' => 'Pierre', 'mail' => 'pierre.adjahoui@ub.bj', 'Id_user' => 14],
            ['nom' => 'Soglo', 'prenoms' => 'René', 'mail' => 'rene.soglo@ub.bj', 'Id_user' => 15],
        ];
        
        foreach ($parents as $p) {
            insertIfNotExists($bdd, 'parent', $p);
        }

        // 8. RELATIONS PARENT-ENFANT (COHÉRENTES et COMPLÈTES)
        $relations_parent_enfant = [
            // Famille Ahouansou
            ['Id_parent' => 1, 'Id_etud' => 1], // Jean Ahouansou → Nadine Ahouansou (fille)
            
            // Famille Tossou  
            ['Id_parent' => 2, 'Id_etud' => 2], // Martial Tossou → Franck Tossou (fils)
            
            // Famille Chabi
            ['Id_parent' => 3, 'Id_etud' => 3], // Julienne Chabi → Rita Chabi (fille)
            
            // Famille Adjahoui
            ['Id_parent' => 4, 'Id_etud' => 4], // Pierre Adjahoui → Karel Adjahoui (fils)
            
            // Famille Soglo
            ['Id_parent' => 5, 'Id_etud' => 5], // René Soglo → Prisca Soglo (fille)
            
            // Relations supplémentaires (familles recomposées)
            ['Id_parent' => 1, 'Id_etud' => 4], // Jean Ahouansou → Karel Adjahoui (neveu)
            ['Id_parent' => 2, 'Id_etud' => 5], // Martial Tossou → Prisca Soglo (belle-fille)
        ];
        
        foreach ($relations_parent_enfant as $r) {
            insertIfNotExists($bdd, 'parent_enfant', $r);
        }

        // 9. RELATIONS FILIÈRE-MATIÈRE (COHÉRENTES)
        $relations_filiere_matiere = [
            // Informatique
            ['Id_fil' => 1, 'Id_mat' => 1], // Mathématiques
            ['Id_fil' => 1, 'Id_mat' => 2], // Programmation
            
            // Gestion  
            ['Id_fil' => 2, 'Id_mat' => 1], // Mathématiques
            ['Id_fil' => 2, 'Id_mat' => 3], // Comptabilité
            
            // Droit
            ['Id_fil' => 3, 'Id_mat' => 4], // Droit civil
            
            // Communication
            ['Id_fil' => 4, 'Id_mat' => 5], // Communication
            
            // Agronomie
            ['Id_fil' => 5, 'Id_mat' => 1], // Mathématiques
        ];
        
        foreach ($relations_filiere_matiere as $r) {
            insertIfNotExists($bdd, 'filliere_matiere', $r);
        }

        // 10. NOTES (COHÉRENTES avec étudiants et matières existantes)
        $notes = [
            // Étudiant 1 (Nadine) - Informatique
            ['Id_ens' => 1, 'Id_mat' => 1, 'Id_etud' => 1, 'cc' => 14, 'exam' => 16], // Maths
            ['Id_ens' => 1, 'Id_mat' => 2, 'Id_etud' => 1, 'cc' => 15, 'exam' => 17], // Programmation
            
            // Étudiant 2 (Franck) - Gestion
            ['Id_ens' => 1, 'Id_mat' => 1, 'Id_etud' => 2, 'cc' => 12, 'exam' => 14], // Maths
            ['Id_ens' => 2, 'Id_mat' => 3, 'Id_etud' => 2, 'cc' => 16, 'exam' => 15], // Comptabilité
            
            // Étudiant 3 (Rita) - Droit
            ['Id_ens' => 3, 'Id_mat' => 4, 'Id_etud' => 3, 'cc' => 13, 'exam' => 16], // Droit civil
            
            // Étudiant 4 (Karel) - Communication
            ['Id_ens' => 3, 'Id_mat' => 5, 'Id_etud' => 4, 'cc' => 14, 'exam' => 15], // Communication
            
            // Étudiant 5 (Prisca) - Agronomie
            ['Id_ens' => 1, 'Id_mat' => 1, 'Id_etud' => 5, 'cc' => 11, 'exam' => 13], // Maths
        ];
        
        foreach ($notes as $n) {
            insertIfNotExists($bdd, 'note', $n);
        }

        // 11. PROGRAMMES (COHÉRENTS - utilisent les users existants)
        $programmes = [
            ['Id_ens' => 1, 'Id_fil' => 1, 'titre' => 'Cours de Programmation', 'Id_user' => 3, 'salle' => 'Salle A1', 'date_debut' => '2025-01-10', 'date_fin' => '2025-02-10'],
            ['Id_ens' => 2, 'Id_fil' => 2, 'titre' => 'Cours de Comptabilité', 'Id_user' => 4, 'salle' => 'Salle B2', 'date_debut' => '2025-01-15', 'date_fin' => '2025-02-15'],
            ['Id_ens' => 3, 'Id_fil' => 3, 'titre' => 'Cours de Droit civil', 'Id_user' => 5, 'salle' => 'Salle C3', 'date_debut' => '2025-01-20', 'date_fin' => '2025-02-20'],
        ];
        
        foreach ($programmes as $p) {
            insertIfNotExists($bdd, 'programme', $p);
        }

        echo "✅ Données de démo insérées avec succès !<br>";
        echo "📧 Comptes de test créés :<br>";
        echo "&nbsp;&nbsp;• Admin: <strong>admin.ecole@ub.bj</strong> / <strong>pass123</strong><br>";
        echo "&nbsp;&nbsp;• Étudiant: <strong>nadine.ahouansou@ub.bj</strong> / <strong>pass123</strong><br>";
        echo "&nbsp;&nbsp;• Enseignant: <strong>marc.adjovi@ub.bj</strong> / <strong>pass123</strong><br>";
        echo "&nbsp;&nbsp;• Parent: <strong>jean.ahouansou@ub.bj</strong> / <strong>pass123</strong><br>";
        
    } else {
        // Affiche les statistiques sans toucher aux données
        echo "✅ Base de données déjà initialisée<br>";
        echo "📊 Statistiques actuelles :<br>";
        
        $stats = $bdd->query("
            SELECT 'Utilisateurs' as type, COUNT(*) as count FROM user
            UNION SELECT 'Étudiants', COUNT(*) FROM etud  
            UNION SELECT 'Enseignants', COUNT(*) FROM enseignant
            UNION SELECT 'Parents', COUNT(*) FROM parent
            UNION SELECT 'Notes', COUNT(*) FROM note
            UNION SELECT 'Programmes', COUNT(*) FROM programme
        ")->fetchAll();
        
        foreach ($stats as $stat) {
            echo "&nbsp;&nbsp;• {$stat['type']} : <strong>{$stat['count']}</strong><br>";
        }
        
        echo "<br>💡 <em>Les nouvelles données sont préservées</em><br>";
    }

} catch (PDOException $e) {
    die("❌ Erreur : " . $e->getMessage());
}
?>