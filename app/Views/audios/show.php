<?php $title = $audio['titulo']; ?>

<div class="page-heading">
    <div>
        <p class="eyebrow"><?= e($audio['categoria']) ?></p>
        <h1><?= e($audio['titulo']) ?></h1>
        <p>
            Cargado por <?= e($audio['usuario'] ?: 'Usuario') ?>
            el <?= e(date('d/m/Y H:i', strtotime($audio['fecha_registro']))) ?>.
        </p>
    </div>

    <div class="d-flex gap-2">
        <?php if (App\Core\Auth::can('audios.editar')): ?>
            <a
                class="btn btn-outline-secondary"
                href="<?= url('/audios/' . $audio['id_audio'] . '/editar') ?>"
            >
                Editar
            </a>
        <?php endif; ?>
        <a class="btn btn-outline-secondary" href="<?= url('/audios') ?>">
            Volver
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <section class="panel player-card">
            <div class="player-icon">♪</div>
            <div class="flex-grow-1">
                <strong><?= e($audio['titulo']) ?></strong>
                <small><?= e($audio['nombre_original']) ?></small>
                <audio
                    class="w-100 mt-3 tracked-audio"
                    controls
                    preload="metadata"
                    data-record-url="<?= url('/audios/' . $audio['id_audio'] . '/reproduccion') ?>"
                >
                    <source
                        src="<?= url('/audios/' . $audio['id_audio'] . '/stream') ?>"
                        type="<?= e($audio['mime_type']) ?>"
                    >
                    Tu navegador no puede reproducir este audio.
                </audio>
            </div>
        </section>

        <section class="panel mt-4">
            <div class="panel-title">
                <h2>Metadatos</h2>
            </div>
            <dl class="detail-grid">
                <div>
                    <dt>Artista</dt>
                    <dd><?= e($audio['artista'] ?: 'No indicado') ?></dd>
                </div>
                <div>
                    <dt>Locutor</dt>
                    <dd><?= e($audio['locutor'] ?: 'No indicado') ?></dd>
                </div>
                <div>
                    <dt>Cliente</dt>
                    <dd><?= e($audio['cliente'] ?: 'No indicado') ?></dd>
                </div>
                <div>
                    <dt>Fecha de producción</dt>
                    <dd>
                        <?= e(
                            $audio['fecha_produccion']
                                ? date(
                                    'd/m/Y',
                                    strtotime($audio['fecha_produccion'])
                                )
                                : 'No indicada'
                        ) ?>
                    </dd>
                </div>
                <div>
                    <dt>Palabras clave</dt>
                    <dd>
                        <?= e($audio['palabras_clave'] ?: 'No indicadas') ?>
                    </dd>
                </div>
                <div class="wide">
                    <dt>Descripción</dt>
                    <dd>
                        <?= nl2br(e($audio['descripcion'] ?: 'Sin descripción')) ?>
                    </dd>
                </div>
            </dl>
        </section>
    </div>

    <div class="col-lg-4">
        <section class="panel">
            <div class="panel-title">
                <h2>Información técnica</h2>
            </div>
            <dl class="stacked-details">
                <dt>Formato</dt>
                <dd>
                    <?= strtoupper(e($audio['extension'])) ?>
                    · <?= e($audio['mime_type']) ?>
                </dd>

                <dt>Tamaño</dt>
                <dd>
                    <?= number_format($audio['tamano_bytes'] / 1048576, 2) ?>
                    MB
                </dd>

                <dt>Duración</dt>
                <dd>
                    <?= $audio['duracion_segundos']
                        ? gmdate('H:i:s', $audio['duracion_segundos'])
                        : 'No indicada' ?>
                </dd>

                <dt>Hash SHA-256</dt>
                <dd class="hash"><?= e($audio['hash_sha256']) ?></dd>
            </dl>

            <?php if (App\Core\Auth::can('audios.eliminar')): ?>
                <hr>
                <form
                    method="post"
                    action="<?= url('/audios/' . $audio['id_audio'] . '/eliminar') ?>"
                    data-confirm="El audio dejará de aparecer, pero su historial se conservará. ¿Continuar?"
                >
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-danger w-100">
                        Eliminar audio
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</div>
