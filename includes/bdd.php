<?php
/**
 * Script de connexion et initialisation de base de données scolaire (Afrique / Bénin)
 * Auteur : DG BLT
 * Date : 2025
 * Version améliorée avec contraintes d'unicité
 */

$host = 'localhost';
$dbname = 'ecole_benin';
$user = 'root';
$pass = '';

try {
    // Connexion initiale sans base pour créer la BDD
    $bdd = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Création de la base de données si elle n'existe pas
    $bdd->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // echo "✅ Base de données '$dbname' vérifiée/créée avec succès.<br>";

    // Connexion à la base de données
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // echo "✅ Connexion à la base de données réussie.<br><br>";

    // --- Création des tables avec contraintes d'unicité ---
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
    // echo "✅ Tables créées avec succès.<br><br>";

    // --- Insertion des données africaines / béninoises ---
    // echo "⏳ Insertion des données exemples...<br>";

    // Fonction pour insertion sécurisée sans doublons
    function insertIfNotExists($bdd, $table, $data) {
        try {
            $fields = implode(',', array_keys($data));
            $placeholders = implode(',', array_fill(0, count($data), '?'));
            $stmt = $bdd->prepare("INSERT INTO $table ($fields) VALUES ($placeholders)");
            $stmt->execute(array_values($data));
            return true;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                // echo "⚠️ Doublon ignoré dans $table<br>";
                return false;
            } else {
                throw $e;
            }
        }
    }

    // 1. Utilisateurs
    $users = [
        ['mail' => 'agnes.kossi@gmail.com', 'nom' => 'Kossi', 'prenom' => 'Agnès', 'motdepasse' => 'pass123', 'tel' => '+22962000001', 'status' => 'admin'],
        ['mail' => 'ahmed.ouedraogo@gmail.com', 'nom' => 'Ouedraogo', 'prenom' => 'Ahmed', 'motdepasse' => 'pass123', 'tel' => '+22671000002', 'status' => 'enseignant'],
        ['mail' => 'sarah.mensah@gmail.com', 'nom' => 'Mensah', 'prenom' => 'Sarah', 'motdepasse' => 'pass123', 'tel' => '+23354000003', 'status' => 'etudiant'],
        ['mail' => 'boris.tchedre@gmail.com', 'nom' => 'Tchedre', 'prenom' => 'Boris', 'motdepasse' => 'pass123', 'tel' => '+22997000004', 'status' => 'enseignant'],
        ['mail' => 'mariam.traore@gmail.com', 'nom' => 'Traoré', 'prenom' => 'Mariam', 'motdepasse' => 'pass123', 'tel' => '+22557000005', 'status' => 'etudiant'],
        ['mail' => 'yvan.adoh@gmail.com', 'nom' => 'Adoh', 'prenom' => 'Yvan', 'motdepasse' => 'pass123', 'tel' => '+22966000006', 'status' => 'parent'],
        ['mail' => 'fatou.diop@gmail.com', 'nom' => 'Diop', 'prenom' => 'Fatou', 'motdepasse' => 'pass123', 'tel' => '+22177000007', 'status' => 'etudiant'],
        ['mail' => 'david.ekoue@gmail.com', 'nom' => 'Ekoué', 'prenom' => 'David', 'motdepasse' => 'pass123', 'tel' => '+22890000008', 'status' => 'enseignant'],
        ['mail' => 'celine.hounkpati@gmail.com', 'nom' => 'Hounkpati', 'prenom' => 'Céline', 'motdepasse' => 'pass123', 'tel' => '+22961000009', 'status' => 'parent'],
        ['mail' => 'paul.abalo@gmail.com', 'nom' => 'Abalo', 'prenom' => 'Paul', 'motdepasse' => 'pass123', 'tel' => '+22893000010', 'status' => 'admin']
    ];
    
    foreach ($users as $user) {
        insertIfNotExists($bdd, 'user', $user);
    }

    // 2. Filières
    $filieres = ['Informatique', 'Gestion', 'Droit', 'Communication', 'Agronomie', 'Économie', 'Mathématiques', 'Physique', 'Comptabilité', 'Marketing'];
    foreach ($filieres as $f) {
        insertIfNotExists($bdd, 'filiere', ['nom' => $f]);
    }

    // 3. Enseignants
    $enseignants = [
        ['nom' => 'Adjovi', 'prenom' => 'Marc', 'mail' => 'marc.adjovi@ub.bj', 'Id_user' => 2],
        ['nom' => 'Gbèto', 'prenom' => 'Jean', 'mail' => 'jean.gbeto@ub.bj', 'Id_user' => 4],
        ['nom' => 'Ekoué', 'prenom' => 'David', 'mail' => 'david.ekoue@tg.tg', 'Id_user' => 8],
        ['nom' => 'Zinsou', 'prenom' => 'Clarisse', 'mail' => 'clarisse.zinsou@ub.bj', 'Id_user' => null],
        ['nom' => 'Ouattara', 'prenom' => 'Ibrahim', 'mail' => 'ibrahim.ouattara@ci.ci', 'Id_user' => null],
        ['nom' => 'Sodjinou', 'prenom' => 'Luc', 'mail' => 'luc.sodjinou@ub.bj', 'Id_user' => null],
        ['nom' => 'Ayélo', 'prenom' => 'Bénédicte', 'mail' => 'benedicte.ayelo@ub.bj', 'Id_user' => null],
        ['nom' => 'Kombaté', 'prenom' => 'Issa', 'mail' => 'issa.kombate@ub.bj', 'Id_user' => null],
        ['nom' => 'Nadjo', 'prenom' => 'Elise', 'mail' => 'elise.nadjo@ub.bj', 'Id_user' => null],
        ['nom' => 'Agossa', 'prenom' => 'Patrick', 'mail' => 'patrick.agossa@ub.bj', 'Id_user' => null]
    ];
    
    foreach ($enseignants as $e) {
        insertIfNotExists($bdd, 'enseignant', $e);
    }

    // 4. Étudiants
    $etudiants = [
        ['nom' => 'Ahouansou', 'prenom' => 'Nadine', 'mail' => 'nadine.ahouansou@ub.bj', 'Id_fil' => 1, 'matricule' => 'INF001', 'Id_user' => 3],
        ['nom' => 'Tossou', 'prenom' => 'Franck', 'mail' => 'franck.tossou@ub.bj', 'Id_fil' => 2, 'matricule' => 'GES002', 'Id_user' => 5],
        ['nom' => 'Aklé', 'prenom' => 'Josué', 'mail' => 'josue.akle@ub.bj', 'Id_fil' => 3, 'matricule' => 'DRO003', 'Id_user' => 7],
        ['nom' => 'Chabi', 'prenom' => 'Rita', 'mail' => 'rita.chabi@ub.bj', 'Id_fil' => 4, 'matricule' => 'COM004', 'Id_user' => null],
        ['nom' => 'Adjahoui', 'prenom' => 'Karel', 'mail' => 'karel.adjahoui@ub.bj', 'Id_fil' => 5, 'matricule' => 'AGR005', 'Id_user' => null],
        ['nom' => 'Soglo', 'prenom' => 'Prisca', 'mail' => 'prisca.soglo@ub.bj', 'Id_fil' => 6, 'matricule' => 'ECO006', 'Id_user' => null],
        ['nom' => 'Hounsou', 'prenom' => 'Yannick', 'mail' => 'yannick.hounsou@ub.bj', 'Id_fil' => 7, 'matricule' => 'MAT007', 'Id_user' => null],
        ['nom' => 'Tokpo', 'prenom' => 'Clarisse', 'mail' => 'clarisse.tokpo@ub.bj', 'Id_fil' => 8, 'matricule' => 'PHY008', 'Id_user' => null],
        ['nom' => 'Loko', 'prenom' => 'Maxime', 'mail' => 'maxime.loko@ub.bj', 'Id_fil' => 9, 'matricule' => 'COM009', 'Id_user' => null],
        ['nom' => 'Adjaho', 'prenom' => 'Patricia', 'mail' => 'patricia.adjaho@ub.bj', 'Id_fil' => 10, 'matricule' => 'INF010', 'Id_user' => null]
    ];
    
    foreach ($etudiants as $e) {
        insertIfNotExists($bdd, 'etud', $e);
    }

    // 5. Matières
    $matieres = ['Mathématiques', 'Programmation', 'Réseaux', 'Comptabilité', 'Communication', 'Agronomie', 'Droit civil', 'Marketing', 'Statistiques', 'Physique'];
    foreach ($matieres as $i => $m) {
        insertIfNotExists($bdd, 'matiere', ['nom' => $m, 'Id_ens' => ($i % 10) + 1]);
    }

    // 6. Notes
    for ($i = 1; $i <= 10; $i++) {
        insertIfNotExists($bdd, 'note', [
            'Id_ens' => $i, 
            'Id_mat' => $i, 
            'Id_etud' => $i, 
            'cc' => rand(8,18), 
            'exam' => rand(10,20)
        ]);
    }

    // 7. Parents
    $parents = [
        ['nom' => 'Hounkpati', 'prenoms' => 'Céline', 'mail' => 'celine.hounkpati@gmail.com', 'Id_user' => 9],
        ['nom' => 'Adoh', 'prenoms' => 'Yvan', 'mail' => 'yvan.adoh@gmail.com', 'Id_user' => 6],
        ['nom' => 'Gbeto', 'prenoms' => 'Louise', 'mail' => 'louise.gbeto@gmail.com', 'Id_user' => null],
        ['nom' => 'Adjovi', 'prenoms' => 'Thérèse', 'mail' => 'therese.adjovi@gmail.com', 'Id_user' => null],
        ['nom' => 'Zinsou', 'prenoms' => 'Pierre', 'mail' => 'pierre.zinsou@gmail.com', 'Id_user' => null],
        ['nom' => 'Soglo', 'prenoms' => 'René', 'mail' => 'rene.soglo@gmail.com', 'Id_user' => null],
        ['nom' => 'Loko', 'prenoms' => 'Justine', 'mail' => 'justine.loko@gmail.com', 'Id_user' => null],
        ['nom' => 'Agossa', 'prenoms' => 'Maurice', 'mail' => 'maurice.agossa@gmail.com', 'Id_user' => null],
        ['nom' => 'Tossou', 'prenoms' => 'Noël', 'mail' => 'noel.tossou@gmail.com', 'Id_user' => null],
        ['nom' => 'Adjaho', 'prenoms' => 'Patricia', 'mail' => 'patricia.adjaho@gmail.com', 'Id_user' => null]
    ];
    
    foreach ($parents as $p) {
        insertIfNotExists($bdd, 'parent', $p);
    }

    // 8. Relations parent-enfant
    for ($i = 1; $i <= 10; $i++) {
        insertIfNotExists($bdd, 'parent_enfant', ['Id_parent' => $i, 'Id_etud' => $i]);
    }

    // 9. Programmes
    for ($i = 1; $i <= 10; $i++) {
        insertIfNotExists($bdd, 'programme', [
            'Id_ens' => $i, 
            'Id_fil' => $i, 
            'titre' => "Cours de " . $matieres[$i-1], 
            'Id_user' => $i, 
            'salle' => "Salle " . chr(64+$i),
            'date_debut' => '2025-01-0' . (($i%9)+1), 
            'date_fin' => '2025-02-0' . (($i%9)+1)
        ]);
    }

    // 10. filliere_matiere
    $relations = [
        // Informatique
        ['Id_fil' => 1, 'Id_mat' => 2], ['Id_fil' => 1, 'Id_mat' => 3], ['Id_fil' => 1, 'Id_mat' => 9],
        // Gestion
        ['Id_fil' => 2, 'Id_mat' => 4], ['Id_fil' => 2, 'Id_mat' => 8],
        // Droit
        ['Id_fil' => 3, 'Id_mat' => 7],
        // Communication
        ['Id_fil' => 4, 'Id_mat' => 5],
        // Agronomie
        ['Id_fil' => 5, 'Id_mat' => 6],
        // Économie
        ['Id_fil' => 6, 'Id_mat' => 9],
        // Mathématiques
        ['Id_fil' => 7, 'Id_mat' => 1], ['Id_fil' => 7, 'Id_mat' => 9],
        // Physique
        ['Id_fil' => 8, 'Id_mat' => 10],
        // Comptabilité
        ['Id_fil' => 9, 'Id_mat' => 4],
        // Marketing
        ['Id_fil' => 10, 'Id_mat' => 8]
    ];

    foreach ($relations as $r) {
        insertIfNotExists($bdd, 'filliere_matiere', $r);
    }

    // echo "✅ Données insérées avec succès dans toutes les tables.";

} catch (PDOException $e) {
    die("❌ Erreur : " . $e->getMessage());
}
?>