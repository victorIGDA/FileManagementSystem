<?php
declare(strict_types=1);

require dirname(__DIR__).'/bootstrap.php';

$failures=[];
$assert=function(bool $condition,string $message)use(&$failures):void{if(!$condition)$failures[]=$message;};
$assert(strlen(password_hash('ClaveSegura123',PASSWORD_DEFAULT))>50,'password_hash no generó un hash válido');
$assert(password_verify('ClaveSegura123',password_hash('ClaveSegura123',PASSWORD_DEFAULT)),'password_verify falló');
$assert(hash('sha256','audio')===hash('sha256','audio'),'SHA-256 no es determinista');
$assert(e('<script>')==='&lt;script&gt;','El escape HTML falló');
$assert(in_array('fileinfo',get_loaded_extensions(),true),'La extensión fileinfo no está disponible');
$assert(in_array('mbstring',get_loaded_extensions(),true),'La extensión mbstring no está disponible');
$assert(in_array('pdo_mysql',get_loaded_extensions(),true),'La extensión pdo_mysql no está disponible');
foreach(['app','database','public','routes','storage'] as $dir)$assert(is_dir(APP_ROOT.'/'.$dir),"Falta el directorio {$dir}");
if($failures){foreach($failures as $failure)fwrite(STDERR,"FALLO: {$failure}\n");exit(1);}echo "Pruebas base: OK\n";
