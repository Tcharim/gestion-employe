<?php
    session_start();

    include_once "includes/utils.php";

    if(!isConnected($pdo)) {
        header('Location: '.WORKSPACE.'/login.php');
        exit;
    };

    if(!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
        header('Location: '.WORKSPACE.'/employe.php');
        exit;
    }

    $id_employe = (int)$_GET['id'];

    $employe = getEmployeDetails($id_employe, $pdo);

    if(!$employe) {
        header('Location: '.WORKSPACE.'/employe.php');
        exit;
    }
    
    $activePanel = "emp";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <title>Attestation de travail</title>
    <link rel="icon" href="images/images.jpg" type="image/jpeg">

    <style>
        body{
            font-family: Arial, sans-serif;
            padding:60px;
        }

        .header{
            text-align:center;
            margin-bottom:60px;
        }

        h1{
            margin-bottom:40px;
        }

        .content{
            font-size:18px;
            line-height:1.8;
        }

        .signature{
            margin-top:80px;
            text-align:right;
        }

        @media print{
            .no-print{
                display:none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ATTESTATION DE TRAVAIL</h1>
    </div>

    <div class="content">
        Je soussigné, représentant de l'organisme,
        atteste que :
        <br><br>
        <?php if($employe['genre']=='f'): ?>
            <?php if($employe['civilite']=='c'): ?>
                Mlle.
            <?php else: ?>
                Mme.
            <?php endif ?>
        <?php else: ?>
            Mr.
        <?php endif ?>
        <strong><?= htmlspecialchars(strtoupper($employe['nom']).' '.ucfirst($employe['prenom'])) ?></strong>
        est employé<?= ($employe['genre']=='f')?'e':'' ?> au sein de notre établissement depuis le
        <strong><?= date('d/m/Y', strtotime($employe['date_recrutement'])) ?></strong>
        en qualité de:
        <strong><?= htmlspecialchars($employe['poste_nom']) ?>
        dans le service <?= htmlspecialchars($employe['service_nom']) ?>
        du departement <?= htmlspecialchars($employe['departement_nom']) ?></strong>.
        <br><br>
        La présente attestation est délivrée à l'intéressé(e)
        pour servir et valoir ce que de droit.
    </div>

    <div class="signature">
        Fait le <?= date('d/m/Y') ?>
        <br><br><br>
        Signature et cachet
    </div>

    <script>
        window.print();
  </script>

</body>
</html>