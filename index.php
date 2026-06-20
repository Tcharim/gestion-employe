<?php
    session_start();

    include_once "includes/utils.php";

    if(!isConnected($pdo)) {
        header('Location: '.WORKSPACE.'/login.php');
        exit;
    };

    $activePanel = "home";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>

    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/style.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/dashboard.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/themify-icons.css">
    <link rel="icon" href="images/images.jpg" type="image/jpeg">

    <style>
        .home-hero {
            background: linear-gradient(135deg, #0d2137 0%, #1a3d5c 60%, #1e4568 100%);
            border-radius: 16px;
            padding: 2.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .home-hero::after {
            content: '';
            position: absolute; bottom: -60px; right: -40px;
            width: 220px; height: 220px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.08);
            pointer-events: none;
        }
        .home-hero-content { position: relative; z-index: 1; max-width: 600px; }
        .home-hero h2 {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }
        .home-hero p {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
            line-height: 1.7;
            margin: 0;
        }

        .home-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
            gap: 16px;
        }

        .home-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .home-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.08); transform: translateY(-3px); }

        .home-card-icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            background: #fdecea;
            display: flex; align-items: center; justify-content: center;
        }
        .home-card-icon i { color: #C0392B; font-size: 20px; }

        .home-card-title { font-size: 15px; font-weight: 700; color: #1a1a2e; }
        .home-card-desc { font-size: 13px; color: #888; line-height: 1.6; flex-grow: 1; }
    </style>
</head>
<body>
    <?php include_once "includes/header.php" ?>
    <main class="dashboard-main">

        <?php include_once "includes/aside.php"; ?>
        <section>

            <div class="home-hero">
                <div class="home-hero-content">
                    <h2>Dashboard de gestion de Dylia Market</h2>
                    <p>
                        Bienvenue sur le dashboard de gestion de Dylia Market.
                        Ici vous pouvez gérer les employés, en ajouter, les lister ou les retirer,
                        effectuer des décisions ou les révoquer, et même ajouter, lister et retirer des formations.
                    </p>
                </div>
            </div>

            <div class="home-grid">
                <div class="home-card">
                    <div class="home-card-icon"><i class="ti ti-id-badge"></i></div>
                    <div class="home-card-title">Gestion des employés</div>
                    <div class="home-card-desc">Ajoutez, modifiez ou supprimez les employés de votre organisation en quelques clics.</div>
                </div>

                <div class="home-card">
                    <div class="home-card-icon"><i class="ti ti-layout"></i></div>
                    <div class="home-card-title">Organisation</div>
                    <div class="home-card-desc">Gérez vos départements, services et postes pour structurer votre entreprise.</div>
                </div>

        </section>
    </main>
</body>
</html>