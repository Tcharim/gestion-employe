CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    username VARCHAR(15) NOT NULL,
    password_hash VARCHAR(128) NOT NULL
);

--ECHELONNAGE
CREATE TABLE categorie (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(15) NOT NULL,
    indice_min INT NOT NULL,
    indice_1er_echelon INT NOT NULL
);

CREATE TABLE echellon (
    num INT NOT NULL PRIMARY KEY 
);


-- STRUCTURE
CREATE TABLE departement (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nom VARCHAR(30) NOT NULL
);

CREATE TABLE service (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nom VARCHAR(30) NOT NULL,

    id_departement INT NOT NULL,
    FOREIGN KEY (id_departement) REFERENCES departement(id)
);

CREATE TABLE poste (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nom VARCHAR(30) NOT NULL,

    id_service INT NOT NULL,
    FOREIGN KEY (id_service) REFERENCES service(id)
);

-- EMPLOIS
CREATE TABLE employe (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(30) NOT NULL,
    prenom VARCHAR(30) NOT NULL,
    date_naissance DATE NOT NULL,
    adresse VARCHAR(150) NOT NULL,
    lieu_naissance VARCHAR(50) NOT NULL,
    nationalite VARCHAR(30) NOT NULL,
    genre ENUM('m', 'f') NOT NULL,
    civilite ENUM('c', 'm', 'd', 'v') NOT NULL DEFAULT 'c',
    etat_sante ENUM('s', 'm') NOT NULL DEFAULT 's',
    service_national BOOLEAN NOT NULL,

    num_tel VARCHAR(10) NOT NULL,
    num_assurance VARCHAR(13) NOT NULL,
    num_rib VARCHAR(15) NOT NULL,
    nin VARCHAR(18) NOT NULL, -- Numéro d'identification nationale

    date_recrutement DATE NOT NULL DEFAULT (CURDATE())
);

CREATE TABLE affectation (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    date DATE NOT NULL DEFAULT (CURDATE()),

    id_employe INT NOT NULL,
    id_poste INT NOT NULL,
    FOREIGN KEY (id_employe) REFERENCES employe(id),
    FOREIGN KEY (id_poste) REFERENCES poste(id)
);

CREATE TABLE diplome (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    niveau INT NOT NULL,
    annee_obtention YEAR NOT NULL,

    id_employe INT NOT NULL,
    FOREIGN KEY (id_employe) REFERENCES employe(id)
);

CREATE TABLE enfant (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    prenom VARCHAR(30) NOT NULL,
    date_naissance DATE NOT NULL,

    id_employe INT NOT NULL,
    FOREIGN KEY (id_employe) REFERENCES employe(id)
);

CREATE TABLE conjoint (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(30) NOT NULL,
    prenom VARCHAR(30) NOT NULL,
    date_naissance DATE NOT NULL,
    lieu_naissance VARCHAR(50) NOT NULL,
    nationalite VARCHAR(30) NOT NULL,

    profession VARCHAR(50) NOT NULL,
    organisme VARCHAR(50),
    adresse_organisme VARCHAR(50),

    id_employe INT NOT NULL,
    FOREIGN KEY (id_employe) REFERENCES employe(id)
);

-- FORMULE = indice_min + indice_1er_echellon*echellon
CREATE TABLE echellonnage_employe (
    id_employe INT NOT NULL,
    id_categorie INT NOT NULL,
    id_echellon INT NOT NULL,

    date_obtention DATE NOT NULL DEFAULT (CURDATE()),

    PRIMARY KEY(id_employe, id_categorie, id_echellon),
    FOREIGN KEY (id_employe) REFERENCES employe(id),
    FOREIGN KEY (id_categorie) REFERENCES categorie(id),
    FOREIGN KEY (id_echellon) REFERENCES echellon(num)
);

-- TODO: FORMATION + DEMANDE