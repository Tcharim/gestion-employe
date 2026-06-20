<?php
    session_start();

    include_once "../includes/utils.php";

    if(!isConnected($pdo)) {
        http_response_code(403);
        echo json_encode([
            "status" => 403,
            "data" => "Veuillez vous connecter"
        ]);

        exit;
    }

    $result = null;
    $input = json_decode(file_get_contents('php://input'));

    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        if(!isset($_GET) || !isset($_GET['id_dep']) || !ctype_digit($_GET['id_dep'])) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "data" => "Requête mal formulée"
            ]);

            exit;
        }

        $result = getServicesByIdDep((int)$_GET['id_dep'], $pdo);

        if(!$result) exit;
    }
    else if($_SERVER['REQUEST_METHOD'] === 'POST')  {
        if(!isset($input) || !isset($input->nom) || !isset($input->id_departement) || !ctype_digit($input->id_departement)) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "data" => "Requête mal formulée"
            ]);

            exit;
        }
        
        $result = insertService((int)$input->id_departement, strtolower(htmlspecialchars($input->nom)), $pdo);
    }
    else if($_SERVER['REQUEST_METHOD'] === 'PUT')  {
        if(!isset($input) || !isset($input->id) || !ctype_digit($input->id) || !isset($input->nom)) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "data" => "Requête mal formulée"
            ]);

            exit;
        }
        
        $result = updateService((int)$input->id, strtolower(htmlspecialchars($input->nom)), $pdo);
    }
    else if($_SERVER['REQUEST_METHOD'] === 'DELETE')  {
        if(!isset($input) || !isset($input->id) || !ctype_digit($input->id)) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "data" => "Requête mal formulée"
            ]);

            exit;
        }
        
        $result = deleteService((int)$input->id, $pdo);
    }
    else {
        http_response_code(401);
        echo json_encode([
            "status" => 401,
            "data" => "Requête mal formulée"
        ]);

        exit;
    }

    if(!$result) {
        http_response_code(500);
        echo json_encode([
            "status" => 500,
            "data" => "Une erreur interne est suvenue"
        ]);

        exit;
    }

    echo json_encode([
        "status" => 200,
        "data" => $result
    ]);
?>