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
        if(!isset($_GET) || !isset($_GET['id_serv']) || !ctype_digit($_GET['id_serv'])) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "data" => "Requête mal formulée"
            ]);

            exit;
        }

        $result = getPosteByIdServ((int)$_GET['id_serv'], $pdo);

        if(!$result) exit;
    }
    else if($_SERVER['REQUEST_METHOD'] === 'POST')  {
        if(!isset($input) || !isset($input->nom) || !isset($input->id_service) || !ctype_digit($input->id_service)) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "data" => "Requête mal formulée"
            ]);

            exit;
        }
        
        $result = insertPoste((int)$input->id_service, strtolower(htmlspecialchars($input->nom)), $pdo);
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
        
        $result = updatePoste((int)$input->id, strtolower(htmlspecialchars($input->nom)), $pdo);
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
        
        $result = deletePoste((int)$input->id, $pdo);
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