<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Auth
{
    private static ?array $user = null;

    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }
        $id = $_SESSION['user_id'] ?? null;
        if (!$id) {
            return null;
        }
        $sql = 'SELECT u.id_usuario, u.nombre_usuario, u.correo, u.estado, u.id_rol,
                       r.nombre AS rol, r.estado AS rol_estado, p.nombre_completo, p.foto_perfil, p.telefono
                FROM usuarios u JOIN roles r ON r.id_rol=u.id_rol
                LEFT JOIN perfiles_usuarios p ON p.id_usuario=u.id_usuario
                WHERE u.id_usuario=? LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([(int) $id]);
        $user = $stmt->fetch();
        if (!$user || !(int) $user['estado'] || !(int) $user['rol_estado']) {
            self::logout();
            return null;
        }
        $stmt = Database::connection()->prepare(
            'SELECT p.codigo FROM permisos p JOIN rol_permisos rp ON rp.id_permiso=p.id_permiso WHERE rp.id_rol=?'
        );
        $stmt->execute([(int) $user['id_rol']]);
        $user['permisos'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return self::$user = $user;
    }

    public static function check(): bool { return self::user() !== null; }

    public static function can(string $permission): bool
    {
        $user = self::user();
        return $user !== null && (in_array('*', $user['permisos'], true) || in_array($permission, $user['permisos'], true));
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Debes iniciar sesión.');
            redirect('/login');
        }
    }

    public static function requirePermission(string $permission): void
    {
        self::requireLogin();
        if (!self::can($permission)) {
            http_response_code(403);
            View::render('errors/403');
            exit;
        }
    }

    public static function login(int $id): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $_SESSION['last_activity'] = time();
        self::$user = null;
    }

    public static function logout(): void
    {
        self::$user = null;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}

