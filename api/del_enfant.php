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

    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(401);
        echo json_encode([
            "status" => 401,
            "data" => "Requête mal formulée"
        ]);

        exit;
    }


    $post = json_decode(file_get_contents('php://input'));

    if(!isset($post) || !isset($post->id) || !ctype_digit($post->id)) {
        http_response_code(401);
        echo json_encode([
            "status" => 401,
            "data" => "Requête mal formulée"
        ]);

        exit;
    }
    
    $result = deleteEnfant((int)$post->id, $pdo);

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