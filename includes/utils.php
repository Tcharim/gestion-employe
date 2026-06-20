<?php
    include_once "values.php";
    include_once "db.php";

    
    // Utils
    function sendError(int $code_error): void {
        header('Location: '.WORKSPACE.'/error.php?code='.$code_error);
        exit();
    }

    function isConnected(PDO $pdo): bool {
        if (!isset($_SESSION['username']) || is_null(getCompteByUsername($_SESSION['username'], $pdo))) {
            return false;
        }

        $timeout = 3600; // 1 heure en secondes

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            session_unset();
            session_destroy();
            return false;
        }

        $_SESSION['last_activity'] = time();

        return true;
    }

    // SELECT
    function getCompteByUsername(string $username, PDO $pdo): ?array {
        try{
            $stmt = $pdo->prepare('SELECT id, username, password_hash FROM utilisateur WHERE username = :username');
            if(!$stmt) return null;

            if(!$stmt->execute(['username' => $username])) return null;

            $result =  $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getAllDepartementDetails(PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare("
                SELECT 
                    d.id AS id, 
                    d.nom AS nom,
                    (
                        SELECT JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'id', s.id, 
                                'nom', s.nom,
                                'list_poste', (
                                    SELECT JSON_ARRAYAGG(
                                        JSON_OBJECT('id', p.id, 'nom', p.nom)
                                    )
                                    FROM poste p
                                    WHERE p.id_service = s.id
                                )
                            )
                        )
                        FROM service s
                        WHERE s.id_departement = d.id
                    ) AS list_service
                FROM departement d;
            ");
            if(!$stmt) return null;     

            if(!$stmt->execute()) return null;

            $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getAllServiceDetails(PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare("
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', s.id, 
                        'nom', s.nom,
                        'list_poste', (
                            SELECT JSON_ARRAYAGG(
                                JSON_OBJECT('id', p.id, 'nom', p.nom)
                            )
                            FROM poste p
                            WHERE p.id_service = s.id
                        )
                    )
                )
                FROM service s
            ");
            if(!$stmt) return null;     

            if(!$stmt->execute()) return null;

            $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getDepartementById(int $id, PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare("SELECT id, nom FROM departement WHERE id = :id");
            if(!$stmt) return null;     

            if(!$stmt->execute(["id" => $id])) return null;

            $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getServicesByIdDep(int $id_dep, PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare("SELECT id, nom FROM service WHERE id_departement = :id_dep");
            if(!$stmt) return null;     

            if(!$stmt->execute(["id_dep" => $id_dep])) return null;

            $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getPostesByIdServ(int $id_serv, PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare("SELECT id, nom FROM poste WHERE id_service = :id_serv");
            if(!$stmt) return null;     

            if(!$stmt->execute(["id_serv" => $id_serv])) return null;

            $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getAllEmploye(PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare(
                'SELECT
                    e.id id, e.nom nom, e.prenom prenom,
                    d.nom nom_departement, s.nom nom_service, p.nom nom_poste,
                    e.genre genre, e.num_tel num_tel,
                    e.date_naissance date_naissance, e.date_recrutement date_recrutement, a.date date_affectation
                FROM employe e
                LEFT JOIN affectation a ON a.id_employe = e.id
                LEFT JOIN poste p ON a.id_poste = p.id
                LEFT JOIN service s ON p.id_service = s.id
                LEFT JOIN departement d ON s.id_departement = d.id
            ');
            if(!$stmt) return null;

            if(!$stmt->execute()) return null;

            $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getEmployeQuery(string $q, PDO $pdo): ?array {
        try {
            $stmt = $pdo->prepare(
                "SELECT
                    e.id id, e.nom nom, e.prenom prenom,
                    d.nom nom_departement, s.nom nom_service, p.nom nom_poste,
                    e.genre genre, e.num_tel num_tel,
                    e.date_naissance date_naissance, e.date_recrutement date_recrutement, a.date date_affectation
                FROM employe e
                LEFT JOIN affectation a ON a.id_employe = e.id
                LEFT JOIN poste p ON a.id_poste = p.id
                LEFT JOIN service s ON p.id_service = s.id
                LEFT JOIN departement d ON s.id_departement = d.id
                WHERE CONCAT(e.nom, ' ', e.prenom) LIKE :q
                OR CONCAT(e.prenom, ' ', e.nom) LIKE :q
            ");
            if (!$stmt) return null;

            $like = '%' . $q . '%';
            if (!$stmt->execute(['q' => $like])) return null;

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ($result) ? $result : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    function getAllCategorie(PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare(
                'SELECT
                    id, nom, indice_min, indice_1er_echellon
                FROM categorie
            ');
            if(!$stmt) return null;

            if(!$stmt->execute()) return null;

            $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getEmployeDetails(int $id, PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare(
                "SELECT
                    e.id id, e.nom nom, e.prenom prenom,
                    d.id id_departement, d.nom departement_nom,
                    s.id id_service, s.nom service_nom,
                    p.id id_poste, p.nom poste_nom,
                    e.genre genre, e.num_tel num_tel, e.etat_sante etat_sante, e.civilite civilite,
                    e.nationalite nationalite, e.adresse adresse, e.num_rib num_rib, e.num_assurance num_assurance,
                    e.nin nin, e.service_national service_national,
                    e.date_naissance date_naissance, e.lieu_naissance lieu_naissance,
                    e.date_recrutement date_recrutement, a.date date_affectation,
                    cat.nom categorie_nom, ee.id_echellon echellon_num, ee.date_obtention date_obtention_echellonnement,
                    (ee.id_echellon*cat.indice_1er_echellon + cat.indice_min) indice,
                    (SELECT 
                        JSON_ARRAYAGG(JSON_OBJECT(
                            'id', dip.id,
                            'nom', dip.nom,
                            'niveau',dip.niveau,
                            'annee_obtention', dip.annee_obtention
                        ))
                    FROM diplome dip WHERE dip.id_employe = e.id ORDER BY dip.annee_obtention DESC
                    ) diplome_list,
                    (SELECT 
                        JSON_ARRAYAGG(JSON_OBJECT(
                            'id', enf.id,
                            'prenom', enf.prenom,
                            'date_naissance', enf.date_naissance
                        ))
                    FROM enfant enf WHERE enf.id_employe = e.id
                    ) enfant_list,
                    (SELECT 
                        JSON_ARRAYAGG(JSON_OBJECT(
                            'id', con.id,
                            'nom', con.nom,
                            'prenom', con.prenom,
                            'date_naissance', con.date_naissance,
                            'lieu_naissance', con.lieu_naissance,
                            'nationalite', con.nationalite,
                            'profession', con.profession,
                            'organisme', con.organisme,
                            'adresse_organisme', con.adresse_organisme
                        ))
                    FROM conjoint con WHERE con.id_employe = e.id
                    ) conjoint_list
                FROM employe e
                LEFT JOIN affectation a ON a.id_employe = e.id
                LEFT JOIN poste p ON a.id_poste = p.id
                LEFT JOIN service s ON p.id_service = s.id
                LEFT JOIN departement d ON s.id_departement = d.id
                LEFT JOIN echellonnage_employe ee ON ee.id_employe = e.id
                LEFT JOIN categorie cat on cat.id = ee.id_categorie 
                WHERE e.id = :id
            ");
            if(!$stmt) return null;

            if(!$stmt->execute(['id' => $id])) return null;

            $result =  $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getAllEchellon(PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare(
                'SELECT
                    DISTINCT num
                FROM echellon
            ');
            if(!$stmt) return null;

            if(!$stmt->execute()) return null;

            $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getServiceDetailsById(int $id, PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare("
                SELECT
                        s.id id, 
                        s.nom nom,
                    (
                        SELECT JSON_ARRAYAGG(
                            JSON_OBJECT('id', p.id, 'nom', p.nom)
                        )
                        FROM poste p
                        WHERE p.id_service = s.id
                    ) list_poste
                FROM service s
                WHERE s.id = :id
            ");
            if(!$stmt) return null;     

            if(!$stmt->execute(["id" => $id])) return null;

            $result =  $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    function getDepartementDetailsById(int $id, PDO $pdo) : ?array {
        try{
            $stmt = $pdo->prepare("
                SELECT 
                    d.id AS id, 
                    d.nom AS nom,
                    (
                        SELECT JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'id', s.id, 
                                'nom', s.nom,
                                'list_poste', (
                                    SELECT JSON_ARRAYAGG(
                                        JSON_OBJECT('id', p.id, 'nom', p.nom)
                                    )
                                    FROM poste p
                                    WHERE p.id_service = s.id
                                )
                            )
                        )
                        FROM service s
                        WHERE s.id_departement = d.id
                    ) AS list_service
                FROM departement d
                WHERE d.id = :id
            ");
            if(!$stmt) return null;     

            if(!$stmt->execute(["id" => $id])) return null;

            $result =  $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result)?$result:null;
        }
        catch(PDOException $e) {
            return null;
        }
    }

    // INSERT
    function insertDepartement(string $nom, PDO $pdo) : ?int {
        try {
            $stmt = $pdo->prepare("INSERT INTO departement(nom) VALUES (:nom);");
            if(!$stmt) return null;
            
            if(!$stmt->execute(['nom' => $nom]))
                return null;;

            return intval($pdo->lastInsertId());
        } catch (PDOException $e) {
            return null;
        }
    }

    function insertService(int $id_departement, string $nom, PDO $pdo) : ?int {
        try {
            $stmt = $pdo->prepare("INSERT INTO service(nom, id_departement) VALUES (:nom, :id_departement);");
            if(!$stmt) return null;
            
            if(!$stmt->execute(['nom' => $nom, 'id_departement' => $id_departement]))
                return null;;

            return intval($pdo->lastInsertId());
        } catch (PDOException $e) {
            return null;
        }
    }

    function insertPoste(int $id_service, string $nom, PDO $pdo) : ?int {
        try {
            $stmt = $pdo->prepare("INSERT INTO poste(nom, id_service) VALUES (:nom, :id_service);");
            if(!$stmt) return null;
            
            if(!$stmt->execute(['nom' => $nom, 'id_service' => $id_service]))
                return null;;

            return intval($pdo->lastInsertId());
        } catch (PDOException $e) {
            return null;
        }
    }
    
    function insertEmploye(stdClass $data, PDO $pdo): ?int {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO employe (
                    nom, prenom, date_naissance, adresse, lieu_naissance, nationalite, genre, civilite, etat_sante,
                    service_national, num_tel, num_assurance, num_rib, nin, date_recrutement
                )
                VALUES (
                    :nom, :prenom, :date_naissance, :adresse, :lieu_naissance, :nationalite, :genre, :civilite, :etat_sante,
                    :service_national, :num_tel, :num_assurance, :num_rib, :nin, :date_recrutement
                )
            ");

            $stmt->execute([
                'nom'               => $data->nom,
                'prenom'            => $data->prenom,
                'date_naissance'    => $data->date_naissance,
                'adresse'           => $data->adresse,
                'lieu_naissance'    => $data->lieu_naissance,
                'nationalite'       => $data->nationalite,
                'genre'             => $data->genre,
                'civilite'          => $data->civilite,
                'etat_sante'        => $data->etat_sante,
                'service_national'  => (int)$data->service_national,
                'num_tel'           => $data->num_tel,
                'num_assurance'     => $data->num_assurance,
                'num_rib'           => $data->num_rib,
                'nin'               => $data->nin,
                'date_recrutement'  => $data->date_recrutement
            ]);

            $idEmploye = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare(
                "INSERT INTO echellonnage_employe (id_employe, id_echellon, id_categorie, date_obtention) VALUES 
                    (:id_employe, :id_echellon, :id_categorie, :date_obtention)
            ");

            $stmt->execute([
                'id_employe' => $idEmploye,
                'id_echellon' => $data->id_echellon,
                'id_categorie' => $data->id_categorie,
                'date_obtention' => $data->date_obtention_echellonnement
            ]);

            return ($idEmploye)?$idEmploye:null;

        } catch (Exception $e) {

            return null;
        }
    }

    // UPDATE
    function updateDepartement(int $id, string $nom, PDO $pdo): bool {
        try {
            $stmt = $pdo->prepare("UPDATE `departement` SET nom=:nom WHERE id=:id;");
            if(!$stmt) return false;
            return $stmt->execute([
                'id' => $id,
                'nom' => $nom
            ]);
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    function updateService(int $id, string $nom, PDO $pdo): bool {
        try {
            $stmt = $pdo->prepare("UPDATE `service` SET nom=:nom WHERE id=:id;");
            if(!$stmt) return false;
            return $stmt->execute([
                'id' => $id,
                'nom' => $nom
            ]);
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    function updatePoste(int $id, string $nom, PDO $pdo): bool {
        try {
            $stmt = $pdo->prepare("UPDATE `poste` SET nom=:nom WHERE id=:id;");
            if(!$stmt) return false;
            return $stmt->execute([
                'id' => $id,
                'nom' => $nom
            ]);
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    function updateEmploye(stdClass $data, PDO $pdo): ?int {
        try {
            $stmt = $pdo->prepare("
                UPDATE employe SET
                    nom = :nom,
                    prenom = :prenom,
                    date_naissance = :date_naissance,
                    adresse = :adresse,
                    lieu_naissance = :lieu_naissance,
                    nationalite = :nationalite,
                    genre = :genre,
                    civilite = :civilite,
                    etat_sante = :etat_sante,
                    service_national = :service_national,
                    num_tel = :num_tel,
                    num_assurance = :num_assurance,
                    num_rib = :num_rib,
                    nin = :nin,
                    date_recrutement = :date_recrutement
                WHERE id = :id
            ");
            $stmt->execute([
                'nom'               => $data->nom,
                'prenom'            => $data->prenom,
                'date_naissance'    => $data->date_naissance,
                'adresse'           => $data->adresse,
                'lieu_naissance'    => $data->lieu_naissance,
                'nationalite'       => $data->nationalite,
                'genre'             => $data->genre,
                'civilite'          => $data->civilite,
                'etat_sante'        => $data->etat_sante,
                'service_national'  => (int)$data->service_national,
                'num_tel'           => $data->num_tel,
                'num_assurance'     => $data->num_assurance,
                'num_rib'           => $data->num_rib,
                'nin'               => $data->nin,
                'date_recrutement'  => $data->date_recrutement,
                'id'                => $data->id
            ]);

            $idEmploye = (int)$data->id;

            $stmt = $pdo->prepare("
                UPDATE echellonnage_employe SET
                    id_echellon = :id_echellon,
                    id_categorie = :id_categorie,
                    date_obtention = :date_obtention
                WHERE id_employe = :id_employe
            ");
            $stmt->execute([
                'id_employe'     => $idEmploye,
                'id_echellon'    => $data->id_echellon,
                'id_categorie'   => $data->id_categorie,
                'date_obtention' => $data->date_obtention_echellonnement
            ]);

            return $idEmploye;
        } catch (Exception $e) {
            return null;
        }
    }

    // DELETE
    function deletePoste(int $id, PDO $pdo): bool {
        try {
            $stmt = $pdo->prepare("DELETE FROM affectation WHERE id_poste = :id");
            if(!$stmt) return false;
            if(!$stmt->execute(['id' => $id])) return false;

            $stmt = $pdo->prepare("DELETE FROM poste WHERE id = :id");
            if(!$stmt) return false;
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    function deleteService(int $id, PDO $pdo): bool {
        try {
            $serviceDetails = getServiceDetailsById($id, $pdo);

            if(!$serviceDetails) return false;

            if($serviceDetails['list_poste']) {
                $postes_list = json_decode($serviceDetails['list_poste']);
                foreach ($postes_list as $poste) {
                    if(!deletePoste($poste->id, $pdo)) return false;
                }
            }
            
            $stmt = $pdo->prepare("DELETE FROM service WHERE id = :id");
            if(!$stmt) return false;
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    function deleteDepartement(int $id, PDO $pdo): bool {
        try {
            $departementDetails = getDepartementDetailsById($id, $pdo);

            if(!$departementDetails) return false;

            if($departementDetails['list_service']) {
                $services_list = json_decode($departementDetails['list_service']);
                foreach ($services_list as $service) {
                    if(!deleteService($service->id, $pdo)) return false;
                }
            }

            $stmt = $pdo->prepare("DELETE FROM departement WHERE id = :id");
            if(!$stmt) return false;
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    function deleteEmploye(int $id, PDO $pdo): bool {
        try {
            $stmt = $pdo->prepare("DELETE FROM affectation WHERE id_employe = :id");
            if(!$stmt) return false;
            if(!$stmt->execute(['id' => $id])) return false;

            $stmt = $pdo->prepare("DELETE FROM conjoint WHERE id_employe = :id");
            if(!$stmt) return false;
            if(!$stmt->execute(['id' => $id])) return false;

            $stmt = $pdo->prepare("DELETE FROM enfant WHERE id_employe = :id");
            if(!$stmt) return false;
            if(!$stmt->execute(['id' => $id])) return false;

            $stmt = $pdo->prepare("DELETE FROM echellonnage_employe WHERE id_employe = :id");
            if(!$stmt) return false;
            if(!$stmt->execute(['id' => $id])) return false;

            $stmt = $pdo->prepare("DELETE FROM diplome WHERE id_employe = :id");
            if(!$stmt) return false;
            if(!$stmt->execute(['id' => $id])) return false;

            $stmt = $pdo->prepare("DELETE FROM employe WHERE id = :id");
            if(!$stmt) return false;
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    function deleteConjoint(int $id, PDO $pdo): bool {
        try {
            $stmt = $pdo->prepare("DELETE FROM conjoint WHERE id = :id");
            if(!$stmt) return false;
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    function deleteAffectation(int $id, PDO $pdo): bool {
        try {
            $stmt = $pdo->prepare("DELETE FROM affectation WHERE id = :id");
            if(!$stmt) return false;
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    function deleteEnfant(int $id, PDO $pdo): bool {
        try {
            $stmt = $pdo->prepare("DELETE FROM enfant WHERE id = :id");
            if(!$stmt) return false;
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    function deleteDiplome(int $id, PDO $pdo): bool {
        try {
            $stmt = $pdo->prepare("DELETE FROM diplome WHERE id = :id");
            if(!$stmt) return false;
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
?>