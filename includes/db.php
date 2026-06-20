<?php
    require_once "utils.php";

    $env = parse_ini_file(ROOTPATH."/../.env", true);

    if(!isset($env)) 
        sendError(500);

    if(!isset($env['DB_HOST']) || !isset($env['DB_NAME']) || !isset($env['DB_USER']) || !isset($env['DB_PASSWORD'])) 
        sendError(500);

    $host = $env['DB_HOST'];
    $db = $env['DB_NAME'];
    $user = $env['DB_USER'];
    $password = $env['DB_PASSWORD'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    } catch (PDOException $e) {
        sendError(500);
        exit();
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>