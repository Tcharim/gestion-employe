<?php
    session_start();
    include_once "includes/utils.php";

    $error = isset($_GET['error']) && ctype_digit($_GET['error']) ? (int)$_GET['error'] : 500;

    $messages = [
        400 => ['title' => 'Requête invalide', 'desc' => 'La requête envoyée est incorrecte ou mal formée.'],
        401 => ['title' => 'Non autorisé', 'desc' => 'Vous devez être connecté pour accéder à cette page.'],
        403 => ['title' => 'Accès refusé', 'desc' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource.'],
        404 => ['title' => 'Page introuvable', 'desc' => 'La page que vous recherchez n\'existe pas ou a été déplacée.'],
        405 => ['title' => 'Méthode non autorisée', 'desc' => 'Cette méthode n\'est pas autorisée pour cette ressource.'],
        500 => ['title' => 'Erreur serveur', 'desc' => 'Une erreur interne est survenue. Veuillez réessayer plus tard.'],
        502 => ['title' => 'Erreur de passerelle', 'desc' => 'Le serveur a reçu une réponse invalide.'],
        503 => ['title' => 'Service indisponible', 'desc' => 'Le service est temporairement indisponible.'],
    ];

    $info = $messages[$error] ?? $messages[500];

    $active = '';
    $isConnected = isConnected($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dylia Market Solutions Digitales - Erreur <?= $error ?></title>

    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/style.css">
    <link rel="icon" href="images/images.jpg" type="image/jpeg">

    <style>
        .error-content {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 16px;
            border: 1px solid #ececec;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 40px 36px;
            text-align: center;
        }

        .error-code {
            font-size: 56px;
            font-weight: 800;
            color: #c9302c;
            line-height: 1;
            margin-bottom: 8px;
        }

        .error-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0 0 10px;
        }

        .error-desc {
            font-size: 14px;
            color: #888;
            line-height: 1.6;
            margin: 0 0 28px;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .error-btn {
            padding: 11px 22px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }

        .error-btn-primary {
            background: #c9302c;
            color: #fff;
        }

        .error-btn-primary:hover { background: #b32a26; }

        .error-btn-secondary {
            background: #f4f5f7;
            color: #444;
            border: 1px solid #d9d9d9;
        }

        .error-btn-secondary:hover { background: #ececec; }

        @media (max-width: 500px) {
            .error-content { padding: 32px 24px; }
            .error-code { font-size: 46px; }
        }
    </style>
</head>
<body>
    <?php include_once "includes/header.php"; ?>
    <main class="site-main">
        <section class="main-content error-content">
            <div class="error-code"><?= $error ?></div>
            <h2 class="error-title"><?= htmlspecialchars($info['title']) ?></h2>
            <p class="error-desc"><?= htmlspecialchars($info['desc']) ?></p>
            <div class="error-actions">
                <a class="error-btn error-btn-secondary" href="javascript:history.back()">Retour</a>
                <a class="error-btn error-btn-primary" href="<?= $isConnected ? 'index.php' : 'login.php' ?>">
                    <?= $isConnected ? 'Tableau de bord' : 'Connexion' ?>
                </a>
            </div>
        </section>
    </main>
</body>
</html>