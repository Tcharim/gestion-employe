<?php
    session_start();
    include_once 'includes/utils.php';

    if(isConnected($pdo)) {
        header('Location: '.WORKSPACE.'/index.php');
        exit;
    };

    $isConnectionFailed = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = htmlspecialchars($_POST['username']);
        $password = $_POST['password'];

        $user = getCompteByUsername($username, $pdo);
        if(!is_null($user)) {
            if (hash('sha256', $password) === $user['password_hash']) {
                $_SESSION['username'] = $user['username'];

                header('Location: '.WORKSPACE.'/index.php');
                exit;
            }
            else {
                $isConnectionFailed = true;                    
            }

        } else {
            $isConnectionFailed = true;
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dylia Market Solutions Digitales - Connexion</title>

    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/style.css">

    <link rel="icon" href="images/images.jpg" type="image/jpeg">

    <style>
        .site-main {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .main-content {
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            padding: 0;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            border: 1px solid #ececec;
            text-align: left;
            overflow: hidden;
        }

        .login-header {
            padding: 32px 36px 0;
        }

        .login-header .login-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: #c9302c;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .login-header .login-tag::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #c9302c;
            display: inline-block;
        }

        .main-content h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0 0 4px;
        }

        .login-sub {
            font-size: 13px;
            color: #888;
            margin: 0 0 24px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
            padding: 0 36px 32px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .form-group label {
            margin-bottom: 7px;
            font-size: 12px;
            font-weight: 600;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #d9d9d9;
            border-radius: 9px;
            font-size: 14px;
            background: #fafafa;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            box-sizing: border-box;
            outline: none;
        }

        .form-group input::placeholder { color: #b3b3b3; }

        .form-group input:focus {
            border-color: #c9302c;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(201, 48, 44, 0.1);
        }

        button {
            margin-top: 6px;
            padding: 12px;
            border: none;
            border-radius: 9px;
            background: #c9302c;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover { background: #b32a26; }
        button:active { transform: scale(0.99); }

        .error {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fdecea;
            color: #c9302c;
            padding: 11px 14px;
            border-radius: 9px;
            margin: 24px 36px 0;
            font-size: 13px;
            font-weight: 500;
        }

        @media (max-width: 500px) {
            .login-header { padding: 26px 24px 0; }
            .login-form { padding: 0 24px 26px; }
            .error { margin: 20px 24px 0; }
        }
    </style>
</head>
<body>
    <?php include_once "includes/header.php"; ?>
    <main class="site-main">
        <section class="main-content">
            <div class="login-header">
                <span class="login-tag">Espace employé</span>
                <h2>Connexion</h2>
                <p class="login-sub">Entrez vos identifiants pour accéder à votre espace.</p>
            </div>

            <?php if($isConnectionFailed):?>
                <div class="error">Identifiant ou mot de passe incorrect.</div>
            <?php endif ?>

            <form action="" method="post" class="login-form">
                <div class="form-group">
                    <label for="username">Identifiant</label>
                    <input type="text" id="username" name="username" placeholder="Votre identifiant" required>
                </div>
                <div class="form-group password-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                </div>
                <button type="submit">Se connecter</button>
            </form>
        </section>
    </main>
</body>
</html>