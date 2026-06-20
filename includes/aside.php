<?php
    if(!isset($activePanel)) $activePanel = '';
?>

<aside>
    <a class="aside-section-label aside-link <?= ($activePanel === 'home')?'active':''?>" href="index.php">
        Home
    </a>
    <a class="aside-section-label aside-link <?= ($activePanel === 'org')?'active':''?>" href="organisation.php">
        Organisation
    </a>
    <a class="aside-section-label aside-link <?= ($activePanel === 'emp')?'active':''?>" href="employe.php">
        Employés
    </a>
</aside>