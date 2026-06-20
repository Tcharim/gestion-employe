<?php
    session_start();

    include_once "includes/utils.php";

    if(!isConnected($pdo)) {
        header('Location: '.WORKSPACE.'/login.php');
        exit;
    };

    $list_departement = getAllDepartementDetails($pdo);
    
    $activePanel = "org";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des employés</title>

    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/style.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/dashboard.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/organisation.css">
    <link rel="stylesheet" href="<?= WORKSPACE ?>/stylesheets/themify-icons.css">
    
    <link rel="icon" href="images/images.jpg" type="image/jpeg">
</head>
<body>
    <?php include_once "includes/header.php" ?>
    <main class="dashboard-main">

        <?php include_once "includes/aside.php";?>
        <section>
            <h2>Gestion de l'organisation:</h2>
            <section class="categorie-section">
                <button class="btn-add" onclick="openModal('modal-add-departement')">
                    <i class="ti ti-plus"></i> Ajouter un département
                </button>

                <div class="card-grid" id="departement-grid">
                    <?php if($list_departement): ?>
                        <?php foreach($list_departement as $departement): ?>
                            <div class="card">
                                <button class="btn-del-small" onclick="openDelDepartementModal('<?= $departement['id'] ?>')">
                                    <i class="ti ti-close"></i> Supprimer
                                </button>
                                <button class="btn-modify-small" onclick="openUpdateDepartementModal('<?= $departement['id'] ?>', '<?= htmlspecialchars($departement['nom'], ENT_QUOTES) ?>')">
                                    <i class="ti ti-pencil"></i> Modifier
                                </button>
                                <button class="btn-add-small btn-add" onclick="openAddServiceModal('<?= $departement['id'] ?>', '<?= htmlspecialchars($departement['nom'], ENT_QUOTES) ?>')">
                                    <i class="ti ti-plus"></i> Service
                                </button>
                                <h3 class="card-field header" style="margin-top:15px"><?= ucfirst(htmlspecialchars($departement['nom'])) ?></h3>
                                

                                <div class="sub-card-grid" id="services-of-<?= $departement['id'] ?>">
                                    <?php if(isset($departement['list_service'])): ?>
                                        <?php foreach(json_decode($departement['list_service']) as $service): ?>
                                            <div class="card sub-card">
                                                <button class="btn-del-small" onclick="openDelServiceModal('<?= $service->id ?>')">
                                                    <i class="ti ti-close"></i> Supprimer
                                                </button>
                                                <button class="btn-modify-small" onclick="openUpdateServiceModal('<?= $service->id ?>', '<?= htmlspecialchars($service->nom, ENT_QUOTES) ?>')">
                                                    <i class="ti ti-pencil"></i> Modifier
                                                </button>
                                                <button class="btn-add-small" onclick="openAddPosteModal('<?= $service->id ?>', '<?= htmlspecialchars($service->nom, ENT_QUOTES) ?>')">
                                                    <i class="ti ti-plus"></i> Poste
                                                </button>
                                                <div class="card-header">
                                                    <h4 class="card-field"><?= ucfirst(htmlspecialchars($service->nom)) ?></h4>
                                                </div>

                                                <ul class="poste-list" id="postes-of-<?= $service->id ?>">
                                                    <?php if(isset($service->list_poste)): ?>
                                                        <?php foreach($service->list_poste as $poste): ?>
                                                            <li>
                                                                <?= ucfirst(htmlspecialchars($poste->nom)) ?>
                                                                <span class="postes-actions">
                                                                    <button class="modify-btn" onclick="openUpdatePosteModal('<?= $poste->id ?>', '<?= htmlspecialchars($poste->nom, ENT_QUOTES) ?>')">
                                                                        <i class="ti ti-pencil"></i>
                                                                    </button>
                                                                    <button class="delete-btn" onclick="openDelPosteModal('<?= $poste->id ?>')">
                                                                        <i class="ti ti-trash"></i>
                                                                    </button>
                                                                </span>
                                                            </li>
                                                        <?php endforeach ?>
                                                    <?php else: ?>
                                                        <p>Aucun poste enregistré</p>
                                                    <?php endif ?>
                                                </ul>
                                            </div>
                                        <?php endforeach ?>
                                    <?php else: ?>
                                        <p>Aucun service enregistré</p>
                                    <?php endif ?>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php else: ?>
                        <p>Aucun département enregistré.</p>
                    <?php endif ?>
                </div>
            </section>
        </section>

    </main>

    <div class="modal-overlay" id="modal-del">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title"></h3>
                <button class="modal-close" onclick="closeModal('modal-del')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <p>
                
            </p>
            <form id="form-del">
                <input type="hidden" id="del-action" name="action">
                <input type="hidden" id="del-id" name="id">
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-del')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-update">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title"></h3>
                <button class="modal-close" onclick="closeModal('modal-update')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>

            <form id="form-update">
                <input type="hidden" id="update-action" name="action">
                <input type="hidden" id="update-id" name="id">
                <div class="modal-field">
                    <label for="update-nom">Nom: </label>
                    <input type="text" id="update-nom" name="nom" required>
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-update')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Modifier</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-add-departement">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Ajouter un département</h3>
                <button class="modal-close" onclick="closeModal('modal-add-departement')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <form id="form-add-departement">
                <div class="modal-field">
                    <label for="dep-nom">Nom du département</label>
                    <input type="text" id="dep-nom" name="nom" placeholder="Ex: Ressources humaines" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-add-departement')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-add-service">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Ajouter un service</h3>
                <button class="modal-close" onclick="closeModal('modal-add-service')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <form id="form-add-service">
                <input type="hidden" id="serv-num-dep" name="num_dep">
                <div class="modal-field">
                    <label>Département</label>
                    <input type="text" id="serv-dep-label" disabled>
                </div>
                <div class="modal-field">
                    <label for="serv-nom">Nom du service</label>
                    <input type="text" id="serv-nom" name="nom" placeholder="Ex: Recrutement" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-add-service')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-add-poste">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Ajouter un poste</h3>
                <button class="modal-close" onclick="closeModal('modal-add-poste')"><i class="ti ti-close"></i></button>
            </div>
            <div class="modal-error"></div>
            <form id="form-add-poste">
                <input type="hidden" id="poste-num-serv" name="num_service">
                <div class="modal-field">
                    <label>Service</label>
                    <input type="text" id="poste-serv-label" disabled>
                </div>
                <div class="modal-field">
                    <label for="poste-nom">Nom du poste</label>
                    <input type="text" id="poste-nom" name="nom" placeholder="Ex: Chargé de recrutement" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('modal-add-poste')">Annuler</button>
                    <button type="submit" class="modal-btn modal-btn-submit">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <script src="javascripts/script.js"></script>
    <script>
        function openDelDepartementModal(numDep) {
            document.getElementById('modal-del').querySelector('h3.modal-title').innerHTML = "Supprimer un département";
            document.getElementById('modal-del').querySelector('p').innerHTML = "Êtes vous sur de supprimer ce département?";
            
            document.getElementById('del-id').value = numDep;
            document.getElementById('del-action').value = "api/departement.php";
            openModal('modal-del');
        }

        function openDelServiceModal(numServ) {
            document.getElementById('modal-del').querySelector('h3.modal-title').innerHTML = "Supprimer un service";
            document.getElementById('modal-del').querySelector('p').innerHTML = "Êtes vous sur de supprimer ce service?";
            
            document.getElementById('del-id').value = numServ;
            document.getElementById('del-action').value = "api/service.php";
            openModal('modal-del');
        }

        function openDelPosteModal(numPoste) {
            document.getElementById('modal-del').querySelector('h3.modal-title').innerHTML = "Supprimer un poste";
            document.getElementById('modal-del').querySelector('p').innerHTML = "Êtes vous sur de supprimer ce poste?";
            
            document.getElementById('del-id').value = numPoste;
            document.getElementById('del-action').value = "api/poste.php";
            openModal('modal-del');
        }

        function openUpdateDepartementModal(numDep, nomDep) {
            document.getElementById('modal-update').querySelector('h3.modal-title').innerHTML = "Modifier un département";
            
            document.getElementById('update-id').value = numDep;
            document.getElementById('update-nom').value = nomDep;
            document.getElementById('update-action').value = "api/departement.php";
            openModal('modal-update');
        }

        function openUpdateServiceModal(numServ, nomServ) {
            document.getElementById('modal-update').querySelector('h3.modal-title').innerHTML = "Modifier un service";
            
            document.getElementById('update-id').value = numServ;
            document.getElementById('update-nom').value = nomServ;
            document.getElementById('update-action').value = "api/service.php";
            openModal('modal-update');
        }

        function openUpdatePosteModal(numPoste, nomPoste) {
            document.getElementById('modal-update').querySelector('h3.modal-title').innerHTML = "Modifier un poste";
            
            document.getElementById('update-id').value = numPoste;
            document.getElementById('update-nom').value = nomPoste;
            document.getElementById('update-action').value = "api/poste.php";
            openModal('modal-update');
        }

        function openAddServiceModal(numDep, nomDep) {
            document.getElementById('serv-num-dep').value = numDep;
            document.getElementById('serv-dep-label').value = ucfirstStr(nomDep);
            openModal('modal-add-service');
        }

        function openAddPosteModal(numServ, nomServ) {
            document.getElementById('poste-num-serv').value = numServ;
            document.getElementById('poste-serv-label').value = ucfirstStr(nomServ);
            openModal('modal-add-poste');
        }

        document.getElementById('form-add-departement').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-add-departement');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const nom = document.getElementById('dep-nom').value.trim();

            setSubmitLoading(submitBtn, true, "Ajouter");
            try {
                const { status, data } = await postJson('api/departement.php', { nom });

                if (status >= 200 && status < 300) {
                    closeModal('modal-add-departement');
                    showToast('Département ajouté avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showModalError('modal-add-departement', data || 'Une erreur est survenue.');
                }
            } catch (err) {
                showModalError('modal-add-departement', 'Erreur réseau, veuillez réessayer.');
            } finally {
                setSubmitLoading(submitBtn, false, "Ajouter");
            }
        });

        document.getElementById('form-add-service').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-add-service');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const id_departement = document.getElementById('serv-num-dep').value;
            const nom = document.getElementById('serv-nom').value.trim();

            setSubmitLoading(submitBtn, true, "Ajouter");
            try {
                const { status, data } = await postJson('api/service.php', { id_departement, nom });

                if (status >= 200 && status < 300) {
                    closeModal('modal-add-service');
                    showToast('Service ajouté avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showModalError('modal-add-service', data || 'Une erreur est survenue.');
                }
            } catch (err) {
                showModalError('modal-add-service', 'Erreur réseau, veuillez réessayer.');
            } finally {
                setSubmitLoading(submitBtn, false, "Ajouter");
            }
        });

        document.getElementById('form-add-poste').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-add-poste');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const id_service = document.getElementById('poste-num-serv').value;
            const nom = document.getElementById('poste-nom').value.trim();

            setSubmitLoading(submitBtn, true, "Ajouter");
            try {
                const { status, data } = await postJson('api/poste.php', { id_service, nom });

                if (status >= 200 && status < 300) {
                    closeModal('modal-add-poste');
                    showToast('Poste ajouté avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showModalError('modal-add-poste', data || 'Une erreur est survenue.');
                }
            } catch (err) {
                showModalError('modal-add-poste', 'Erreur réseau, veuillez réessayer.');
            } finally {
                setSubmitLoading(submitBtn, false, "Ajouter");
            }
        });

        document.getElementById('form-del').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-del');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const id = document.getElementById('del-id').value;
            const action = document.getElementById('del-action').value.trim();

            setSubmitLoading(submitBtn, true, "Supprimer");
            try {
                const { status, data } = await deleteJson(action, { id });

                if (status >= 200 && status < 300) {
                    closeModal('modal-del');
                    showToast('Suppression avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showModalError('modal-del', data || 'Une erreur est survenue.');
                }
            } catch (err) {
                showModalError('modal-del', 'Erreur réseau, veuillez réessayer.');
            } finally {
                setSubmitLoading(submitBtn, false, "Supprimer");
            }
        });

        document.getElementById('form-update').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideModalError('modal-update');
            const submitBtn = e.target.querySelector('.modal-btn-submit');
            const id = document.getElementById('update-id').value;
            const nom = document.getElementById('update-nom').value;
            const action = document.getElementById('update-action').value.trim();

            setSubmitLoading(submitBtn, true, "Modifier");
            try {
                const { status, data } = await putJson(action, { id, nom });

                if (status >= 200 && status < 300) {
                    closeModal('modal-update');
                    showToast('Modification avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showModalError('modal-update', data || 'Une erreur est survenue.');
                }
            } catch (err) {
                showModalError('modal-update', 'Erreur réseau, veuillez réessayer.');
            } finally {
                setSubmitLoading(submitBtn, false, "Modifier");
            }
        });
    </script>
</body>
</html>