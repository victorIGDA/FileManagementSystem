<?php $title = 'Mi perfil'; ?>
<div class="page-heading profile-page-heading">
    <div>
        <p class="eyebrow">Configuración de cuenta</p>
        <h1>Mi perfil</h1>
        <p>Administra tu fotografía, información personal y contraseña.</p>
    </div>
</div>

<div class="profile-stack">
    <form class="panel profile-card" method="post" enctype="multipart/form-data" action="<?= url('/perfil') ?>">
        <?= csrf_field() ?>
        <div class="profile-avatar-shell">
            <label class="profile-avatar-clickable" for="foto" title="Seleccionar una fotografía">
                <?php if ($user['foto_perfil']): ?>
                    <img class="profile-avatar-preview" id="profilePhotoPreview" src="<?= url('/perfil/foto/' . $user['foto_perfil']) ?>" alt="Fotografía de <?= e($user['nombre_usuario']) ?>">
                    <span class="profile-avatar-initial d-none" id="profilePhotoInitial"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
                <?php else: ?>
                    <img class="profile-avatar-preview d-none" id="profilePhotoPreview" alt="Vista previa de la fotografía">
                    <span class="profile-avatar-initial" id="profilePhotoInitial"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
                <?php endif; ?>
                <span class="profile-photo-edit"><i class="bi bi-pencil-fill"></i></span>
            </label>
            <input class="visually-hidden" type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" data-max-bytes="<?= (int) App\Core\Env::get('MAX_PROFILE_MB', 3) * 1024 * 1024 ?>">
        </div>

        <div class="profile-identity">
            <span class="profile-account-label">Usuario</span>
            <h2><?= e($user['nombre_usuario']) ?></h2>
            <p><i class="bi bi-shield-check"></i> <?= e($user['rol']) ?></p>
            <span class="profile-photo-feedback" id="profilePhotoFeedback">Haz clic sobre la foto o el lápiz para cambiarla.</span>
        </div>

        <div class="profile-divider"></div>

        <div class="profile-fields row g-3">
            <div class="col-md-6">
                <label class="form-label" for="nombre_completo">Nombre completo <span class="required">*</span></label>
                <input class="form-control" id="nombre_completo" name="nombre_completo" maxlength="150" value="<?= e($user['nombre_completo']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="telefono">Teléfono</label>
                <input class="form-control" id="telefono" name="telefono" maxlength="25" value="<?= e($user['telefono']) ?>" placeholder="Ej. 809-000-0000">
            </div>
            <div class="col-md-6">
                <label class="form-label">Correo</label>
                <div class="readonly-field"><i class="bi bi-envelope"></i><span><?= e($user['correo']) ?></span></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Formatos de fotografía</label>
                <div class="readonly-field"><i class="bi bi-image"></i><span>JPG, PNG o WEBP · Máx. <?= e(App\Core\Env::get('MAX_PROFILE_MB', 3)) ?> MB</span></div>
            </div>
        </div>

        <button class="btn btn-primary profile-submit" id="profileSubmit" type="submit"><i class="bi bi-check2-circle me-1"></i> Actualizar perfil</button>
    </form>

    <form class="panel password-card" method="post" action="<?= url('/perfil/contrasena') ?>">
        <?= csrf_field() ?>
        <div class="password-card-heading">
            <span class="security-icon"><i class="bi bi-shield-lock-fill"></i></span>
            <h2>Cambiar contraseña</h2>
            <p>Utiliza una contraseña segura de al menos 8 caracteres.</p>
        </div>
        <div class="profile-divider"></div>
        <div class="row g-3 password-grid">
            <div class="col-lg-4">
                <label class="form-label" for="contrasena_actual">Contraseña actual <span class="required">*</span></label>
                <div class="password-input-wrap"><input class="form-control" type="password" id="contrasena_actual" name="contrasena_actual" autocomplete="current-password" required><button type="button" data-password-toggle="contrasena_actual" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button></div>
            </div>
            <div class="col-lg-4">
                <label class="form-label" for="nueva_contrasena">Nueva contraseña <span class="required">*</span></label>
                <div class="password-input-wrap"><input class="form-control" type="password" id="nueva_contrasena" name="nueva_contrasena" minlength="8" autocomplete="new-password" required><button type="button" data-password-toggle="nueva_contrasena" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button></div>
            </div>
            <div class="col-lg-4">
                <label class="form-label" for="confirmar_contrasena">Confirmar contraseña <span class="required">*</span></label>
                <div class="password-input-wrap"><input class="form-control" type="password" id="confirmar_contrasena" name="confirmar_contrasena" minlength="8" autocomplete="new-password" required><button type="button" data-password-toggle="confirmar_contrasena" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button></div>
            </div>
        </div>
        <button class="btn btn-primary password-submit" type="submit"><i class="bi bi-key-fill me-1"></i> Actualizar contraseña</button>
    </form>
</div>
