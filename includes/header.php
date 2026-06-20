<?php
    include_once "includes/utils.php";

    if(!isset($active))
        $active = "";

    if(!isset($isConnected))
        $isConnected = isConnected($pdo);
        
?>

<header class="site-header">
    <div class="logo">
        <a href="<?= WORKSPACE ?>/index.php"><img src="<?= WORKSPACE ?>/images/images.jpg" alt="DM" style="height:60px;"></a>
    </div>
    <nav>
        <ul>
            <?php if($isConnected): ?>            
                <li><a class="active" href="<?= WORKSPACE ?>/logout.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a class="active" href="<?= WORKSPACE ?>/login.php">Connexion</a></li>
            <?php endif ?>
            
        </ul>
    </nav>
</header>