function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
    const form = document.querySelector('#' + id + ' form');
    if (form) form.reset();
    hideModalError(id);
}

function showModalError(modalId, message) {
    const err = document.querySelector('#' + modalId + ' .modal-error');
    err.textContent = message;
    err.classList.add('show');
}

function hideModalError(modalId) {
    const err = document.querySelector('#' + modalId + ' .modal-error');
    err.classList.remove('show');
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = '<i class="ti ti-' + (type === 'success' ? 'check' : 'alert-circle') + '"></i><span>' + message + '</span>';
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

function setSubmitLoading(btn, loading, idleHtml) {
    btn.disabled = loading;
    btn.innerHTML = loading
        ? '<i class="ti ti-loader-2"></i> En cours...'
        : idleHtml;
}

async function deleteJson(url, payload) {
    const res = await fetch(url, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const json = await res.json();
    return { status: json.status, data: json.data };
}

async function putJson(url, payload) {
    const res = await fetch(url, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const json = await res.json();
    return { status: json.status, data: json.data };
}

async function postJson(url, payload) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const json = await res.json();
    return { status: json.status, data: json.data };
}

async function getJson(url) {
    const res = await fetch(url);
    const json = await res.json();
    return { status: json.status, data: json.data };
}

function ucfirstStr(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function initScrollCue(modalId) {
    const scrollArea = document.querySelector('#' + modalId + ' .modal-body-scroll');
    const cue = document.querySelector('#' + modalId + ' .scroll-cue');
    if (!scrollArea || !cue) return;

    const updateCue = () => {
        const atBottom = scrollArea.scrollTop + scrollArea.clientHeight >= scrollArea.scrollHeight - 4;
        const hasOverflow = scrollArea.scrollHeight > scrollArea.clientHeight + 4;
        cue.classList.toggle('hide', atBottom || !hasOverflow);
    };

    requestAnimationFrame(() => requestAnimationFrame(updateCue));
    scrollArea.removeEventListener('scroll', scrollArea._cueHandler || (() => {}));
    scrollArea._cueHandler = updateCue;
    scrollArea.addEventListener('scroll', updateCue);
}

function fillSelect(select, items, placeholder) {
    select.innerHTML = '';
    const opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = placeholder;
    select.appendChild(opt0);

    items.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = ucfirstStr(item.nom);
        select.appendChild(opt);
    });
}

function resetCascadingSelects() {
    if (!selectDep) return;
    selectDep.value = '';
    fillSelect(selectService, [], '— Choisir un département d\'abord —');
    selectService.disabled = true;
    fillSelect(selectPoste, [], '— Choisir un service d\'abord —');
    selectPoste.disabled = true;
}