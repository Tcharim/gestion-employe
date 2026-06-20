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

    $employe = getEmployeDetails($id_employe,$pdo);

    if(!$employe) {
        header('Location: '.WORKSPACE.'/employe.php');
        exit;
    }

    $list_departements = getAllDepartementDetails($pdo);
    $list_categories = getAllCategorie($pdo);
    $list_echellons = getAllEchellon($pdo);

    $activePanel = "emp";

    // ── Helpers d'affichage ──
    function fmtDate($d) {
        if (!$d) return '—';
        $t = strtotime($d);
        return $t ? date('d/m/Y', $t) : '—';
    }
    function fmtBool($b) {
        return $b ? 'Oui' : 'Non';
    }
    function fmtGenre($g) {
        return $g === 'm' ? 'Homme' : 'Femme';
    }
    function fmtCivilite($c,$genre) {
        return match($c) {
            'c' => 'Célibataire',
            'm' => ($genre === 'm')?'Marié':'Mariée',
            'd' => ($genre === 'm')?'Divorcé':'Divorcée',
            'v' => ($genre === 'm')?'Veuf':'Veuve',
            default => '—',
        };
    }
    function fmtEtatSante($e) {
        return $e === 's' ? 'Sain' : 'Maladie chronique';
    }
    function fmtVal($v) {
        return ($v === null || $v === '') ? '—' : htmlspecialchars(ucfirst($v));
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails employé</title>

    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/style.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/dashboard.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/details_employe.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/themify-icons.css">
    
    <link rel="icon" href="images/images.jpg" type="image/jpeg">

    <style>
        .modal-overlay .form, form#form-edit-employe, form#form-conjoint, form#form-enfant, form#form-diplome, form#form-affectation {
            display: flex;
            flex-direction: column;
            min-height: 0;
            flex: 1 1 auto;
            overflow: hidden;
        }
        .modal-body-scroll {
            overflow-y: auto;
            padding-right: 12px;
            margin-right: -12px;
            min-height: 0;
            flex: 1 1 auto;
            position: relative;
        }
        .modal-body-scroll::-webkit-scrollbar { width: 9px; }
        .modal-body-scroll::-webkit-scrollbar-thumb { background: #c6c9ce; border-radius: 10px; border: 2px solid #fff; }
        .modal-body-scroll::-webkit-scrollbar-thumb:hover { background: #a6a9ae; }
        .modal-body-scroll::-webkit-scrollbar-track { background: #f4f5f7; border-radius: 10px; }
        .modal-body-scroll { scrollbar-width: thin; scrollbar-color: #c6c9ce #f4f5f7; }

        .modal-box { display: flex; flex-direction: column; overflow: hidden; }

        .scroll-cue {
            position: sticky;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 34px;
            margin-top: -34px;
            pointer-events: none;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,0.96) 75%);
            opacity: 1;
            transition: opacity 0.2s ease;
        }
        .scroll-cue.hide { opacity: 0; }
        .scroll-cue i { font-size: 18px; color: #C0392B; margin-bottom: 4px; animation: scroll-bounce 1.4s ease-in-out infinite; }
        @keyframes scroll-bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(4px); } }

        .modal-actions { flex-shrink: 0; }
    </style>

</head>
<body>
    <?php include_once "includes/header.php" ?>
    <main class="dashboard-main">

        <?php include_once "includes/aside.php"; ?>
        <section>

            <a href="employe.php" class="detail-back-link">
                <i class="ti ti-arrow-left"></i> Retour à la liste
            </a>

            <div class="detail-page-header">
                <div class="detail-identity">
                    <div class="detail-avatar">
                        <?= strtoupper(substr($employe['nom'], 0, 1) . substr($employe['prenom'], 0, 1)) ?>
                    </div>
                    <div>
                        <h1 class="detail-name"><?= htmlspecialchars(strtoupper($employe['nom']) . ' ' . ucfirst($employe['prenom'])) ?></h1>
                        <div class="detail-subtitle">
                            <?php if($employe['poste_nom'] && $employe['service_nom'] && $employe['departement_nom']): ?>
                                <?= 
                                    htmlspecialchars(ucfirst($employe['poste_nom']))
                                    .'— '. htmlspecialchars(ucfirst($employe['service_nom']))
                                    .'—'.htmlspecialchars(ucfirst($employe['departement_nom']))
                                ?>
                            <?php else: ?>
                                Aucun poste
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="detail-actions">
                    <button class="btn-action btn-modifier" onclick="openModal('modal-edit-employe')">
                        <i class="ti ti-pencil"></i> Modifier
                    </button>
                    <button class="btn-action btn-imprimer" onclick="printAttestation(<?= $employe['id'] ?>)">
                        <i class="ti ti-printer"></i> Attestation de travail
                    </button>
                </div>
            </div>

            <div class="badge-row">
                <span class="badge">Catégorie <?= htmlspecialchars($employe['categorie_nom'] ?? '—') ?></span>
                <span class="badge">Échelon <?= htmlspecialchars($employe['echellon_num'] ?? '—') ?></span>
                <span class="badge">Indice <?= htmlspecialchars($employe['indice']) ?></span>
                <span class="badge">Obtenu le <?= fmtDate($employe['date_obtention_echellonnement']) ?></span>
            </div>

            <section class="categorie-section detail-grid">
                <!-- Identité -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-icon"><i class="ti ti-id-badge"></i></div>
                        <span class="detail-card-title">Identité</span>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row"><span class="label">Nom</span><span class="value"><?= fmtVal(strtoupper($employe['nom'])) ?></span></div>
                        <div class="detail-row"><span class="label">Prénom</span><span class="value"><?= fmtVal($employe['prenom']) ?></span></div>
                        <div class="detail-row"><span class="label">Genre</span><span class="value"><?= fmtGenre($employe['genre']) ?></span></div>
                        <div class="detail-row"><span class="label">Situation familiale</span><span class="value"><?= fmtCivilite($employe['civilite'], $employe['genre']) ?></span></div>
                        <div class="detail-row"><span class="label">Date de naissance</span><span class="value"><?= fmtDate($employe['date_naissance']) ?></span></div>
                        <div class="detail-row"><span class="label">Lieu de naissance</span><span class="value"><?= fmtVal($employe['lieu_naissance']) ?></span></div>
                        <div class="detail-row"><span class="label">Nationalité</span><span class="value"><?= fmtVal($employe['nationalite']) ?></span></div>
                        <div class="detail-row"><span class="label">État de santé</span><span class="value"><?= fmtEtatSante($employe['etat_sante']) ?></span></div>
                    </div>
                </div>

                <!-- Coordonnées & administratif -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-icon"><i class="ti ti-map-alt"></i></div>
                        <span class="detail-card-title">Coordonnées & administratif</span>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row"><span class="label">Adresse</span><span class="value"><?= fmtVal($employe['adresse']) ?></span></div>
                        <div class="detail-row"><span class="label">Téléphone</span><span class="value"><?= fmtVal($employe['num_tel']) ?></span></div>
                        <div class="detail-row"><span class="label">N° Assurance</span><span class="value"><?= fmtVal($employe['num_assurance']) ?></span></div>
                        <div class="detail-row"><span class="label">NIN</span><span class="value"><?= fmtVal($employe['nin']) ?></span></div>
                        <div class="detail-row"><span class="label">RIB</span><span class="value"><?= fmtVal($employe['num_rib']) ?></span></div>
                        <div class="detail-row"><span class="label">Service national</span><span class="value"><?= fmtBool($employe['service_national']) ?></span></div>
                        <div class="detail-row"><span class="label">Date de recrutement</span><span class="value"><?= fmtDate($employe['date_recrutement']) ?></span></div>
                    </div>
                </div>

                <!-- Affectation -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="detail-card-icon"><i class="ti ti-briefcase"></i></div>
                            <span class="detail-card-title">Affectation</span>
                        </div>
                        <div style="display:flex; gap:6px;">
                            <?php if (!empty($employe['poste_nom'])): ?>
                                <button class="btn-card-add" onclick="openEditAffectationModal()">
                                    <i class="ti ti-pencil"></i> Modifier
                                </button>
                                <button class="btn-card-add" onclick="confirmDeleteAffectation(<?= (int)$employe['id'] ?>)">
                                    <i class="ti ti-trash"></i> Supprimer
                                </button>
                            <?php else: ?>
                                <button class="btn-card-add" onclick="openAddModal('modal-add-affectation', 'form-affectation', 'modal-affectation-title', 'Ajouter une affectation')">
                                    <i class="ti ti-plus"></i> Ajouter
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-row"><span class="label">Département</span><span class="value"><?= fmtVal($employe['departement_nom'] ?? null) ?></span></div>
                        <div class="detail-row"><span class="label">Service</span><span class="value"><?= fmtVal($employe['service_nom'] ?? null) ?></span></div>
                        <div class="detail-row"><span class="label">Poste</span><span class="value"><?= fmtVal($employe['poste_nom'] ?? null) ?></span></div>
                        <div class="detail-row"><span class="label">Affecté le</span><span class="value"><?= fmtDate($employe['date_affectation']) ?></span></div>
                    </div>
                </div>

                <!-- Diplômes -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="detail-card-icon"><i class="ti ti-book"></i></div>
                            <span class="detail-card-title">Diplômes</span>
                        </div>
                        <button class="btn-card-add" onclick="openAddModal('modal-add-diplome', 'form-diplome', 'modal-diplome-title', 'Ajouter un diplôme')">
                            <i class="ti ti-plus"></i> Ajouter
                        </button>
                    </div>
                    <div class="detail-card-body">
                        <?php if (!empty($employe['diplome_list'])): ?>
                            <?php foreach (json_decode($employe['diplome_list']) as $diplome): ?>
                                <div class="detail-subitem">
                                    <div class="detail-subitem-actions">
                                        <button class="btn-subitem-action action-edit" onclick='openEditDiplomeModal(<?= json_encode($diplome) ?>)' title="Modifier">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        <button class="btn-subitem-action action-delete" onclick="confirmDeleteItem('diplome', <?= (int)$diplome->id ?>)" title="Supprimer">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                    <div class="detail-subitem-title"><?= htmlspecialchars(ucfirst($diplome->nom)) ?></div>
                                    <div class="detail-subitem-sub">Niveau Bac<?= htmlspecialchars((($diplome->niveau>=0)?'+':'-').$diplome->niveau) ?> — Obtenu en <?= fmtVal($diplome->annee_obtention) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="detail-empty">Aucun diplôme enregistré</div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($employe['civilite'] !== 'c'): ?>
                    <!-- Enfants -->
                    <div class="detail-card">
                        <div class="detail-card-header">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div class="detail-card-icon"><i class="ti ti-home"></i></div>
                                <span class="detail-card-title">Enfants</span>
                            </div>
                            <button class="btn-card-add" onclick="openAddModal('modal-add-enfant', 'form-enfant', 'modal-enfant-title', 'Ajouter un enfant')">
                                <i class="ti ti-plus"></i> Ajouter
                            </button>
                        </div>
                        <div class="detail-card-body">
                            <?php if (!empty($employe['enfant_list'])): ?>
                                <?php foreach (json_decode($employe['enfant_list']) as $enfant): ?>
                                    <div class="detail-subitem">
                                        <div class="detail-subitem-actions">
                                            <button class="btn-subitem-action action-edit" onclick='openEditEnfantModal(<?= json_encode($enfant) ?>)' title="Modifier">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <button class="btn-subitem-action action-delete" onclick="confirmDeleteItem('enfant', <?= (int)$enfant->id ?>)" title="Supprimer">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <div class="detail-subitem-title"><?= htmlspecialchars(ucfirst($enfant->prenom)) ?></div>
                                        <div class="detail-subitem-sub">Né(e) le <?= fmtDate($enfant->date_naissance) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="detail-empty">Aucun enfant enregistré</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif ?>

                <?php if($employe['civilite'] === 'm'): ?>
                    <!-- Conjoint -->
                    <div class="detail-card">
                        <div class="detail-card-header">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div class="detail-card-icon"><i class="ti ti-heart"></i></div>
                                <span class="detail-card-title">Conjoint</span>
                            </div>
                            <button class="btn-card-add" onclick="openAddModal('modal-add-conjoint', 'form-conjoint', 'modal-conjoint-title', 'Ajouter un conjoint')">
                                <i class="ti ti-plus"></i> Ajouter
                            </button>
                        </div>
                        <div class="detail-card-body">
                            <?php if (!empty($employe['conjoint_list'])):?>
                                <?php foreach (json_decode($employe['conjoint_list']) as $c): ?>
                                    <div class="detail-subitem">
                                        <div class="detail-subitem-actions">
                                            <button class="btn-subitem-action action-edit" onclick='openEditConjointModal(<?= json_encode($c) ?>)' title="Modifier">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <button class="btn-subitem-action action-delete" onclick="confirmDeleteItem('conjoint', <?= (int)$c->id ?>)" title="Supprimer">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <div class="detail-subitem-title"><?= fmtVal(strtoupper($c->nom)).' '.fmtVal(ucfirst($c->prenom)) ?></div>
                                        <div class="detail-subitem-sub">Date de naiddance: <?= fmtDate($c->date_naissance) ?></div>
                                        <div class="detail-subitem-sub">Lieu de naissance: <?= fmtVal($c->lieu_naissance) ?></div>
                                        <div class="detail-subitem-sub">Nationalité: <?= fmtVal($c->nationalite) ?></div>
                                        <div class="detail-subitem-sub">Profession: <?= fmtVal($c->profession) ?></div>
                                        <div class="detail-subitem-sub">Organisme: <?= fmtVal($c->organisme ?? null) ?></div>
                                        <div class="detail-subitem-sub">Addresse de l'organisme: <?= fmtVal($c->adresse_organisme ?? null) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="detail-empty">Aucun conjoint enregistré</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif ?>

            </section>
        </section>
    </main>

    <div class="modal-overlay" id="modal-edit-employe">
        <div class="modal-box modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Modifier l'employé</h3>
                <button class="modal-close" onclick="closeModal('modal-edit-employe')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <form id="form-edit-employe">
                <input type="hidden" name="id" value="<?= (int)$employe['id'] ?>">

                <div class="modal-body-scroll">

                    <h3>Informations personnelles</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Nom</label>
                            <input type="text" name="nom" value="<?= htmlspecialchars($employe['nom']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Prénom</label>
                            <input type="text" name="prenom" value="<?= htmlspecialchars($employe['prenom']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Date de naissance</label>
                            <input type="date" name="date_naissance" value="<?= htmlspecialchars($employe['date_naissance']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Lieu de naissance</label>
                            <input type="text" name="lieu_naissance" value="<?= htmlspecialchars($employe['lieu_naissance']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Nationalité</label>
                            <input type="text" name="nationalite" value="<?= htmlspecialchars($employe['nationalite']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Genre</label>
                            <select name="genre" required>
                                <option value="m" <?= $employe['genre'] === 'm' ? 'selected' : '' ?>>Homme</option>
                                <option value="f" <?= $employe['genre'] === 'f' ? 'selected' : '' ?>>Femme</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Situation familiale</label>
                            <select name="civilite">
                                <option value="c" <?= $employe['civilite'] === 'c' ? 'selected' : '' ?>>Célibataire</option>
                                <option value="m" <?= $employe['civilite'] === 'm' ? 'selected' : '' ?>>Marié(e)</option>
                                <option value="d" <?= $employe['civilite'] === 'd' ? 'selected' : '' ?>>Divorcé(e)</option>
                                <option value="v" <?= $employe['civilite'] === 'v' ? 'selected' : '' ?>>Veuf / Veuve</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>État de santé</label>
                            <select name="etat_sante">
                                <option value="s" <?= $employe['etat_sante'] === 's' ? 'selected' : '' ?>>Sain</option>
                                <option value="m" <?= $employe['etat_sante'] === 'm' ? 'selected' : '' ?>>Maladie chronique</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="service_national" value="1" <?= $employe['service_national'] ? 'checked' : '' ?>>
                            Service national effectué
                        </label>
                    </div>

                    <hr>

                    <h3>Informations administratives</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Téléphone</label>
                            <input type="text" maxlength="10" name="num_tel" value="<?= htmlspecialchars($employe['num_tel']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>N° Assurance</label>
                            <input type="text" maxlength="13" name="num_assurance" value="<?= htmlspecialchars($employe['num_assurance']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>NIN</label>
                            <input type="text" maxlength="18" name="nin" value="<?= htmlspecialchars($employe['nin']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>RIB</label>
                            <input type="text" maxlength="15" name="num_rib" value="<?= htmlspecialchars($employe['num_rib']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Adresse</label>
                            <input type="text" name="adresse" value="<?= htmlspecialchars($employe['adresse']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Date de recrutement</label>
                            <input type="date" name="date_recrutement" value="<?= htmlspecialchars($employe['date_recrutement']) ?>" required>
                        </div>
                    </div>

                    <hr>

                    <h3>Echellonnement</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Catégorie</label>
                            <select name="id_categorie">
                                <?php foreach($list_categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($employe['id_categorie'] ?? null) == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nom']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Échelon</label>
                            <select name="id_echellon">
                                <?php foreach($list_echellons as $e): ?>
                                    <option value="<?= $e['num'] ?>" <?= ($employe['echellon_num'] ?? null) == $e['num'] ? 'selected' : '' ?>>
                                        <?= $e['num'] ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group required">
                        <label>Date d'obtention de l'echellonement</label>
                        <input type="date" name="date_obtention_echellonnement" value="<?= htmlspecialchars($employe['date_obtention_echellonnement']) ?>">
                    </div>

                    <hr>
                    
                    <div class="scroll-cue" id="scroll-cue-edit-employe">
                        <i class="ti ti-chevron-down"></i>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-edit-employe')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal: Ajouter / Modifier Conjoint ── -->
    <div class="modal-overlay" id="modal-add-conjoint">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-conjoint-title">Ajouter un conjoint</h3>
                <button class="modal-close" onclick="closeModal('modal-add-conjoint')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <form id="form-conjoint">
                <input type="hidden" name="id" id="conjoint-id" value="">
                <input type="hidden" name="id_employe" value="<?= (int)$employe['id'] ?>">
                <div class="modal-body-scroll">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nom</label>
                            <input type="text" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label>Prénom</label>
                            <input type="text" name="prenom" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date de naissance</label>
                            <input type="date" name="date_naissance" required>
                        </div>
                        <div class="form-group">
                            <label>Lieu de naissance</label>
                            <input type="text" name="lieu_naissance" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nationalité</label>
                            <input type="text" name="nationalite" required>
                        </div>
                        <div class="form-group">
                            <label>Profession</label>
                            <input type="text" name="profession" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Organisme</label>
                            <input type="text" name="organisme">
                        </div>
                        <div class="form-group">
                            <label>Adresse de l'organisme</label>
                            <input type="text" name="adresse_organisme">
                        </div>
                    </div>

                    <div class="scroll-cue" id="scroll-cue-conjoint">
                        <i class="ti ti-chevron-down"></i>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-add-conjoint')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal: Ajouter / Modifier Enfant ── -->
    <div class="modal-overlay" id="modal-add-enfant">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-enfant-title">Ajouter un enfant</h3>
                <button class="modal-close" onclick="closeModal('modal-add-enfant')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <form id="form-enfant">
                <input type="hidden" name="id" id="enfant-id" value="">
                <input type="hidden" name="id_employe" value="<?= (int)$employe['id'] ?>">
                <div class="modal-body-scroll">
                    <div class="form-group" style="margin-bottom: 14px;">
                        <label>Prénom</label>
                        <input type="text" name="prenom" required>
                    </div>
                    <div class="form-group">
                        <label>Date de naissance</label>
                        <input type="date" name="date_naissance" required>
                    </div>

                    <div class="scroll-cue" id="scroll-cue-enfant">
                        <i class="ti ti-chevron-down"></i>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-add-enfant')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal: Ajouter / Modifier Diplôme ── -->
    <div class="modal-overlay" id="modal-add-diplome">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-diplome-title">Ajouter un diplôme</h3>
                <button class="modal-close" onclick="closeModal('modal-add-diplome')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <form id="form-diplome">
                <input type="hidden" name="id" id="diplome-id" value="">
                <input type="hidden" name="id_employe" value="<?= (int)$employe['id'] ?>">
                <div class="modal-body-scroll">
                    <div class="form-group" style="margin-bottom: 14px;">
                        <label>Nom du diplôme</label>
                        <input type="text" name="nom" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Niveau (ex: 3 pour Bac+3)</label>
                            <input type="number" name="niveau" required>
                        </div>
                        <div class="form-group">
                            <label>Année d'obtention</label>
                            <input type="number" name="annee_obtention" min="1950" max="2100" required>
                        </div>
                    </div>

                    <div class="scroll-cue" id="scroll-cue-diplome">
                        <i class="ti ti-chevron-down"></i>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-add-diplome')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal: Ajouter / Modifier Affectation ── -->
    <div class="modal-overlay" id="modal-add-affectation">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-affectation-title">Ajouter une affectation</h3>
                <button class="modal-close" onclick="closeModal('modal-add-affectation')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <form id="form-affectation">
                <input type="hidden" name="id_employe" value="<?= (int)$employe['id'] ?>">
                <div class="modal-body-scroll">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Département</label>
                            <select id="select-dep-aff" name="id_departement" required>
                                <option value="">— Choisir —</option>
                                <?php foreach($list_departements as $dep): ?>
                                    <option value="<?= $dep['id'] ?>"><?= htmlspecialchars($dep['nom']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Service</label>
                            <select id="select-service-aff" name="id_service" required disabled>
                                <option value="">— Choisir un département d'abord —</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Poste</label>
                            <select id="select-poste-aff" name="id_poste" required disabled>
                                <option value="">— Choisir un service d'abord —</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date d'affectation</label>
                            <input type="date" name="date_affection" required>
                        </div>
                    </div>

                    <div class="scroll-cue" id="scroll-cue-affectation">
                        <i class="ti ti-chevron-down"></i>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-add-affectation')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal: Confirmation de suppression (générique) ── -->
    <div class="modal-overlay" id="modal-confirm-delete">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Confirmer la suppression</h3>
                <button class="modal-close" onclick="closeModal('modal-confirm-delete')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <p class="confirm-text" id="confirm-delete-text">Êtes-vous sûr de vouloir supprimer cet élément ?</p>
            <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-confirm-delete')">Annuler</button>
                <button type="button" class="modal-btn modal-btn-submit" id="btn-confirm-delete">Supprimer</button>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <script src="javascripts/script.js"></script>
    <script>
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            const scrollArea = overlay.querySelector('.modal-body-scroll');
            const cue = overlay.querySelector('.scroll-cue');
            if (!scrollArea || !cue) return;

            const updateCue = () => {
                const atBottom = scrollArea.scrollTop + scrollArea.clientHeight >= scrollArea.scrollHeight - 4;
                const hasOverflow = scrollArea.scrollHeight > scrollArea.clientHeight + 4;
                cue.classList.toggle('hide', atBottom || !hasOverflow);
            };

            scrollArea.addEventListener('scroll', updateCue);

            new MutationObserver(() => {
                if (overlay.classList.contains('show')) {
                    requestAnimationFrame(() => requestAnimationFrame(updateCue));
                }
            }).observe(overlay, { attributes: true, attributeFilter: ['class'] });
        });
    </script>
    <script>
        /* ── Suppression générique conjoint / enfant / diplôme ── */
        const deleteEndpoints = {
            conjoint: 'api/conjoint.php',
            enfant: 'api/enfant.php',
            diplome: 'api/diplome.php',
        };
        const deleteLabels = {
            conjoint: 'ce conjoint',
            enfant: 'cet enfant',
            diplome: 'ce diplôme',
        };
        
        const selectDepAff = document.getElementById('select-dep-aff');
        const selectServiceAff = document.getElementById('select-service-aff');
        const selectPosteAff = document.getElementById('select-poste-aff');

        if (selectDepAff) {
            selectDepAff.addEventListener('change', async function() {
                fillSelect(selectPosteAff, [], '— Choisir un service d\'abord —');
                selectPosteAff.disabled = true;

                if (!this.value) {
                    fillSelect(selectServiceAff, [], '— Choisir un département d\'abord —');
                    selectServiceAff.disabled = true;
                    return;
                }

                selectServiceAff.disabled = true;
                fillSelect(selectServiceAff, [], 'Chargement...');

                try {
                    const { status, data } = await getJson('api/service.php?id_dep=' + encodeURIComponent(this.value));
                    if (status >= 200 && status < 300) {
                        fillSelect(selectServiceAff, data, '— Choisir un service —');
                        selectServiceAff.disabled = false;
                    } else {
                        fillSelect(selectServiceAff, [], 'Erreur de chargement');
                    }
                } catch (err) {
                    fillSelect(selectServiceAff, [], 'Erreur réseau');
                }
            });
        }

        if (selectServiceAff) {
            selectServiceAff.addEventListener('change', async function() {
                if (!this.value) {
                    fillSelect(selectPosteAff, [], '— Choisir un service d\'abord —');
                    selectPosteAff.disabled = true;
                    return;
                }

                selectPosteAff.disabled = true;
                fillSelect(selectPosteAff, [], 'Chargement...');

                try {
                    const { status, data } = await getJson('api/poste.php?id_serv=' + encodeURIComponent(this.value));
                    if (status >= 200 && status < 300) {
                        fillSelect(selectPosteAff, data, '— Choisir un poste —');
                        selectPosteAff.disabled = false;
                    } else {
                        fillSelect(selectPosteAff, [], 'Erreur de chargement');
                    }
                } catch (err) {
                    fillSelect(selectPosteAff, [], 'Erreur réseau');
                }
            });
        }

        function openAddModal(modalId, formId, titleId, titleText) {
            const form = document.getElementById(formId);
            if (form) form.reset();
            const hiddenId = form ? form.querySelector('input[type="hidden"][name="id"]') : null;
            if (hiddenId) hiddenId.value = '';
            const title = document.getElementById(titleId);
            if (title) title.textContent = titleText;
            openModal(modalId);
        }

        function printAttestation(idEmploye) {
            window.open(
                'attestation_travail.php?id=' + idEmploye,
                '_blank'
            );
        }

        document.getElementById('form-edit-employe').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-edit-employe');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const formData = new FormData(e.target);
            const payload = Object.fromEntries(formData.entries());
            payload.service_national = formData.has('service_national');

            setSubmitLoading(submitBtn, true, 'Enregistrer');
            try {
                const { status, data } = await putJson('api/employe.php', payload);
                if (status >= 200 && status < 300) {
                    showToast('Employé modifié avec succès', 'success');
                    closeModal('modal-edit-employe');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showModalError('modal-edit-employe', data || 'Une erreur est survenue.');
                }
            } catch (err) {
                showModalError('modal-edit-employe', 'Erreur réseau, veuillez réessayer.');
            } finally {
                setSubmitLoading(submitBtn, false, 'Enregistrer');
            }
        });

        /* ── Helpers génériques pour pré-remplir un formulaire depuis un objet ── */
        function fillForm(form, obj) {
            Object.keys(obj).forEach(key => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field) field.value = obj[key] ?? '';
            });
        }

        /* ── Conjoint ── */
        function openEditConjointModal(conjoint) {
            const form = document.getElementById('form-conjoint');
            form.reset();
            document.getElementById('conjoint-id').value = conjoint.id;
            fillForm(form, conjoint);
            document.getElementById('modal-conjoint-title').textContent = 'Modifier le conjoint';
            openModal('modal-add-conjoint');
        }

        document.getElementById('form-conjoint').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-add-conjoint');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const payload = Object.fromEntries(new FormData(e.target).entries());
            const isEdit = !!payload.id;
            const url = 'api/conjoint.php';

            setSubmitLoading(submitBtn, true, 'Enregistrer');
            try {
                const { status, data } = (isEdit)?await putJson(url, payload):await postJson(url, payload);
                if (status >= 200 && status < 300) {
                    showToast(isEdit ? 'Conjoint modifié avec succès' : 'Conjoint ajouté avec succès', 'success');
                    closeModal('modal-add-conjoint');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showModalError('modal-add-conjoint', data || 'Une erreur est survenue.');
                }
            } catch (err) {
                showModalError('modal-add-conjoint', 'Erreur réseau, veuillez réessayer.');
            } finally {
                setSubmitLoading(submitBtn, false, 'Enregistrer');
            }
        });

        /* ── Enfant ── */
        function openEditEnfantModal(enfant) {
            const form = document.getElementById('form-enfant');
            form.reset();
            document.getElementById('enfant-id').value = enfant.id;
            fillForm(form, enfant);
            document.getElementById('modal-enfant-title').textContent = "Modifier l'enfant";
            openModal('modal-add-enfant');
        }

        document.getElementById('form-enfant').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-add-enfant');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const payload = Object.fromEntries(new FormData(e.target).entries());
            const isEdit = !!payload.id;
            const url = 'api/enfant.php';

            setSubmitLoading(submitBtn, true, 'Enregistrer');
            try {
                const { status, data } = (isEdit)?await putJson(url, payload):await postJson(url, payload);
                if (status >= 200 && status < 300) {
                    showToast(isEdit ? 'Enfant modifié avec succès' : 'Enfant ajouté avec succès', 'success');
                    closeModal('modal-add-enfant');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showModalError('modal-add-enfant', data || 'Une erreur est survenue.');
                }
            } catch (err) {
                showModalError('modal-add-enfant', 'Erreur réseau, veuillez réessayer.');
            } finally {
                setSubmitLoading(submitBtn, false, 'Enregistrer');
            }
        });

        /* ── Diplôme ── */
        function openEditDiplomeModal(diplome) {
            const form = document.getElementById('form-diplome');
            form.reset();
            document.getElementById('diplome-id').value = diplome.id;
            fillForm(form, diplome);
            document.getElementById('modal-diplome-title').textContent = 'Modifier le diplôme';
            openModal('modal-add-diplome');
        }

        document.getElementById('form-diplome').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-add-diplome');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const payload = Object.fromEntries(new FormData(e.target).entries());
            const isEdit = !!payload.id;
            const url = 'api/diplome.php';

            setSubmitLoading(submitBtn, true, 'Enregistrer');
            try {
                const { status, data } = (isEdit)?await putJson(url, payload):await postJson(url, payload);
                if (status >= 200 && status < 300) {
                    showToast(isEdit ? 'Diplôme modifié avec succès' : 'Diplôme ajouté avec succès', 'success');
                    closeModal('modal-add-diplome');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showModalError('modal-add-diplome', data || 'Une erreur est survenue.');
                }
            } catch (err) {
                showModalError('modal-add-diplome', 'Erreur réseau, veuillez réessayer.');
            } finally {
                setSubmitLoading(submitBtn, false, 'Enregistrer');
            }
        });

        /* ── Affectation (ajout ou modification — toujours sur le même formulaire) ── */
        async function openEditAffectationModal() {
            document.getElementById('modal-affectation-title').textContent = "Modifier l'affectation";
            openModal('modal-add-affectation');

            const idDep = <?= json_encode($employe['id_departement'] ?? null) ?>;
            const idService = <?= json_encode($employe['id_service'] ?? null) ?>;
            const idPoste = <?= json_encode($employe['id_poste'] ?? null) ?>;

            const form = document.getElementById('form-affectation');

            if (idDep) {
                selectDepAff.value = idDep;
                selectServiceAff.disabled = true;
                fillSelect(selectServiceAff, [], 'Chargement...');
                try {
                    const { status, data } = await getJson('api/service.php?id_dep=' + encodeURIComponent(idDep));
                    if (status >= 200 && status < 300) {
                        fillSelect(selectServiceAff, data, '— Choisir un service —', idService);
                        selectServiceAff.disabled = false;
                    }
                } catch (err) { /* silencieux */ }
            }

            if (idService) {
                selectPosteAff.disabled = true;
                fillSelect(selectPosteAff, [], 'Chargement...');
                try {
                    const { status, data } = await getJson('api/poste.php?id_serv=' + encodeURIComponent(idService));
                    if (status >= 200 && status < 300) {
                        fillSelect(selectPosteAff, data, '— Choisir un poste —', idPoste);
                        selectPosteAff.disabled = false;
                    }
                } catch (err) { /* silencieux */ }
            }
        }

        document.getElementById('form-affectation').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-add-affectation');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const payload = Object.fromEntries(new FormData(e.target).entries());
            const isEdit = <?= !empty($employe['poste_nom']) ? 'true' : 'false' ?>;
            const url = 'api/affectation.php';

            setSubmitLoading(submitBtn, true, 'Enregistrer');
            try {
                const { status, data } = (isEdit)?await putJson(url, payload):await postJson(url, payload);
                if (status >= 200 && status < 300) {
                    showToast(isEdit ? 'Affectation modifiée avec succès' : 'Affectation ajoutée avec succès', 'success');
                    closeModal('modal-add-affectation');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showModalError('modal-add-affectation', data || 'Une erreur est survenue.');
                }
            } catch (err) {
                showModalError('modal-add-affectation', 'Erreur réseau, veuillez réessayer.');
            } finally {
                setSubmitLoading(submitBtn, false, 'Enregistrer');
            }
        });

        function confirmDeleteAffectation(id_employe) {
            document.getElementById('confirm-delete-text').textContent =
                "Êtes-vous sûr de vouloir supprimer l'affectation de cet employé ?";
            const btn = document.getElementById('btn-confirm-delete');
            const idleHtml = 'Supprimer';
            btn.onclick = async function() {
                setSubmitLoading(btn, true, idleHtml);
                try {
                    
                    const {status, data} = await deleteJson('api/affectation.php', {'id_employe': id_employe.toString()});
                    if (status >= 200 && status < 300) {
                        showToast('Affectation supprimée avec succès', 'success');
                        setTimeout(() => location.reload(), 600);
                    } else {
                        showModalError('modal-confirm-delete', data || 'Une erreur est survenue.');
                        setSubmitLoading(btn, false, idleHtml);
                    }
                } catch (err) {
                    showModalError('modal-confirm-delete', 'Erreur réseau, veuillez réessayer.');
                    setSubmitLoading(btn, false, idleHtml);
                }
            };
            hideModalError('modal-confirm-delete');
            openModal('modal-confirm-delete');
        }

        function confirmDeleteItem(type, id) {
            document.getElementById('confirm-delete-text').textContent =
                `Êtes-vous sûr de vouloir supprimer ${deleteLabels[type]} ?`;
            const btn = document.getElementById('btn-confirm-delete');
            const idleHtml = 'Supprimer';
            btn.onclick = async function() {
                setSubmitLoading(btn, true, idleHtml);
                try {
                    const {status, data} = await deleteJson(deleteEndpoints[type], { 'id': id.toString() });
                    if (status >= 200 && status < 300) {
                        showToast('Suppression effectuée avec succès', 'success');
                        setTimeout(() => location.reload(), 600);
                    } else {
                        showModalError('modal-confirm-delete', data || 'Une erreur est survenue.');
                        setSubmitLoading(btn, false, idleHtml);
                    }
                } catch (err) {
                    showModalError('modal-confirm-delete', 'Erreur réseau, veuillez réessayer.');
                    setSubmitLoading(btn, false, idleHtml);
                }
            };
            hideModalError('modal-confirm-delete');
            openModal('modal-confirm-delete');
        }
    </script>
</body>
</html>