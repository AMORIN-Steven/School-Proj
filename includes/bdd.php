<?php
/**
 * Script de connexion et initialisation de base de données scolaire
 */

$host = 'localhost';
$dbname = 'ecole_benin';
$user = 'root';
$pass = '';

try {
    // Connexion initiale sans base
    $bdd = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Création de la base de données
    $bdd->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Connexion à la base
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
      `mail` VARCHAR(255),
      `Id_user` INT DEFAULT NULL,
      PRIMARY KEY (`Id_ens`),
      FOREIGN KEY (`Id_user`) REFERENCES `user`(`Id_user`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `filiere` (
      `Id_fil` INT NOT NULL AUTO_INCREMENT,
      `nom` VARCHAR(100),
      PRIMARY KEY (`Id_fil`)
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `etud` (
      `Id_etud` INT NOT NULL AUTO_INCREMENT,
      `nom` VARCHAR(100) NOT NULL,
      `prenom` VARCHAR(100),
      `mail` VARCHAR(255),
      `Id_fil` INT DEFAULT NULL,
      `matricule` VARCHAR(50) UNIQUE,
      `Id_user` INT DEFAULT NULL,
      PRIMARY KEY (`Id_etud`),
      FOREIGN KEY (`Id_user`) REFERENCES `user`(`Id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
      FOREIGN KEY (`Id_fil`) REFERENCES `filiere`(`Id_fil`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `parent` (
      `Id_parent` INT NOT NULL AUTO_INCREMENT,
      `nom` VARCHAR(100) NOT NULL,
      `prenoms` VARCHAR(100),
      `mail` VARCHAR(255),
      `Id_user` INT DEFAULT NULL,
      PRIMARY KEY (`Id_parent`),
      FOREIGN KEY (`Id_user`) REFERENCES `user`(`Id_user`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    SET FOREIGN_KEY_CHECKS=1;
    SQL;

    $bdd->exec($tablesSQL);

    // --- VIDER LES TABLES EXISTANTES ---
    $bdd->exec("SET FOREIGN_KEY_CHECKS=0");
    $tables = ['parent', 'etud', 'enseignant', 'admin', 'user', 'filiere'];
    foreach ($tables as $table) {
        $bdd->exec("TRUNCATE TABLE `$table`");
    }
    $bdd->exec("SET FOREIGN_KEY_CHECKS=1");

    // --- INSERTION DES DONNÉES DE TEST ---

    // 1. Filières
    $filieres = ['Informatique', 'Gestion', 'Droit', 'Communication', 'Agronomie'];
    $stmt = $bdd->prepare("INSERT INTO filiere(nom) VALUES (?)");
    foreach ($filieres as $f) {
        $stmt->execute([$f]);
    }

    // 2. Utilisateurs avec mots de passe EN CLAIR (pour test)
    $users = [
        // 1 Admin
        ['admin@ecole.bj', 'Admin', 'System', 'admin123', '+22901000001', 'admin'],
        
        // 3 Enseignants
        ['prof1@ecole.bj', 'Kossi', 'Agnès', 'prof123', '+22901000002', 'enseignant'],
        ['prof2@ecole.bj', 'Gbèto', 'Jean', 'prof123', '+22901000003', 'enseignant'],
        ['prof3@ecole.bj', 'Adjovi', 'Marc', 'prof123', '+22901000004', 'enseignant'],
        
        // 6 Étudiants
        ['etud1@ecole.bj', 'Ahouansou', 'Nadine', 'etud123', '+22901000005', 'etudiant'],
        ['etud2@ecole.bj', 'Tossou', 'Franck', 'etud123', '+22901000006', 'etudiant'],
        ['etud3@ecole.bj', 'Aklé', 'Josué', 'etud123', '+22901000007', 'etudiant'],
        ['etud4@ecole.bj', 'Chabi', 'Rita', 'etud123', '+22901000008', 'etudiant'],
        ['etud5@ecole.bj', 'Adjahoui', 'Karel', 'etud123', '+22901000009', 'etudiant'],
        ['etud6@ecole.bj', 'Soglo', 'Prisca', 'etud123', '+22901000010', 'etudiant'],
        
        // 6 Parents
        ['parent1@ecole.bj', 'Hounkpati', 'Céline', 'parent123', '+22901000011', 'parent'],
        ['parent2@ecole.bj', 'Adoh', 'Yvan', 'parent123', '+22901000012', 'parent'],
        ['parent3@ecole.bj', 'Gbeto', 'Louise', 'parent123', '+22901000013', 'parent'],
        ['parent4@ecole.bj', 'Adjovi', 'Thérèse', 'parent123', '+22901000014', 'parent'],
        ['parent5@ecole.bj', 'Zinsou', 'Pierre', 'parent123', '+22901000015', 'parent'],
        ['parent6@ecole.bj', 'Soglo', 'René', 'parent123', '+22901000016', 'parent']
    ];

    $stmt = $bdd->prepare("INSERT INTO user(mail, nom, prenom, motdepasse, tel, status) VALUES (?,?,?,?,?,?)");
    foreach ($users as $u) {
        $stmt->execute($u);
    }

    // 3. Admins
    $admins = [
        ['Admin', 'System', 1]
    ];
    $stmt = $bdd->prepare("INSERT INTO admin(nom, prenom, Id_user) VALUES (?,?,?)");
    foreach ($admins as $a) {
        $stmt->execute($a);
    }

    // 4. Enseignants
    $enseignants = [
        ['Kossi', 'Agnès', 'prof1@ecole.bj', 2],
        ['Gbèto', 'Jean', 'prof2@ecole.bj', 3],
        ['Adjovi', 'Marc', 'prof3@ecole.bj', 4]
    ];
    $stmt = $bdd->prepare("INSERT INTO enseignant(nom, prenom, mail, Id_user) VALUES (?,?,?,?)");
    foreach ($enseignants as $e) {
        $stmt->execute($e);
    }

    // 5. Étudiants
    $etudiants = [
        ['Ahouansou', 'Nadine', 'etud1@ecole.bj', 1, 'INF001', 5],
        ['Tossou', 'Franck', 'etud2@ecole.bj', 2, 'GES001', 6],
        ['Aklé', 'Josué', 'etud3@ecole.bj', 3, 'DRO001', 7],
        ['Chabi', 'Rita', 'etud4@ecole.bj', 4, 'COM001', 8],
        ['Adjahoui', 'Karel', 'etud5@ecole.bj', 5, 'AGR001', 9],
        ['Soglo', 'Prisca', 'etud6@ecole.bj', 1, 'INF002', 10]
    ];
    $stmt = $bdd->prepare("INSERT INTO etud(nom, prenom, mail, Id_fil, matricule, Id_user) VALUES (?,?,?,?,?,?)");
    foreach ($etudiants as $e) {
        $stmt->execute($e);
    }

    // 6. Parents
    $parents = [
        ['Hounkpati', 'Céline', 'parent1@ecole.bj', 11],
        ['Adoh', 'Yvan', 'parent2@ecole.bj', 12],
        ['Gbeto', 'Louise', 'parent3@ecole.bj', 13],
        ['Adjovi', 'Thérèse', 'parent4@ecole.bj', 14],
        ['Zinsou', 'Pierre', 'parent5@ecole.bj', 15],
        ['Soglo', 'René', 'parent6@ecole.bj', 16]
    ];
    $stmt = $bdd->prepare("INSERT INTO parent(nom, prenoms, mail, Id_user) VALUES (?,?,?,?)");
    foreach ($parents as $p) {
        $stmt->execute($p);
    }

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>