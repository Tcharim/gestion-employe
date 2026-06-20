<?php
    session_start();

    include_once "includes/utils.php";

    if(!isConnected($pdo)) {
        header('Location: '.WORKSPACE.'/login.php');
        exit;
    };

    $list_employe = getAllEmploye($pdo);
    $list_departements = getAllDepartementDetails($pdo);
    $list_categorie = getAllCategorie($pdo);
    $list_echellon = getAllEchellon($pdo);

    $activePanel = "emp";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des employés</title>

    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/style.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/dashboard.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/employe.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/themify-icons.css">
    
    <link rel="icon" href="images/images.jpg" type="image/jpeg">
</head>
<body>
    <?php include_once "includes/header.php" ?>
    <main class="dashboard-main">
        <?php include_once "includes/aside.php";?>
        <section>
            <h2>Gestion des employés:</h2>
            <section class="categorie-section">

                <button class="btn-add" onclick="openModal('modal-add-employe')">
                    <i class="ti ti-plus"></i> Ajouter un employé
                </button>

                <div class="search-bar">
                    <i class="ti ti-search"></i>
                    <input type="text" id="search-employe" placeholder="Rechercher un employé..." autocomplete="off">
                </div>

                <div class="card-grid" id="employe-grid">
                    <?php if($list_employe): ?>
                        <?php foreach($list_employe as $employe): ?>
                            <div class="card">
                                <button class="btn-del-small" onclick="openDelEmployeModal('<?= $employe['id'] ?>')">
                                    <i class="ti ti-close"></i> Supprimer
                                </button>

                                <a href="details_employe.php?id=<?= $employe['id'] ?>" style="text-decoration: none;" class="btn-add-small">
                                    <i class="ti ti-plus"></i> Détails
                                </a>
                                <div class="card-header">
                                    <h3 class="card-field header"><?= strtoupper(htmlspecialchars($employe['nom'])).' '.ucfirst(htmlspecialchars($employe['prenom'])) ?></h3>
                                </div>
                                <div class="card-field">
                                    <span class="label">Genre:</span> <span class="value"><?= ucfirst(htmlspecialchars($employe['genre'])) ?></span>
                                </div>
                                <div class="card-field">
                                    <span class="label">Tel:</span> <span class="value"><?= htmlspecialchars($employe['num_tel']) ?></span> 
                                </div>
                                <div class="card-field">
                                    <span class="label">Né(e) le:</span> <span class="value"><?= htmlspecialchars($employe['date_naissance']) ?></span>
                                </div>
                                <div class="card-field">
                                    <span class="label">Recruté(e) le:</span> <span class="value"><?= htmlspecialchars($employe['date_recrutement']) ?></span>
                                </div>
                                <br/>
                                <?php if($employe['date_affectation'] && $employe['nom_departement'] && $employe['nom_service'] && $employe['nom_poste']):  ?>
                                    <div class="card-field">
                                        <span class="label">Affecté(e) le:</span> <span class="value"><?= htmlspecialchars($employe['date_affectation']) ?></span>
                                    </div>
                                    <div class="card-field">
                                        <span class="label">Département:</span> <span class="value"><?= ucfirst(htmlspecialchars($employe['nom_departement'])) ?></span>
                                    </div>
                                    <div class="card-field">
                                        <span class="label">Service:</span> <span class="value"><?= ucfirst(htmlspecialchars($employe['nom_service'])) ?></span>
                                    </div>
                                    <div class="card-field">
                                        <span class="label">Poste:</span> <span class="value"><?= ucfirst(htmlspecialchars($employe['nom_poste'])) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="card-field">
                                        <span class="label">Poste:</span> <span class="value">Aucun</span>
                                    </div>
                                <?php endif ?>
                            </div>
                        <?php endforeach ?>
                    <?php else: ?>
                        <p>Aucun employé enregistré.</p>
                    <?php endif ?>
                    
                </div>
            </section>
        </section>
    </main>

    <div class="modal-overlay" id="modal-del-employe">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Supprimer un employé</h3>
                <button class="modal-close" onclick="closeModal('modal-del-employe')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <p>
                Êtes vous sur de supprimer cet employé?
            </p>
            <form id="form-del-employe">
                <input type="hidden" id="del-id-emp" name="id">
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-del-employe')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-add-employe">
        <div class="modal-box modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Ajouter un employé</h3>
                <button class="modal-close" onclick="closeModal('modal-add-employe')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <form id="form-add-employe">
                <div class="modal-body-scroll">

                    <h3>Informations personnelles</h3>

                    <div class="form-row">
                        <div class="form-group required">
                            <label>Nom</label>
                            <input type="text" name="nom" required>
                        </div>

                        <div class="form-group required">
                            <label>Prénom</label>
                            <input type="text" name="prenom" required>
                        </div>
                    </div>

                    <div class="form-group required">
                        <label>Adresse</label>
                        <input type="text" name="adresse" required>
                    </div>

                    <div class="form-row required">
                        <div class="form-group">
                            <label>Date de naissance</label>
                            <input type="date" name="date_naissance" required>
                        </div>

                        <div class="form-group required">
                            <label>Lieu de naissance</label>
                            <input type="text" name="lieu_naissance" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group required">
                            <label>Nationalité</label>
                            <input type="text" name="nationalite" required>
                        </div>

                        <div class="form-group required">
                            <label>Genre</label>
                            <select name="genre" required>
                                <option value="m">Homme</option>
                                <option value="f">Femme</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group required">
                            <label>Situation familiale</label>
                            <select name="civilite">
                                <option value="c">Célibataire</option>
                                <option value="m">Marié(e)</option>
                                <option value="d">Divorcé(e)</option>
                                <option value="v">Veuf(ve)</option>
                            </select>
                        </div>

                        <div class="form-group required">
                            <label>État de santé</label>
                            <select name="etat_sante">
                                <option value="s">Sain</option>
                                <option value="m">Maladie chronique</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group required">
                        <label>
                            <input type="checkbox" name="service_national" value="1">
                            Service national effectué
                        </label>
                    </div>

                    <hr>

                    <h3>Informations administratives</h3>

                    <div class="form-row">
                        <div class="form-group required">
                            <label>Téléphone</label>
                            <input type="text" maxlength="10" name="num_tel" required>
                        </div>

                        <div class="form-group required">
                            <label>N° Assurance</label>
                            <input type="text" maxlength="13" name="num_assurance" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group required">
                            <label>NIN</label>
                            <input type="text" maxlength="18" name="nin" required>
                        </div>

                        <div class="form-group required">
                            <label>RIB</label>
                            <input type="text" maxlength="15" name="num_rib" required>
                        </div>
                    </div>

                    <div class="form-group required">
                        <label>Date de recrutement</label>
                        <input type="date" name="date_recrutement" value="<?= date('Y-m-d') ?>">
                    </div>

                    <hr>

                    <h3>Echellonnement</h3>
                    <div class="form-row">
                        <div class="form-group required">
                            <label>Catégorie</label>

                            <select name="id_categorie">
                                <?php foreach($list_categorie as $cat): ?>
                                    <option value="<?= $cat['id'] ?>">
                                        <?= htmlspecialchars($cat['nom']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group required">
                            <label>Échelon</label>

                            <select name="id_echellon">
                                <?php foreach($list_echellon as $e): ?>
                                    <option value="<?= $e['num'] ?>">
                                        <?= $e['num'] ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group required">
                        <label>Date d'obtention de l'echellonement</label>
                        <input type="date" name="date_obtention_echellonnement" value="<?= date('Y-m-d') ?>">
                    </div>

                    <br/>

                    <div class="scroll-cue" id="scroll-cue-employe">
                        <i class="ti ti-chevron-down"></i>
                    </div>

                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-add-employe')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <script src="javascripts/script.js"></script>
    <script>
        function openDelEmployeModal(idEmp) {
            document.getElementById('del-id-emp').value = idEmp;
            openModal('modal-del-employe');
        }

        /* ── Recherche en temps réel ── */
        function escapeHtmlEmp(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function ucfirstEmp(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function renderEmployeList(list) {
            const grid = document.getElementById('employe-grid');

            if (!list || list.length === 0) {
                grid.innerHTML = '<p class="card-grid-empty">Aucun employé trouvé.</p>';
                return;
            }

            grid.innerHTML = list.map(employe => {
                const hasAffectation = employe.date_affectation && employe.nom_departement && employe.nom_service && employe.nom_poste;

                const affectationHtml = hasAffectation ? `
                    <div class="card-field">
                        <span class="label">Affecté(e) le:</span> <span class="value">${escapeHtmlEmp(employe.date_affectation)}</span>
                    </div>
                    <div class="card-field">
                        <span class="label">Département:</span> <span class="value">${escapeHtmlEmp(ucfirstEmp(employe.nom_departement))}</span>
                    </div>
                    <div class="card-field">
                        <span class="label">Service:</span> <span class="value">${escapeHtmlEmp(ucfirstEmp(employe.nom_service))}</span>
                    </div>
                    <div class="card-field">
                        <span class="label">Poste:</span> <span class="value">${escapeHtmlEmp(ucfirstEmp(employe.nom_poste))}</span>
                    </div>
                ` : `
                    <div class="card-field">
                        <span class="label">Poste:</span> <span class="value">Aucun</span>
                    </div>
                `;

                return `
                    <div class="card">
                        <button class="btn-del-small" onclick="openDelEmployeModal('${employe.id}')">
                            <i class="ti ti-close"></i> Supprimer
                        </button>

                        <a href="details_employe.php?id=${employe.id}" style="text-decoration: none;" class="btn-add-small">
                            <i class="ti ti-plus"></i> Détails
                        </a>
                        <div class="card-header">
                            <h3 class="card-field header">${escapeHtmlEmp(employe.nom.toUpperCase())} ${escapeHtmlEmp(ucfirstEmp(employe.prenom))}</h3>
                        </div>
                        <div class="card-field">
                            <span class="label">Genre:</span> <span class="value">${escapeHtmlEmp(ucfirstEmp(employe.genre))}</span>
                        </div>
                        <div class="card-field">
                            <span class="label">Tel:</span> <span class="value">${escapeHtmlEmp(employe.num_tel)}</span>
                        </div>
                        <div class="card-field">
                            <span class="label">Né(e) le:</span> <span class="value">${escapeHtmlEmp(employe.date_naissance)}</span>
                        </div>
                        <div class="card-field">
                            <span class="label">Recruté(e) le:</span> <span class="value">${escapeHtmlEmp(employe.date_recrutement)}</span>
                        </div>
                        <br/>
                        ${affectationHtml}
                    </div>
                `;
            }).join('');
        }

        let searchDebounceTimer = null;
        const searchInput = document.getElementById('search-employe');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(async () => {
                    try {
                        const { status, data } = await getJson('api/employe.php?q=' + encodeURIComponent(query));
                        if (status >= 200 && status < 300) {
                            renderEmployeList(data);
                        }
                    } catch (err) {
                        // Erreur réseau silencieuse pour ne pas perturber la saisie
                    }
                }, 300);
            });
        }

        document.getElementById('form-add-employe').addEventListener('submit', async function(e) {
            e.preventDefault();

            hideModalError('modal-add-employe');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const formData = new FormData(this);
            const payload = Object.fromEntries(formData.entries());
            payload.service_national = document.getElementById('service_national')?.checked ? 1 : 0;

            try {
                const { status, data } = await postJson(
                    'api/employe.php',
                    payload
                );

                if (status >= 200 && status < 300) {
                    closeModal('modal-add-employe');
                    showToast('Employé ajouté avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showModalError(
                        'modal-add-employe',
                        data || 'Une erreur est survenue.'
                    );
                }
            } catch (err) {
                showModalError(
                    'modal-add-employe',
                    'Erreur réseau, veuillez réessayer.'
                );
            }

        });

        document.getElementById('form-del-employe').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-del-employe');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const id = document.getElementById('del-id-emp').value;

            try {
                const { status, data } = await deleteJson('api/employe.php', { id });

                if (status >= 200 && status < 300) {
                    closeModal('modal-del-employe');
                    showToast('Employé supprimé avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showModalError(
                        'modal-del-employe',
                        data || 'Une erreur est survenue.'
                    );
                }
            } catch (err) {
                showModalError(
                    'modal-del-employe',
                    'Erreur réseau, veuillez réessayer.'
                );
            }

        });

        const selectDep = document.getElementById('select-dep');
        const selectService = document.getElementById('select-service');
        const selectPoste = document.getElementById('select-poste');

        if (selectDep) {
            selectDep.addEventListener('change', async function() {
                fillSelect(selectPoste, [], '— Choisir un service d\'abord —');
                selectPoste.disabled = true;

                if (!this.value) {
                    fillSelect(selectService, [], '— Choisir un département d\'abord —');
                    selectService.disabled = true;
                    return;
                }

                selectService.disabled = true;
                fillSelect(selectService, [], 'Chargement...');

                try {
                    const { status, data } = await getJson('api/service.php?id_dep=' + encodeURIComponent(this.value));
                    if (status >= 200 && status < 300) {
                        fillSelect(selectService, data, '— Choisir un service —');
                        selectService.disabled = false;
                    } else {
                        fillSelect(selectService, [], 'Erreur de chargement');
                    }
                } catch (err) {
                    fillSelect(selectService, [], 'Erreur réseau');
                }
            });
        }

        if (selectService) {
            selectService.addEventListener('change', async function() {
                if (!this.value) {
                    fillSelect(selectPoste, [], '— Choisir un service d\'abord —');
                    selectPoste.disabled = true;
                    return;
                }

                selectPoste.disabled = true;
                fillSelect(selectPoste, [], 'Chargement...');

                try {
                    const { status, data } = await getJson('api/service.php?id_serv=' + encodeURIComponent(this.value));
                    if (status >= 200 && status < 300) {
                        fillSelect(selectPoste, data, '— Choisir un poste —');
                        selectPoste.disabled = false;
                    } else {
                        fillSelect(selectPoste, [], 'Erreur de chargement');
                    }
                } catch (err) {
                    fillSelect(selectPoste, [], 'Erreur réseau');
                }
            });
        }
    </script>
</body>
</html>