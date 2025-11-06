<?php
/**
 * Script de connexion et initialisation de base de données scolaire (Afrique / Bénin)
 * Auteur : DG BLT
 * Date : 2025
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

    // --- Création des tables ---
    $tablesSQL = <<<SQL
    -- toutes les tables ici (copié depuis la version corrigée avec les FK)
    SET FOREIGN_KEY_CHECKS=0;

    CREATE TABLE IF NOT EXISTS `user` (
      `Id_user` INT NOT NULL AUTO_INCREMENT,
      `mail` VARCHAR(255) NOT NULL,
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
      `matricule` VARCHAR(50),
      `Id_user` INT DEFAULT NULL,
      PRIMARY KEY (`Id_etud`),
      FOREIGN KEY (`Id_user`) REFERENCES `user`(`Id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
      FOREIGN KEY (`Id_fil`) REFERENCES `filiere`(`Id_fil`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `matiere` (
      `Id_mat` INT NOT NULL AUTO_INCREMENT,
      `nom` VARCHAR(100),
      `Id_ens` INT DEFAULT NULL,
      PRIMARY KEY (`Id_mat`),
      FOREIGN KEY (`Id_ens`) REFERENCES `enseignant`(`Id_ens`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `filliere_matiere` (
      `Id_fil_mat` INT NOT NULL AUTO_INCREMENT,
      `Id_fil` INT DEFAULT NULL,
      `Id_mat` INT DEFAULT NULL,
      PRIMARY KEY (`Id_fil_mat`),
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
      FOREIGN KEY (`Id_ens`) REFERENCES `enseignant`(`Id_ens`) ON DELETE SET NULL ON UPDATE CASCADE,
      FOREIGN KEY (`Id_mat`) REFERENCES `matiere`(`Id_mat`) ON DELETE SET NULL ON UPDATE CASCADE,
      FOREIGN KEY (`Id_etud`) REFERENCES `etud`(`Id_etud`) ON DELETE CASCADE ON UPDATE CASCADE
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

    CREATE TABLE IF NOT EXISTS `parent_enfant` (
      `Id_par_enf` INT NOT NULL AUTO_INCREMENT,
      `Id_parent` INT DEFAULT NULL,
      `Id_etud` INT DEFAULT NULL,
      `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`Id_par_enf`),
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

    // 1. Utilisateurs
    $users = [
        ['agnes.kossi@gmail.com','Kossi','Agnès','pass123','+22962000001','admin'],
        ['ahmed.ouedraogo@gmail.com','Ouedraogo','Ahmed','pass123','+22671000002','enseignant'],
        ['sarah.mensah@gmail.com','Mensah','Sarah','pass123','+23354000003','etudiant'],
        ['boris.tchedre@gmail.com','Tchedre','Boris','pass123','+22997000004','enseignant'],
        ['mariam.traore@gmail.com','Traoré','Mariam','pass123','+22557000005','etudiant'],
        ['yvan.adoh@gmail.com','Adoh','Yvan','pass123','+22966000006','parent'],
        ['fatou.diop@gmail.com','Diop','Fatou','pass123','+22177000007','etudiant'],
        ['david.ekoue@gmail.com','Ekoué','David','pass123','+22890000008','enseignant'],
        ['celine.hounkpati@gmail.com','Hounkpati','Céline','pass123','+22961000009','parent'],
        ['paul.abalo@gmail.com','Abalo','Paul','pass123','+22893000010','admin']
    ];
    $stmt = $bdd->prepare("INSERT INTO user(mail, nom, prenom, motdepasse, tel, status) VALUES (?,?,?,?,?,?)");
    foreach ($users as $u) $stmt->execute($u);

    // 2. Filières
    $filieres = ['Informatique', 'Gestion', 'Droit', 'Communication', 'Agronomie', 'Économie', 'Mathématiques', 'Physique', 'Comptabilité', 'Marketing'];
    foreach ($filieres as $f) {
        $bdd->prepare("INSERT INTO filiere(nom) VALUES (?)")->execute([$f]);
    }

    // 3. Enseignants
    $enseignants = [
        ['Adjovi','Marc','marc.adjovi@ub.bj',2],
        ['Gbèto','Jean','jean.gbeto@ub.bj',4],
        ['Ekoué','David','david.ekoue@tg.tg',8],
        ['Zinsou','Clarisse','clarisse.zinsou@ub.bj',NULL],
        ['Ouattara','Ibrahim','ibrahim.ouattara@ci.ci',NULL],
        ['Sodjinou','Luc','luc.sodjinou@ub.bj',NULL],
        ['Ayélo','Bénédicte','benedicte.ayelo@ub.bj',NULL],
        ['Kombaté','Issa','issa.kombate@ub.bj',NULL],
        ['Nadjo','Elise','elise.nadjo@ub.bj',NULL],
        ['Agossa','Patrick','patrick.agossa@ub.bj',NULL]
    ];
    $stmt = $bdd->prepare("INSERT INTO enseignant(nom, prenom, mail, Id_user) VALUES (?,?,?,?)");
    foreach ($enseignants as $e) $stmt->execute($e);

    // 4. Étudiants
    $etudiants = [
        ['Ahouansou','Nadine','nadine.ahouansou@ub.bj',1,'INF001',3],
        ['Tossou','Franck','franck.tossou@ub.bj',2,'GES002',5],
        ['Aklé','Josué','josue.akle@ub.bj',3,'DRO003',7],
        ['Chabi','Rita','rita.chabi@ub.bj',4,'COM004',NULL],
        ['Adjahoui','Karel','karel.adjahoui@ub.bj',5,'AGR005',NULL],
        ['Soglo','Prisca','prisca.soglo@ub.bj',6,'ECO006',NULL],
        ['Hounsou','Yannick','yannick.hounsou@ub.bj',7,'MAT007',NULL],
        ['Tokpo','Clarisse','clarisse.tokpo@ub.bj',8,'PHY008',NULL],
        ['Loko','Maxime','maxime.loko@ub.bj',9,'COM009',NULL],
        ['Adjaho','Patricia','patricia.adjaho@ub.bj',10,'INF010',NULL]
    ];
    $stmt = $bdd->prepare("INSERT INTO etud(nom, prenom, mail, Id_fil, matricule, Id_user) VALUES (?,?,?,?,?,?)");
    foreach ($etudiants as $e) $stmt->execute($e);

    // 5. Matières
    $matieres = ['Mathématiques', 'Programmation', 'Réseaux', 'Comptabilité', 'Communication', 'Agronomie', 'Droit civil', 'Marketing', 'Statistiques', 'Physique'];
    foreach ($matieres as $i => $m) {
        $bdd->prepare("INSERT INTO matiere(nom, Id_ens) VALUES (?, ?)")->execute([$m, ($i % 10) + 1]);
    }

    // 6. Notes
    for ($i = 1; $i <= 10; $i++) {
        $bdd->prepare("INSERT INTO note(Id_ens, Id_mat, Id_etud, cc, exam) VALUES (?,?,?,?,?)")
            ->execute([$i, $i, $i, rand(8,18), rand(10,20)]);
    }

    // 7. Parents
    $parents = [
        ['Hounkpati','Céline','celine.hounkpati@gmail.com',9],
        ['Adoh','Yvan','yvan.adoh@gmail.com',6],
        ['Gbeto','Louise','louise.gbeto@gmail.com',NULL],
        ['Adjovi','Thérèse','therese.adjovi@gmail.com',NULL],
        ['Zinsou','Pierre','pierre.zinsou@gmail.com',NULL],
        ['Soglo','René','rene.soglo@gmail.com',NULL],
        ['Loko','Justine','justine.loko@gmail.com',NULL],
        ['Agossa','Maurice','maurice.agossa@gmail.com',NULL],
        ['Tossou','Noël','noel.tossou@gmail.com',NULL],
        ['Adjaho','Patricia','patricia.adjaho@gmail.com',NULL]
    ];
    $stmt = $bdd->prepare("INSERT INTO parent(nom, prenoms, mail, Id_user) VALUES (?,?,?,?)");
    foreach ($parents as $p) $stmt->execute($p);

    // 8. Relations parent-enfant
    for ($i = 1; $i <= 10; $i++) {
        $bdd->prepare("INSERT INTO parent_enfant(Id_parent, Id_etud) VALUES (?, ?)")->execute([$i, $i]);
    }

    // 9. Programmes
    for ($i = 1; $i <= 10; $i++) {
        $bdd->prepare("INSERT INTO programme(Id_ens, Id_fil, titre, Id_user, salle, date_debut, date_fin)
                       VALUES (?, ?, ?, ?, ?, ?, ?)")->execute([
            $i, $i, "Cours de " . $matieres[$i-1], $i, "Salle " . chr(64+$i),
            '2025-01-0' . (($i%9)+1), '2025-02-0' . (($i%9)+1)
        ]);
    }

    // 10. filliere_matiere
    $relations = [
        // Informatique
        [1, 2], [1, 3], [1, 9],
        // Gestion
        [2, 4], [2, 8],
        // Droit
        [3, 7],
        // Communication
        [4, 5],
        // Agronomie
        [5, 6],
        // Économie
        [6, 9],
        // Mathématiques
        [7, 1], [7, 9],
        // Physique
        [8, 10],
        // Comptabilité
        [9, 4],
        // Marketing
        [10, 8]
    ];

    $stmt = $bdd->prepare("INSERT INTO filliere_matiere(Id_fil, Id_mat) VALUES (?, ?)");
    foreach ($relations as $r) {
        $stmt->execute($r);
    }
    // echo "✅ Données insérées dans la table 'filliere_matiere'.<br>";


    // echo "✅ Données insérées avec succès dans toutes les tables.";

} catch (PDOException $e) {
    die("❌ Erreur : " . $e->getMessage());
}
?>
