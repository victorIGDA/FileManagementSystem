<?php
$editing = $audio !== null;
$title = $editing ? 'Editar audio' : 'Cargar audio';
$action = $editing
    ? url('/audios/' . $audio['id_audio'] . '/editar')
    : url('/audios');
$cancelUrl = $editing
    ? url('/audios/' . $audio['id_audio'])
    : url('/audios');
$selectedCategory = (int) (
    $_SESSION['_old']['id_categoria']
    ?? $audio['id_categoria']
    ?? 0
);
$durationValue = '';

if ($editing && ($audio['duracion_segundos'] ?? null) !== null) {
    $seconds = (int) $audio['duracion_segundos'];
    $durationValue = sprintf(
        '%02d:%02d:%02d',
        intdiv($seconds, 3600),
        intdiv($seconds % 3600, 60),
        $seconds % 60
    );
}
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">Biblioteca</p>
        <h1><?= e($title) ?></h1>
        <p>
            <?= $editing
                ? 'Actualiza la clasificación y los metadatos.'
                : 'Registra un archivo MP3 o WAV con sus metadatos.' ?>
        </p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= $cancelUrl ?>">
        Cancelar
    </a>
</div>

<form
    class="panel"
    method="post"
    enctype="multipart/form-data"
    action="<?= $action ?>"
>
    <?= csrf_field() ?>

    <?php if (!$editing): ?>
        <div class="upload-box mb-4">
            <label class="form-label fw-semibold" for="archivo">
                Archivo de audio *
            </label>
            <input
                class="form-control"
                type="file"
                id="archivo"
                name="archivo"
                accept=".mp3,.wav,audio/mpeg,audio/wav"
                required
            >
            <div class="form-text">
                MP3 o WAV · máximo
                <?= e(App\Core\Env::get('MAX_AUDIO_MB', 100)) ?> MB.
                El sistema verificará el contenido y evitará duplicados.
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="titulo">Título *</label>
            <input
                class="form-control"
                id="titulo"
                name="titulo"
                maxlength="200"
                value="<?= old('titulo', $audio['titulo'] ?? '') ?>"
                required
            >
        </div>

        <div class="col-md-6">
            <label class="form-label" for="id_categoria">
                Categoría *
            </label>
            <select
                class="form-select"
                id="id_categoria"
                name="id_categoria"
                required
            >
                <option value="">Seleccionar</option>
                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= $category['id_categoria'] ?>"
                        <?= $selectedCategory === $category['id_categoria']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($category['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label" for="artista">Artista</label>
            <input
                class="form-control"
                id="artista"
                name="artista"
                maxlength="150"
                value="<?= old('artista', $audio['artista'] ?? '') ?>"
            >
        </div>

        <div class="col-md-4">
            <label class="form-label" for="locutor">
                Locutor / responsable
            </label>
            <input
                class="form-control"
                id="locutor"
                name="locutor"
                maxlength="150"
                value="<?= old('locutor', $audio['locutor'] ?? '') ?>"
            >
        </div>

        <div class="col-md-4">
            <label class="form-label" for="cliente">Cliente</label>
            <input
                class="form-control"
                id="cliente"
                name="cliente"
                maxlength="150"
                value="<?= old('cliente', $audio['cliente'] ?? '') ?>"
            >
        </div>

        <div class="col-md-4">
            <label class="form-label" for="fecha_produccion">
                Fecha de producción
            </label>
            <input
                class="form-control"
                type="date"
                id="fecha_produccion"
                name="fecha_produccion"
                value="<?= old(
                    'fecha_produccion',
                    $audio['fecha_produccion'] ?? ''
                ) ?>"
            >
        </div>

        <div class="col-md-4">
            <label class="form-label" for="duracion_segundos">
                Duración (HH:MM:SS)
            </label>
            <input
                class="form-control"
                type="text"
                id="duracion_segundos"
                name="duracion_segundos"
                inputmode="numeric"
                pattern="[0-9]{2,}:[0-5][0-9]:[0-5][0-9]"
                placeholder="00:03:45"
                value="<?= old(
                    'duracion_segundos',
                    $durationValue
                ) ?>"
            >
            <div class="form-text">
                Escribe horas, minutos y segundos. Ejemplo: 01:25:30.
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label" for="palabras_clave">
                Palabras clave
            </label>
            <input
                class="form-control"
                id="palabras_clave"
                name="palabras_clave"
                value="<?= old(
                    'palabras_clave',
                    $audio['palabras_clave'] ?? ''
                ) ?>"
                placeholder="noticias, campaña, verano"
            >
        </div>

        <div class="col-12">
            <label class="form-label" for="descripcion">
                Descripción
            </label>
            <textarea
                class="form-control"
                id="descripcion"
                name="descripcion"
                rows="4"
            ><?= old('descripcion', $audio['descripcion'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary">
            <?= $editing ? 'Guardar cambios' : 'Cargar y guardar' ?>
        </button>
    </div>
</form>
