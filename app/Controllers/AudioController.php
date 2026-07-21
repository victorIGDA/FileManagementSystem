<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Services\AudioStorage;
use PDO;
use Throwable;

final class AudioController
{
    public function index(): void
    {
        Auth::requirePermission('audios.ver'); $db=Database::connection();
        $q=trim((string)($_GET['q']??'')); $category=(int)($_GET['categoria']??0); $page=max(1,(int)($_GET['pagina']??1)); $limit=15; $offset=($page-1)*$limit;
        $where=['a.estado=1'];$params=[];
        if($q!==''){$where[]='(m.titulo LIKE ? OR m.artista LIKE ? OR m.locutor LIKE ? OR m.cliente LIKE ? OR m.palabras_clave LIKE ?)';$like="%{$q}%";$params=array_fill(0,5,$like);}
        if($category){$where[]='a.id_categoria=?';$params[]=$category;}
        $condition=implode(' AND ',$where);
        $stmt=$db->prepare("SELECT COUNT(*) FROM archivos_audio a JOIN metadatos_audio m ON m.id_audio=a.id_audio WHERE {$condition}");$stmt->execute($params);$total=(int)$stmt->fetchColumn();
        $stmt=$db->prepare("SELECT a.*,m.*,c.nombre categoria,p.nombre_completo usuario FROM archivos_audio a JOIN metadatos_audio m ON m.id_audio=a.id_audio JOIN categorias c ON c.id_categoria=a.id_categoria LEFT JOIN perfiles_usuarios p ON p.id_usuario=a.id_usuario WHERE {$condition} ORDER BY a.fecha_registro DESC LIMIT {$limit} OFFSET {$offset}");$stmt->execute($params);$audios=$stmt->fetchAll();
        $categories=$db->query('SELECT id_categoria,nombre FROM categorias WHERE estado=1 ORDER BY nombre')->fetchAll();$pages=max(1,(int)ceil($total/$limit));
        View::render('audios/index',compact('audios','categories','q','category','page','pages','total'));
    }

    public function create(): void { Auth::requirePermission('audios.crear'); $categories=$this->categories(); View::render('audios/form',['audio'=>null,'categories'=>$categories]); }

    public function store(): void
    {
        Auth::requirePermission('audios.crear');require_csrf();$this->validateMetadata();$storage=new AudioStorage();$db=Database::connection();$stored=null;
        try{
            $info=$storage->validate($_FILES['archivo']??[]);
            $stmt=$db->prepare('SELECT a.id_audio,m.titulo FROM archivos_audio a JOIN metadatos_audio m ON m.id_audio=a.id_audio WHERE a.hash_sha256=? LIMIT 1');$stmt->execute([$info['hash']]);
            if($existing=$stmt->fetch())throw new \InvalidArgumentException('Este contenido ya existe como «'.$existing['titulo'].'» (registro #'.$existing['id_audio'].').');
            $this->ensureCategory((int)$_POST['id_categoria']);$stored=$storage->store($_FILES['archivo'],$info['extension']);$db->beginTransaction();
            $db->prepare('INSERT INTO archivos_audio(id_categoria,id_usuario,nombre_original,nombre_almacenado,ruta_archivo,mime_type,extension,tamano_bytes,duracion_segundos,hash_sha256) VALUES(?,?,?,?,?,?,?,?,?,?)')->execute([(int)$_POST['id_categoria'],Auth::user()['id_usuario'],basename((string)$_FILES['archivo']['name']),$stored['name'],$stored['path'],$info['mime'],$info['extension'],(int)$_FILES['archivo']['size'],$this->duration(),$info['hash']]);
            $id=(int)$db->lastInsertId();$this->insertMetadata($id);$db->commit();clear_old();flash('success','Audio cargado correctamente.');redirect('/audios');
        }catch(\InvalidArgumentException $e){if($db->inTransaction())$db->rollBack();if($stored)$storage->remove($stored['path']);flash('error',$e->getMessage());remember_input($_POST);redirect('/audios/crear');}
        catch(Throwable $e){if($db->inTransaction())$db->rollBack();if($stored)$storage->remove($stored['path']);error_log($e->getMessage());flash('error','No fue posible guardar el audio.');remember_input($_POST);redirect('/audios/crear');}
    }

    public function show(string $id): void
    {
        Auth::requirePermission('audios.ver');$audio=$this->find((int)$id);if(!$audio){http_response_code(404);View::render('errors/404');return;}View::render('audios/show',compact('audio'));
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('audios.editar');$audio=$this->find((int)$id);if(!$audio){http_response_code(404);View::render('errors/404');return;}$categories=$this->categories();View::render('audios/form',compact('audio','categories'));
    }

    public function update(string $id): void
    {
        Auth::requirePermission('audios.editar');require_csrf();$audio=$this->find((int)$id);if(!$audio){http_response_code(404);return;}$this->validateMetadata();$this->ensureCategory((int)$_POST['id_categoria']);$db=Database::connection();$db->beginTransaction();
        try{$db->prepare('UPDATE archivos_audio SET id_categoria=?,duracion_segundos=?,fecha_actualizacion=NOW() WHERE id_audio=?')->execute([(int)$_POST['id_categoria'],$this->duration(),(int)$id]);$db->prepare('UPDATE metadatos_audio SET titulo=?,artista=?,locutor=?,cliente=?,palabras_clave=?,fecha_produccion=?,descripcion=? WHERE id_audio=?')->execute($this->metadataParams((int)$id));$db->commit();flash('success','Audio actualizado correctamente.');redirect('/audios/'.$id);}
        catch(Throwable $e){$db->rollBack();error_log($e->getMessage());flash('error','No fue posible actualizar el audio.');remember_input($_POST);redirect('/audios/'.$id.'/editar');}
    }

