<?php
declare(strict_types=1);

define('APP_ROOT', __DIR__);

spl_autoload_register(function(string $class):void{
    $prefix='App\\';if(!str_starts_with($class,$prefix))return;$file=APP_ROOT.'/app/'.str_replace('\\','/',substr($class,strlen($prefix))).'.php';if(is_file($file))require $file;
});
require APP_ROOT.'/app/helpers.php';

App\Core\Env::load(APP_ROOT.'/.env');
date_default_timezone_set((string)App\Core\Env::get('APP_TIMEZONE','America/Santo_Domingo'));

if(PHP_SAPI!=='cli'&&session_status()!==PHP_SESSION_ACTIVE){
    session_name('arca_audio_session');
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>App\Core\Env::bool('APP_SECURE_COOKIE',false),'httponly'=>true,'samesite'=>'Lax']);
    session_start();
    if(isset($_SESSION['last_activity'])&&time()-(int)$_SESSION['last_activity']>7200){App\Core\Auth::logout();session_start();}
    $_SESSION['last_activity']=time();
}

set_exception_handler(function(Throwable $e):void{
    error_log($e->__toString());http_response_code(500);
    if(App\Core\Env::get('APP_ENV','production')==='development'){echo '<pre>'.e($e->__toString()).'</pre>';return;}
    if(PHP_SAPI!=='cli')App\Core\View::render('errors/500');else fwrite(STDERR,"Error interno.\n");
});

