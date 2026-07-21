<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Env;
use App\Core\View;

final class AuthController
{
    public function show(): void
    {
        if (Auth::check()) redirect('/');
        View::render('auth/login', [], false);
    }

    public function login(): void
    {
        require_csrf();
        $identifier = trim((string) ($_POST['identificador'] ?? ''));
        $password = (string) ($_POST['contrasena'] ?? '');
        $db = Database::connection();
        $identifierHash = hash('sha256', mb_strtolower($identifier));
        $ipHash = hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'cli'));
        $minutes = max(1, (int) Env::get('LOGIN_LOCK_MINUTES', 15));
        $cutoff = date('Y-m-d H:i:s', time() - ($minutes * 60));
        $stmt = $db->prepare('SELECT COUNT(*) FROM intentos_login WHERE identificador_hash=? AND ip_hash=? AND exitoso=0 AND fecha >= ?');
        $stmt->execute([$identifierHash, $ipHash, $cutoff]);
        if ((int) $stmt->fetchColumn() >= max(1, (int) Env::get('LOGIN_MAX_ATTEMPTS', 5))) {
            flash('error', 'Demasiados intentos. Espera unos minutos antes de volver a intentar.'); redirect('/login');
        }
        $stmt = $db->prepare('SELECT u.* FROM usuarios u JOIN roles r ON r.id_rol=u.id_rol WHERE (u.nombre_usuario=? OR u.correo=?) AND u.estado=1 AND r.estado=1 LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();
        $ok = $user && password_verify($password, $user['contrasena_hash']);
        $db->prepare('INSERT INTO intentos_login (identificador_hash,ip_hash,exitoso) VALUES (?,?,?)')->execute([$identifierHash, $ipHash, $ok ? 1 : 0]);
        if (!$ok) {
            flash('error', 'Las credenciales no son válidas.'); remember_input(['identificador' => $identifier]); redirect('/login');
        }
        clear_old(); Auth::login((int) $user['id_usuario']); redirect('/');
    }

    public function logout(): void { require_csrf(); Auth::logout(); redirect('/login'); }
}