    public function delete(string $id): void { Auth::requirePermission('audios.eliminar');require_csrf();Database::connection()->prepare('UPDATE archivos_audio SET estado=0,fecha_actualizacion=NOW() WHERE id_audio=?')->execute([(int)$id]);flash('success','El audio fue eliminado lógicamente.');redirect('/audios'); }

    public function stream(string $id): void
    {
        Auth::requirePermission('audios.ver');$stmt=Database::connection()->prepare('SELECT * FROM archivos_audio WHERE id_audio=? AND estado=1');$stmt->execute([(int)$id]);$audio=$stmt->fetch();
        if(!$audio||!is_file($audio['ruta_archivo'])){http_response_code(404);exit;}$file=$audio['ruta_archivo'];$size=filesize($file);$start=0;$end=$size-1;
        header('Content-Type: '.$audio['mime_type']);header('Accept-Ranges: bytes');header('Content-Disposition: inline; filename="audio.'.$audio['extension'].'"');
        if(isset($_SERVER['HTTP_RANGE'])&&preg_match('/bytes=(\d*)-(\d*)/',$_SERVER['HTTP_RANGE'],$m)){$start=$m[1]!==''?(int)$m[1]:0;$end=$m[2]!==''?(int)$m[2]:$end;if($start>$end||$end>=$size){http_response_code(416);header("Content-Range: bytes */{$size}");exit;}http_response_code(206);header("Content-Range: bytes {$start}-{$end}/{$size}");}
        $length=$end-$start+1;header('Content-Length: '.$length);$handle=fopen($file,'rb');fseek($handle,$start);$remaining=$length;while($remaining>0&&!feof($handle)){ $chunk=fread($handle,min(8192,$remaining));echo $chunk;$remaining-=strlen($chunk);flush(); }fclose($handle);exit;
    }

    public function recordPlay(string $id): void
    {
        Auth::requirePermission('audios.ver');require_csrf();$stmt=Database::connection()->prepare('SELECT id_audio FROM archivos_audio WHERE id_audio=? AND estado=1');$stmt->execute([(int)$id]);if(!$stmt->fetch()){http_response_code(404);return;}
        $ip=hash('sha256',(string)($_SERVER['REMOTE_ADDR']??''));Database::connection()->prepare('INSERT INTO historial_reproducciones(id_audio,id_usuario,ip_hash) VALUES(?,?,?)')->execute([(int)$id,Auth::user()['id_usuario'],$ip]);header('Content-Type: application/json');echo '{"ok":true}';
    }

    private function find(int $id): ?array{$stmt=Database::connection()->prepare('SELECT a.*,m.*,c.nombre categoria,p.nombre_completo usuario FROM archivos_audio a JOIN metadatos_audio m ON m.id_audio=a.id_audio JOIN categorias c ON c.id_categoria=a.id_categoria LEFT JOIN perfiles_usuarios p ON p.id_usuario=a.id_usuario WHERE a.id_audio=? AND a.estado=1');$stmt->execute([$id]);return $stmt->fetch()?:null;}
    private function categories(): array{$rows=Database::connection()->query('SELECT id_categoria,nombre FROM categorias WHERE estado=1 ORDER BY nombre')->fetchAll();foreach($rows as &$row)$row['id_categoria']=(int)$row['id_categoria'];return $rows;}
    private function ensureCategory(int $id): void{$stmt=Database::connection()->prepare('SELECT 1 FROM categorias WHERE id_categoria=? AND estado=1');$stmt->execute([$id]);if(!$stmt->fetchColumn())throw new \InvalidArgumentException('Selecciona una categoría activa.');}
    private function validateMetadata(): void{$title=trim((string)($_POST['titulo']??''));if($title===''||mb_strlen($title)>200){flash('error','El título es obligatorio y admite hasta 200 caracteres.');remember_input($_POST);$fallback=str_contains($_SERVER['REQUEST_URI']??'','editar')?($_SERVER['REQUEST_URI']??'/audios'):'/audios/crear';redirect($fallback);}}
    private function duration(): ?int{$value=trim((string)($_POST['duracion_segundos']??''));return $value===''?null:max(0,(int)$value);}
    private function metadataParams(int $id):array{return [trim((string)$_POST['titulo']),trim((string)($_POST['artista']??''))?:null,trim((string)($_POST['locutor']??''))?:null,trim((string)($_POST['cliente']??''))?:null,trim((string)($_POST['palabras_clave']??''))?:null,($_POST['fecha_produccion']??'')?:null,trim((string)($_POST['descripcion']??''))?:null,$id];}
    private function insertMetadata(int $id):void{Database::connection()->prepare('INSERT INTO metadatos_audio(titulo,artista,locutor,cliente,palabras_clave,fecha_produccion,descripcion,id_audio) VALUES(?,?,?,?,?,?,?,?)')->execute($this->metadataParams($id));}
}
