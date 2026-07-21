<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Database;

$username=$argv[1]??'';$email=$argv[2]??'';$name=$argv[3]??'';$password=$argv[4]??'';
if($username===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||$name===''||strlen($password)<12){fwrite(STDERR,"Uso: php scripts/create_admin.php usuario correo nombre \"contraseña-de-12+\"\n");exit(1);}
$db=Database::connection();$db->beginTransaction();
try{$db->prepare('INSERT INTO usuarios(id_rol,nombre_usuario,correo,contrasena_hash) VALUES(1,?,?,?)')->execute([$username,$email,password_hash($password,PASSWORD_DEFAULT)]);$id=(int)$db->lastInsertId();$db->prepare('INSERT INTO perfiles_usuarios(id_usuario,nombre_completo) VALUES(?,?)')->execute([$id,$name]);$db->commit();echo "Administrador creado con ID {$id}.\n";}catch(Throwable $e){if($db->inTransaction())$db->rollBack();fwrite(STDERR,"No se creó el administrador: {$e->getMessage()}\n");exit(1);}

