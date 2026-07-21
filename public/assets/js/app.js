const sidebarToggle = document.getElementById('sidebarToggle');

function syncSidebarToggle() {
    if (!sidebarToggle) return;
    const compact = document.documentElement.classList.contains('sidebar-compact');
    sidebarToggle.setAttribute('aria-label', compact ? 'Expandir menú' : 'Contraer menú');
    sidebarToggle.setAttribute('title', compact ? 'Expandir menú' : 'Contraer menú');
}

if (sidebarToggle) {
    syncSidebarToggle();
    sidebarToggle.addEventListener('click', () => {
        document.documentElement.classList.toggle('sidebar-compact');
        const compact = document.documentElement.classList.contains('sidebar-compact');
        try { localStorage.setItem('sidebarCompact', String(compact)); } catch (error) {}
        syncSidebarToggle();
    });
}

document.querySelectorAll('#sidebar a.nav-link').forEach((link) => {
    link.addEventListener('click', () => {
        if (window.innerWidth >= 992 || typeof bootstrap === 'undefined') return;
        const sidebar = document.getElementById('sidebar');
        bootstrap.Offcanvas.getOrCreateInstance(sidebar).hide();
    });
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll('.tracked-audio').forEach((audio) => {
    let recorded = false;
    audio.addEventListener('play', () => {
        if (recorded) return;
        recorded = true;
        fetch(audio.dataset.recordUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: '_csrf=' + encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content),
            credentials: 'same-origin'
        }).catch(() => { recorded = false; });
    });
});

document.querySelectorAll('input[type="file"]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files[0];
        if (file) input.setAttribute('aria-label', file.name);
    });
});

const profilePhotoInput = document.getElementById('foto');
if (profilePhotoInput) {
    const preview = document.getElementById('profilePhotoPreview');
    const initial = document.getElementById('profilePhotoInitial');
    const feedback = document.getElementById('profilePhotoFeedback');
    const submit = document.getElementById('profileSubmit');
    let previewUrl = null;

    profilePhotoInput.addEventListener('change', () => {
        const file = profilePhotoInput.files[0];
        feedback.classList.remove('ready', 'error');
        if (!file) return;

        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        const maxBytes = Number(profilePhotoInput.dataset.maxBytes || 0);
        if (!allowed.includes(file.type)) {
            profilePhotoInput.value = '';
            feedback.textContent = 'Selecciona una imagen JPG, PNG o WEBP válida.';
            feedback.classList.add('error');
            return;
        }
        if (maxBytes && file.size > maxBytes) {
            profilePhotoInput.value = '';
            feedback.textContent = 'La imagen supera el tamaño máximo permitido.';
            feedback.classList.add('error');
            return;
        }

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.classList.remove('d-none');
        initial.classList.add('d-none');
        feedback.textContent = file.name + ' está lista para guardarse.';
        feedback.classList.add('ready');
        submit.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Guardar nueva fotografía';
    });
}

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.innerHTML = showing ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        button.setAttribute('aria-label', showing ? 'Mostrar contraseña' : 'Ocultar contraseña');
        input.focus();
    });
});
