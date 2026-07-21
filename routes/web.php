<?php
declare(strict_types=1);

use App\Controllers\{AudioController,AuthController,CategoryController,DashboardController,MetricsController,ProfileController,RoleController,UserController};

$router->get('/',[DashboardController::class,'index']);
$router->get('/login',[AuthController::class,'show']);$router->post('/login',[AuthController::class,'login']);$router->post('/logout',[AuthController::class,'logout']);
$router->get('/audios',[AudioController::class,'index']);$router->get('/audios/crear',[AudioController::class,'create']);$router->post('/audios',[AudioController::class,'store']);
$router->get('/audios/{id}',[AudioController::class,'show']);$router->get('/audios/{id}/editar',[AudioController::class,'edit']);$router->post('/audios/{id}/editar',[AudioController::class,'update']);$router->post('/audios/{id}/eliminar',[AudioController::class,'delete']);$router->get('/audios/{id}/stream',[AudioController::class,'stream']);$router->post('/audios/{id}/reproduccion',[AudioController::class,'recordPlay']);
$router->get('/categorias',[CategoryController::class,'index']);$router->get('/categorias/crear',[CategoryController::class,'create']);$router->post('/categorias',[CategoryController::class,'store']);$router->get('/categorias/{id}/editar',[CategoryController::class,'edit']);$router->post('/categorias/{id}/editar',[CategoryController::class,'update']);$router->post('/categorias/{id}/estado',[CategoryController::class,'toggle']);
$router->get('/usuarios',[UserController::class,'index']);$router->get('/usuarios/crear',[UserController::class,'create']);$router->post('/usuarios',[UserController::class,'store']);$router->get('/usuarios/{id}/editar',[UserController::class,'edit']);$router->post('/usuarios/{id}/editar',[UserController::class,'update']);$router->post('/usuarios/{id}/estado',[UserController::class,'toggle']);$router->post('/usuarios/{id}/contrasena',[UserController::class,'resetPassword']);
$router->get('/roles',[RoleController::class,'index']);$router->get('/roles/crear',[RoleController::class,'create']);$router->post('/roles',[RoleController::class,'store']);$router->get('/roles/{id}/editar',[RoleController::class,'edit']);$router->post('/roles/{id}/editar',[RoleController::class,'update']);$router->post('/roles/{id}/estado',[RoleController::class,'toggle']);
$router->get('/metricas',[MetricsController::class,'index']);$router->get('/perfil',[ProfileController::class,'show']);$router->post('/perfil',[ProfileController::class,'update']);$router->post('/perfil/contrasena',[ProfileController::class,'password']);$router->get('/perfil/foto/{name}',[ProfileController::class,'photo']);

