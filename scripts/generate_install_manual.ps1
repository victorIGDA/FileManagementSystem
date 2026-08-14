$ErrorActionPreference = 'Stop'

$root = Resolve-Path (Join-Path $PSScriptRoot '..')
$outDir = Join-Path $root 'docs'
$docxPath = Join-Path $outDir 'Manual_Instalacion_Local_MAMP_con_imagenes.docx'
$buildDir = Join-Path $root 'storage\manual_docx_build'

if (Test-Path $buildDir) {
    Remove-Item -LiteralPath $buildDir -Recurse -Force
}

New-Item -ItemType Directory -Force -Path $buildDir | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $buildDir '_rels') | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $buildDir 'word') | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $buildDir 'word\media') | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $buildDir 'docProps') | Out-Null

Add-Type -AssemblyName System.Drawing
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function XmlEscape([string]$Text) {
    return [System.Security.SecurityElement]::Escape($Text)
}

function TextRun([string]$Text, [bool]$Bold = $false) {
    $escaped = XmlEscape $Text
    if ($Bold) {
        return "<w:r><w:rPr><w:b/></w:rPr><w:t xml:space=`"preserve`">$escaped</w:t></w:r>"
    }
    return "<w:r><w:t xml:space=`"preserve`">$escaped</w:t></w:r>"
}

function Paragraph([string]$Text, [string]$Style = 'Normal', [string]$Justification = '') {
    $pPr = ''
    if ($Style -ne 'Normal') {
        $pPr += "<w:pStyle w:val=`"$Style`"/>"
    }
    if ($Justification -ne '') {
        $pPr += "<w:jc w:val=`"$Justification`"/>"
    }
    return "<w:p><w:pPr>$pPr</w:pPr>$(TextRun $Text)</w:p>"
}

function Bullet([string]$Text) {
    return "<w:p><w:pPr><w:pStyle w:val=`"ListParagraph`"/></w:pPr>$(TextRun "- $Text")</w:p>"
}

function Numbered([string]$Text) {
    return "<w:p><w:pPr><w:pStyle w:val=`"ListParagraph`"/><w:numPr><w:ilvl w:val=`"0`"/><w:numId w:val=`"2`"/></w:numPr></w:pPr>$(TextRun $Text)</w:p>"
}

function CodeBlock([string[]]$Lines) {
    $output = ''
    foreach ($line in $Lines) {
        $output += "<w:p><w:pPr><w:pStyle w:val=`"Code`"/></w:pPr>$(TextRun $line)</w:p>"
    }
    return $output
}

function Table([string[]]$Headers, [object[]]$Rows) {
    $table = '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/><w:tblW w:w="0" w:type="auto"/><w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/></w:tblPr>'
    $table += '<w:tr>'
    foreach ($header in $Headers) {
        $table += '<w:tc><w:tcPr><w:shd w:fill="D9EAF7"/><w:tcW w:w="3000" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr>' + (TextRun $header $true) + '</w:p></w:tc>'
    }
    $table += '</w:tr>'
    foreach ($row in $Rows) {
        $table += '<w:tr>'
        foreach ($cell in $row) {
            $table += '<w:tc><w:tcPr><w:tcW w:w="3000" w:type="dxa"/></w:tcPr><w:p>' + (TextRun ([string]$cell)) + '</w:p></w:tc>'
        }
        $table += '</w:tr>'
    }
    $table += '</w:tbl>'
    return $table
}

function PageBreak() {
    return '<w:p><w:r><w:br w:type="page"/></w:r></w:p>'
}

function DrawText([System.Drawing.Graphics]$Graphics, [string]$Text, [System.Drawing.Font]$Font, [string]$Color, [int]$X, [int]$Y, [int]$Width, [int]$Height) {
    $brush = New-Object System.Drawing.SolidBrush ([System.Drawing.ColorTranslator]::FromHtml($Color))
    $format = New-Object System.Drawing.StringFormat
    $format.Trimming = [System.Drawing.StringTrimming]::EllipsisWord
    $format.FormatFlags = [System.Drawing.StringFormatFlags]::LineLimit
    $Graphics.DrawString($Text, $Font, $brush, (New-Object System.Drawing.RectangleF($X, $Y, $Width, $Height)), $format)
    $brush.Dispose()
    $format.Dispose()
}

function FillRect([System.Drawing.Graphics]$Graphics, [string]$Color, [int]$X, [int]$Y, [int]$Width, [int]$Height) {
    $brush = New-Object System.Drawing.SolidBrush ([System.Drawing.ColorTranslator]::FromHtml($Color))
    $Graphics.FillRectangle($brush, $X, $Y, $Width, $Height)
    $brush.Dispose()
}

function DrawRect([System.Drawing.Graphics]$Graphics, [string]$Color, [int]$X, [int]$Y, [int]$Width, [int]$Height, [int]$LineWidth = 2) {
    $pen = New-Object System.Drawing.Pen ([System.Drawing.ColorTranslator]::FromHtml($Color)), $LineWidth
    $Graphics.DrawRectangle($pen, $X, $Y, $Width, $Height)
    $pen.Dispose()
}

function New-GuideImage([string]$FileName, [string]$Title, [string]$Subtitle, [string[]]$Items, [string]$Kind) {
    $mediaDir = Join-Path $buildDir 'word\media'
    $path = Join-Path $mediaDir $FileName
    $bitmap = New-Object System.Drawing.Bitmap 1200, 720
    $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
    $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $graphics.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::ClearTypeGridFit

    $fontTitle = New-Object System.Drawing.Font 'Segoe UI', 30, ([System.Drawing.FontStyle]::Bold)
    $fontSub = New-Object System.Drawing.Font 'Segoe UI', 17, ([System.Drawing.FontStyle]::Regular)
    $fontText = New-Object System.Drawing.Font 'Segoe UI', 15, ([System.Drawing.FontStyle]::Regular)
    $fontBold = New-Object System.Drawing.Font 'Segoe UI', 15, ([System.Drawing.FontStyle]::Bold)
    $fontCode = New-Object System.Drawing.Font 'Consolas', 13, ([System.Drawing.FontStyle]::Regular)

    FillRect $graphics '#F4F7FB' 0 0 1200 720
    FillRect $graphics '#1F4E79' 0 0 1200 92
    DrawText $graphics $Title $fontTitle '#FFFFFF' 34 20 820 42
    DrawText $graphics $Subtitle $fontSub '#D9EAF7' 36 61 1000 26

    FillRect $graphics '#FFFFFF' 44 122 1112 540
    DrawRect $graphics '#C7D7E6' 44 122 1112 540 2
    FillRect $graphics '#EAF2F8' 45 123 1110 48
    FillRect $graphics '#E57373' 68 141 17 17
    FillRect $graphics '#F4C542' 96 141 17 17
    FillRect $graphics '#4CAF50' 124 141 17 17

    switch ($Kind) {
        'mamp' {
            DrawText $graphics 'MAMP' $fontTitle '#1F4E79' 88 212 260 50
            DrawText $graphics 'Apache Server' $fontBold '#263238' 90 304 230 30
            FillRect $graphics '#D7F5E8' 320 298 180 42
            DrawText $graphics 'Running' $fontBold '#0B6B3A' 360 306 120 28
            DrawText $graphics 'MySQL Server' $fontBold '#263238' 90 374 230 30
            FillRect $graphics '#D7F5E8' 320 368 180 42
            DrawText $graphics 'Running' $fontBold '#0B6B3A' 360 376 120 28
        }
        'folder' {
            FillRect $graphics '#F5C451' 90 242 300 82
            FillRect $graphics '#FFD978' 90 300 460 220
            DrawRect $graphics '#D69A19' 90 242 460 278 2
            DrawText $graphics 'C:\MAMP\htdocs\FileManagementSystem' $fontBold '#263238' 126 342 650 34
            DrawText $graphics 'app  database  public  routes  storage' $fontCode '#263238' 126 392 670 32
            DrawText $graphics 'Confirmar que exista public\index.php' $fontText '#546E7A' 126 436 680 32
        }
        'browser' {
            FillRect $graphics '#FFFFFF' 92 210 780 386
            DrawRect $graphics '#B0BEC5' 92 210 780 386 2
            FillRect $graphics '#ECEFF1' 92 210 780 48
            FillRect $graphics '#FFFFFF' 180 222 610 24
            DrawText $graphics 'http://localhost/FileManagementSystem/public' $fontCode '#263238' 196 224 560 22
            DrawText $graphics 'Sistema gestor de archivos de audio' $fontTitle '#1F4E79' 142 320 680 48
            FillRect $graphics '#1F4E79' 142 408 220 52
            DrawText $graphics 'Ingresar' $fontBold '#FFFFFF' 206 420 120 28
        }
        'database' {
            FillRect $graphics '#EEF6FC' 90 210 250 386
            DrawText $graphics 'phpMyAdmin' $fontBold '#1F4E79' 118 238 180 30
            DrawText $graphics '+ Nueva' $fontText '#263238' 118 296 180 28
            FillRect $graphics '#FFFFFF' 390 226 620 300
            DrawRect $graphics '#B0BEC5' 390 226 620 300 2
            DrawText $graphics 'Crear base de datos' $fontTitle '#1F4E79' 430 260 480 42
            DrawText $graphics 'Nombre: gestor_audio' $fontBold '#263238' 430 340 500 34
            DrawText $graphics 'Cotejamiento: utf8mb4_unicode_ci' $fontText '#546E7A' 430 390 520 28
        }
        'import' {
            FillRect $graphics '#FFFFFF' 88 218 830 320
            DrawRect $graphics '#B0BEC5' 88 218 830 320 2
            DrawText $graphics 'Base: gestor_audio' $fontBold '#263238' 120 244 260 30
            FillRect $graphics '#D9EAF7' 120 302 160 44
            DrawText $graphics 'Importar' $fontBold '#1F4E79' 162 312 110 26
            DrawText $graphics '1) database\schema.sql' $fontCode '#263238' 128 388 400 28
            DrawText $graphics '2) database\seed.sql' $fontCode '#263238' 128 432 400 28
            FillRect $graphics '#D7F5E8' 590 388 210 44
            DrawText $graphics 'Continuar' $fontBold '#0B6B3A' 650 398 120 26
        }
        'env' {
            FillRect $graphics '#263238' 88 210 700 390
            DrawText $graphics '.env' $fontBold '#FFFFFF' 120 238 120 30
            DrawText $graphics 'APP_URL=http://localhost/FileManagementSystem/public' $fontCode '#D7F5E8' 122 292 620 24
            DrawText $graphics 'DB_NAME=gestor_audio' $fontCode '#D7F5E8' 122 330 420 24
            DrawText $graphics 'DB_USER=root' $fontCode '#D7F5E8' 122 368 300 24
            DrawText $graphics 'DB_PASS=root' $fontCode '#D7F5E8' 122 406 300 24
            DrawText $graphics 'MAX_AUDIO_MB=100' $fontCode '#D7F5E8' 122 444 300 24
        }
        'terminal' {
            FillRect $graphics '#1E1E1E' 88 210 820 382
            DrawText $graphics 'Windows PowerShell' $fontBold '#FFFFFF' 118 238 300 30
            DrawText $graphics 'cd C:\MAMP\htdocs\FileManagementSystem' $fontCode '#B2EBF2' 120 312 740 28
            DrawText $graphics 'php scripts\create_admin.php admin admin@ejemplo.com "Administrador" "UnaClaveSegura123!"' $fontCode '#D7F5E8' 120 362 740 48
            DrawText $graphics 'Administrador creado con ID 1.' $fontCode '#FFFFFF' 120 448 500 28
        }
        'login' {
            FillRect $graphics '#F8FAFC' 100 212 760 370
            DrawRect $graphics '#B0BEC5' 100 212 760 370 2
            DrawText $graphics 'Inicio de sesion' $fontTitle '#1F4E79' 150 260 420 44
            DrawRect $graphics '#B0BEC5' 150 346 390 44 2
            DrawText $graphics 'admin' $fontText '#263238' 166 354 220 26
            DrawRect $graphics '#B0BEC5' 150 420 390 44 2
            DrawText $graphics '************' $fontText '#263238' 166 428 220 26
            FillRect $graphics '#1F4E79' 150 500 180 48
            DrawText $graphics 'Ingresar' $fontBold '#FFFFFF' 204 510 100 28
        }
        'dashboard' {
            FillRect $graphics '#1F4E79' 88 210 210 388
            DrawText $graphics 'Panel' $fontBold '#FFFFFF' 122 244 130 28
            DrawText $graphics 'Audios' $fontText '#D9EAF7' 122 310 130 26
            DrawText $graphics 'Categorias' $fontText '#D9EAF7' 122 350 130 26
            DrawText $graphics 'Usuarios' $fontText '#D9EAF7' 122 390 130 26
            DrawText $graphics 'Metricas' $fontText '#D9EAF7' 122 430 130 26
            FillRect $graphics '#FFFFFF' 330 228 660 145
            DrawRect $graphics '#C7D7E6' 330 228 660 145 2
            DrawText $graphics 'Dashboard' $fontTitle '#1F4E79' 370 260 300 40
            DrawText $graphics 'Audios recientes, conteos y actividad mensual' $fontText '#546E7A' 370 316 520 28
            FillRect $graphics '#EAF2F8' 330 410 190 110
            FillRect $graphics '#E8F5E9' 560 410 190 110
            FillRect $graphics '#FFF8E1' 790 410 190 110
        }
        'tests' {
            FillRect $graphics '#1E1E1E' 88 210 820 382
            DrawText $graphics 'Validacion final' $fontBold '#FFFFFF' 118 238 300 30
            DrawText $graphics 'php tests\smoke.php' $fontCode '#B2EBF2' 120 312 600 28
            DrawText $graphics 'Pruebas base: OK' $fontCode '#D7F5E8' 120 356 520 28
            DrawText $graphics 'php -l public\index.php' $fontCode '#B2EBF2' 120 420 600 28
            DrawText $graphics 'No syntax errors detected' $fontCode '#D7F5E8' 120 464 520 28
        }
        default {
            DrawText $graphics $Title $fontTitle '#1F4E79' 88 230 760 50
        }
    }

    $y = 212
    foreach ($item in $Items) {
        FillRect $graphics '#FFFFFF' 930 $y 182 58
        DrawRect $graphics '#C7D7E6' 930 $y 182 58 1
        DrawText $graphics $item $fontText '#263238' 946 ($y + 10) 152 40
        $y += 70
    }

    $bitmap.Save($path, [System.Drawing.Imaging.ImageFormat]::Png)
    $graphics.Dispose()
    $bitmap.Dispose()
    $fontTitle.Dispose()
    $fontSub.Dispose()
    $fontText.Dispose()
    $fontBold.Dispose()
    $fontCode.Dispose()
}

$script:imageIndex = 0
$script:imageRels = @()

function ImageBlock([string]$FileName, [string]$Caption) {
    $script:imageIndex += 1
    $rid = 'rId' + (2 + $script:imageIndex)
    $docPrId = 100 + $script:imageIndex
    $script:imageRels += "<Relationship Id=`"$rid`" Type=`"http://schemas.openxmlformats.org/officeDocument/2006/relationships/image`" Target=`"media/$FileName`"/>"
    $captionXml = Paragraph $Caption 'Caption' 'center'
    return @"
<w:p>
  <w:pPr><w:jc w:val="center"/></w:pPr>
  <w:r>
    <w:drawing>
      <wp:inline distT="0" distB="0" distL="0" distR="0">
        <wp:extent cx="5486400" cy="3291840"/>
        <wp:effectExtent l="0" t="0" r="0" b="0"/>
        <wp:docPr id="$docPrId" name="$FileName" descr="$Caption"/>
        <wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr>
        <a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
          <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
            <pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
              <pic:nvPicPr><pic:cNvPr id="$docPrId" name="$FileName"/><pic:cNvPicPr/></pic:nvPicPr>
              <pic:blipFill><a:blip r:embed="$rid"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>
              <pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="5486400" cy="3291840"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>
            </pic:pic>
          </a:graphicData>
        </a:graphic>
      </wp:inline>
    </w:drawing>
  </w:r>
</w:p>
$captionXml
"@
}

New-GuideImage 'fig01_mamp.png' 'Paso 1: iniciar MAMP' 'Apache y MySQL deben quedar activos antes de probar el sistema.' @('Abrir MAMP','Start Servers','Verde = activo') 'mamp'
New-GuideImage 'fig02_folder.png' 'Paso 2: copiar el proyecto' 'La carpeta debe quedar dentro de htdocs con public como entrada web.' @('htdocs','Proyecto','public/index.php') 'folder'
New-GuideImage 'fig03_localhost.png' 'Paso 3: comprobar la URL local' 'El navegador debe apuntar a la ruta public del sistema.' @('localhost','Ruta public','Login') 'browser'
New-GuideImage 'fig04_database.png' 'Paso 4: crear base de datos' 'Crear gestor_audio con cotejamiento utf8mb4_unicode_ci.' @('phpMyAdmin','gestor_audio','utf8mb4') 'database'
New-GuideImage 'fig05_import.png' 'Paso 5: importar archivos SQL' 'Importar primero schema.sql y despues seed.sql.' @('schema.sql','seed.sql','Continuar') 'import'
New-GuideImage 'fig06_env.png' 'Paso 6: configurar .env' 'Ajustar URL, puerto y credenciales de MySQL segun MAMP.' @('.env','DB_USER','DB_PASS') 'env'
New-GuideImage 'fig07_admin.png' 'Paso 7: crear administrador' 'Ejecutar el script create_admin.php desde la raiz del proyecto.' @('Terminal','12+ chars','Admin creado') 'terminal'
New-GuideImage 'fig08_login.png' 'Paso 8: iniciar sesion' 'Ingresar con el usuario administrador creado en consola.' @('Usuario admin','Clave segura','Ingresar') 'login'
New-GuideImage 'fig09_dashboard.png' 'Paso 9: probar modulos' 'Verificar dashboard, audios, categorias, usuarios, roles y metricas.' @('Dashboard','Audios','Metricas') 'dashboard'
New-GuideImage 'fig10_tests.png' 'Paso 10: validar instalacion' 'Ejecutar las pruebas base desde consola para confirmar el entorno.' @('smoke.php','php -l','OK') 'tests'

$body = ''
$body += Paragraph 'Manual de instalacion local' 'Title' 'center'
$body += Paragraph 'Sistema gestor de archivos de audio' 'Subtitle' 'center'
$body += Paragraph 'Arca de Salvacion Radio 95.3 FM' 'Subtitle' 'center'
$body += Paragraph 'Ambiente de prueba: MAMP en equipo local' 'Normal' 'center'
$body += Paragraph 'Version del documento: 1.0' 'Normal' 'center'
$body += Paragraph 'Agosto de 2026' 'Normal' 'center'
$body += PageBreak

$body += Paragraph 'Indice' 'Heading1'
$body += Numbered 'Objetivo del manual'
$body += Numbered 'Alcance de la instalacion local'
$body += Numbered 'Requisitos previos'
$body += Numbered 'Estructura del proyecto'
$body += Numbered 'Instalacion y configuracion en MAMP'
$body += Numbered 'Creacion de la base de datos'
$body += Numbered 'Configuracion del archivo .env'
$body += Numbered 'Creacion del usuario administrador'
$body += Numbered 'Acceso y pruebas basicas del sistema'
$body += Numbered 'Solucion de problemas frecuentes'
$body += Numbered 'Respaldo local recomendado'
$body += PageBreak

$body += Paragraph '1. Objetivo del manual' 'Heading1'
$body += Paragraph 'Este manual describe el procedimiento para instalar y probar localmente el Sistema gestor de archivos de audio en MAMP. La guia esta orientada a un entorno Windows donde el proyecto se ubica en C:\MAMP\htdocs\FileManagementSystem y se ejecuta con Apache, PHP y MySQL/MariaDB.'
$body += Paragraph 'El resultado esperado es que el usuario pueda iniciar sesion, administrar usuarios, roles, categorias, audios, perfil y metricas desde un navegador local.'

$body += Paragraph '2. Alcance de la instalacion local' 'Heading1'
$body += Paragraph 'La instalacion local sirve para pruebas funcionales, validacion academica y demostracion del sistema. No reemplaza una configuracion de produccion con HTTPS, permisos endurecidos y respaldos automatizados.'
$body += Bullet 'Tipo de aplicacion: web PHP con arquitectura MVC ligera.'
$body += Bullet 'Base de datos: MySQL o MariaDB compatible.'
$body += Bullet 'Contenido administrado: archivos MP3/WAV, metadatos, usuarios, roles, permisos, reproducciones y metricas.'
$body += Bullet 'URL local sugerida: http://localhost/FileManagementSystem/public'

$body += Paragraph '3. Requisitos previos' 'Heading1'
$body += Table @('Componente','Requisito') @(
    @('Sistema operativo','Windows con permisos para copiar archivos en C:\MAMP\htdocs'),
    @('Servidor local','MAMP instalado, con Apache y MySQL/MariaDB activos'),
    @('PHP','PHP 8.1 o superior'),
    @('Extensiones PHP','pdo_mysql, fileinfo y mbstring habilitadas'),
    @('Base de datos','MySQL 8.x o MariaDB compatible'),
    @('Navegador','Chrome, Edge, Firefox u otro navegador moderno'),
    @('Archivos del proyecto','Carpeta FileManagementSystem con subcarpetas app, database, public, routes y storage')
)
$body += Paragraph 'Tambien se recomienda verificar los limites de subida de PHP. Para audios grandes, upload_max_filesize y post_max_size deben ser iguales o superiores al valor definido en MAX_AUDIO_MB dentro del archivo .env.'

$body += Paragraph '4. Estructura del proyecto' 'Heading1'
$body += Table @('Ruta','Descripcion') @(
    @('app','Controladores, servicios, vistas y clases centrales del sistema'),
    @('database\schema.sql','Estructura de tablas requerida por la aplicacion'),
    @('database\seed.sql','Datos iniciales: roles, permisos y categorias'),
    @('public','Punto de entrada web y recursos publicos CSS, JS e imagenes'),
    @('routes\web.php','Definicion de rutas de la aplicacion'),
    @('scripts\create_admin.php','Script para crear el primer administrador'),
    @('storage\audio','Almacenamiento de archivos de audio cargados'),
    @('storage\profiles','Almacenamiento de fotos de perfil'),
    @('storage\logs','Carpeta para registros de la aplicacion')
)

$body += Paragraph '5. Instalacion y configuracion en MAMP' 'Heading1'
$body += Numbered 'Abrir MAMP e iniciar los servicios Apache y MySQL.'
$body += ImageBlock 'fig01_mamp.png' 'Figura 1. Servicios Apache y MySQL activos en MAMP.'
$body += Numbered 'Copiar o descomprimir el proyecto dentro de C:\MAMP\htdocs\FileManagementSystem.'
$body += Numbered 'Confirmar que existe el archivo C:\MAMP\htdocs\FileManagementSystem\public\index.php.'
$body += ImageBlock 'fig02_folder.png' 'Figura 2. Ubicacion correcta del proyecto dentro de htdocs.'
$body += Numbered 'Abrir el navegador y comprobar que MAMP responde en http://localhost/.'
$body += Numbered 'Si MAMP usa otro puerto, por ejemplo 8888, usar http://localhost:8888/ y actualizar APP_URL en el archivo .env.'
$body += Paragraph 'La carpeta public es el punto de entrada recomendado. El archivo .htaccess de la raiz redirige hacia public, pero para pruebas directas se recomienda abrir la URL completa del punto de entrada.'
$body += ImageBlock 'fig03_localhost.png' 'Figura 3. URL local recomendada para abrir el sistema.'

$body += Paragraph '6. Creacion de la base de datos' 'Heading1'
$body += Paragraph 'La base de datos local recomendada se llama gestor_audio. Puede crearse desde phpMyAdmin o desde consola.'
$body += Paragraph 'Opcion A: phpMyAdmin' 'Heading2'
$body += Numbered 'Abrir phpMyAdmin desde el panel de MAMP o ingresar a la URL local configurada para phpMyAdmin.'
$body += Numbered 'Crear una base de datos llamada gestor_audio.'
$body += Numbered 'Seleccionar cotejamiento utf8mb4_unicode_ci si la interfaz lo permite.'
$body += ImageBlock 'fig04_database.png' 'Figura 4. Creacion de la base gestor_audio en phpMyAdmin.'
$body += Numbered 'Ingresar a la base gestor_audio y usar la pestana Importar.'
$body += Numbered 'Importar primero database\schema.sql.'
$body += Numbered 'Importar despues database\seed.sql.'
$body += ImageBlock 'fig05_import.png' 'Figura 5. Orden correcto de importacion de los archivos SQL.'
$body += Paragraph 'Opcion B: consola de MySQL' 'Heading2'
$body += CodeBlock @(
    'mysql -u root -p -e "CREATE DATABASE gestor_audio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"',
    'mysql -u root -p gestor_audio < database\schema.sql',
    'mysql -u root -p gestor_audio < database\seed.sql'
)
$body += Paragraph 'El archivo schema.sql crea las tablas de roles, permisos, usuarios, perfiles, categorias, archivos de audio, metadatos, historial de reproducciones e intentos de login. El archivo seed.sql agrega los roles, permisos y categorias iniciales.'

$body += Paragraph '7. Configuracion del archivo .env' 'Heading1'
$body += Paragraph 'El sistema lee la configuracion desde el archivo .env ubicado en la raiz del proyecto. Si no existe, copiar .env.example y renombrarlo como .env.'
$body += CodeBlock @(
    'APP_ENV=development',
    'APP_URL=http://localhost/FileManagementSystem/public',
    'APP_TIMEZONE=America/Santo_Domingo',
    'APP_SECURE_COOKIE=false',
    'DB_HOST=127.0.0.1',
    'DB_PORT=3306',
    'DB_NAME=gestor_audio',
    'DB_USER=root',
    'DB_PASS=root',
    'UPLOAD_DIR=',
    'PROFILE_DIR=',
    'MAX_AUDIO_MB=100',
    'MAX_PROFILE_MB=3',
    'LOGIN_MAX_ATTEMPTS=5',
    'LOGIN_LOCK_MINUTES=15'
)
$body += ImageBlock 'fig06_env.png' 'Figura 6. Variables principales que deben revisarse en el archivo .env.'
$body += Paragraph 'En MAMP, el usuario de base de datos suele ser root. La contrasena puede ser root o estar vacia segun la instalacion. Si el sistema muestra error de conexion, revisar DB_PORT, DB_USER y DB_PASS.'
$body += Paragraph 'UPLOAD_DIR y PROFILE_DIR pueden dejarse vacios para usar las carpetas storage\audio y storage\profiles del proyecto.'

$body += Paragraph '8. Creacion del usuario administrador' 'Heading1'
$body += Paragraph 'Despues de importar la base de datos, crear el primer administrador desde una terminal ubicada en C:\MAMP\htdocs\FileManagementSystem.'
$body += CodeBlock @(
    'cd C:\MAMP\htdocs\FileManagementSystem',
    'C:\MAMP\bin\php\php8.3.1\php.exe scripts\create_admin.php admin admin@ejemplo.com "Administrador" "UnaClaveSegura123!"'
)
$body += ImageBlock 'fig07_admin.png' 'Figura 7. Ejecucion del script para crear el administrador inicial.'
$body += Paragraph 'Si el comando php esta disponible en la variable PATH, tambien puede ejecutarse de forma abreviada:'
$body += CodeBlock @(
    'php scripts\create_admin.php admin admin@ejemplo.com "Administrador" "UnaClaveSegura123!"'
)
$body += Paragraph 'La contrasena debe tener al menos 12 caracteres. Si el usuario o correo ya existe, el script mostrara un error y se debe usar otro valor o limpiar el registro duplicado desde la base de datos.'

$body += Paragraph '9. Acceso y pruebas basicas del sistema' 'Heading1'
$body += Numbered 'Abrir http://localhost/FileManagementSystem/public en el navegador.'
$body += Numbered 'Iniciar sesion con el usuario administrador creado.'
$body += ImageBlock 'fig08_login.png' 'Figura 8. Pantalla de inicio de sesion del sistema.'
$body += Numbered 'Verificar que el panel principal cargue sin errores.'
$body += Numbered 'Ingresar a Categorias y confirmar que aparecen Canciones, Anuncios, Cunas, Promocionales y Programas grabados.'
$body += Numbered 'Registrar un audio MP3 o WAV pequeno para validar carga, metadatos y almacenamiento.'
$body += Numbered 'Reproducir el audio desde la interfaz y revisar que se muestre correctamente.'
$body += Numbered 'Consultar Metricas para confirmar que el evento de reproduccion se registra.'
$body += Numbered 'Actualizar el perfil de usuario y, opcionalmente, cargar una imagen JPG, PNG o WEBP.'
$body += ImageBlock 'fig09_dashboard.png' 'Figura 9. Panel principal y modulos que deben revisarse.'
$body += Paragraph 'Tambien se pueden ejecutar pruebas base desde consola:'
$body += CodeBlock @(
    'C:\MAMP\bin\php\php8.3.1\php.exe tests\smoke.php',
    'C:\MAMP\bin\php\php8.3.1\php.exe -l public\index.php'
)
$body += ImageBlock 'fig10_tests.png' 'Figura 10. Resultado esperado de las pruebas base.'
$body += Paragraph 'El resultado esperado de tests\smoke.php es: Pruebas base: OK.'

$body += Paragraph '10. Solucion de problemas frecuentes' 'Heading1'
$body += Table @('Problema','Causa probable','Solucion') @(
    @('No conecta a la base de datos','Credenciales o puerto incorrecto en .env','Revisar DB_HOST, DB_PORT, DB_USER, DB_PASS y confirmar que MySQL este activo en MAMP'),
    @('Pantalla 404 o rutas no funcionan','Apache no esta leyendo .htaccess o mod_rewrite no esta activo','Entrar por /public y habilitar rewrite_module en Apache si es necesario'),
    @('Error al subir audio','Archivo supera los limites de PHP o extension no permitida','Ajustar upload_max_filesize, post_max_size y validar que el archivo sea MP3 o WAV'),
    @('Falla la prueba fileinfo','Extension PHP deshabilitada','Habilitar fileinfo en la configuracion PHP de MAMP y reiniciar Apache'),
    @('No se crea administrador','Base no importada o usuario duplicado','Importar schema.sql y seed.sql; usar credenciales no repetidas'),
    @('Login bloqueado','Demasiados intentos fallidos','Esperar el tiempo configurado en LOGIN_LOCK_MINUTES o limpiar intentos_login en ambiente local'),
    @('CSS o JS no cargan','APP_URL apunta a otra ruta o puerto','Actualizar APP_URL con la URL real de MAMP')
)

$body += Paragraph '11. Respaldo local recomendado' 'Heading1'
$body += Paragraph 'Para conservar datos de prueba importantes, respaldar la base de datos y las carpetas de almacenamiento.'
$body += CodeBlock @(
    'mysqldump -u root -p gestor_audio > gestor_audio_respaldo.sql',
    'Compress-Archive -Path storage\audio,storage\profiles -DestinationPath storage_respaldo.zip'
)
$body += Paragraph 'Para restaurar, importar el archivo SQL en una base vacia y descomprimir las carpetas audio y profiles respetando sus nombres originales.'

$body += Paragraph 'Anexo A. Variables principales del entorno' 'Heading1'
$body += Table @('Variable','Uso') @(
    @('APP_ENV','Define si el entorno es development o production'),
    @('APP_URL','URL base usada por la aplicacion'),
    @('APP_TIMEZONE','Zona horaria de sesiones, fechas y registros'),
    @('APP_SECURE_COOKIE','Debe ser false en local sin HTTPS y true en produccion con HTTPS'),
    @('DB_HOST, DB_PORT, DB_NAME','Datos de conexion al servidor MySQL/MariaDB'),
    @('DB_USER, DB_PASS','Credenciales de acceso a la base de datos'),
    @('MAX_AUDIO_MB','Tamano maximo permitido para archivos de audio'),
    @('MAX_PROFILE_MB','Tamano maximo permitido para imagenes de perfil'),
    @('LOGIN_MAX_ATTEMPTS, LOGIN_LOCK_MINUTES','Politica de bloqueo ante intentos fallidos')
)

$body += Paragraph 'Anexo B. Criterios de verificacion' 'Heading1'
$body += Table @('Comprobacion','Resultado esperado') @(
    @('Login valido','Redireccion al panel principal'),
    @('Login invalido','Mensaje generico sin revelar detalles'),
    @('Carga MP3/WAV','Archivo guardado y metadatos persistidos'),
    @('Archivo duplicado','Rechazo por hash SHA-256'),
    @('Reproduccion','Audio entregado y evento registrado'),
    @('Dashboard','Conteos y resumenes cargan correctamente'),
    @('Responsive','Navegacion usable en escritorio y movil'),
    @('Cambio de contrasena','La clave nueva funciona y la anterior deja de autenticar')
)

$body += '<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1440" w:right="1200" w:bottom="1440" w:left="1200" w:header="720" w:footer="720" w:gutter="0"/></w:sectPr>'

$documentXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:w15="http://schemas.microsoft.com/office/word/2012/wordml" mc:Ignorable="w14 w15 wp14">
  <w:body>
    $body
  </w:body>
</w:document>
"@

$stylesXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="22"/></w:rPr></w:rPrDefault></w:docDefaults>
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:qFormat/><w:pPr><w:spacing w:after="160" w:line="276" w:lineRule="auto"/></w:pPr></w:style>
  <w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/><w:qFormat/><w:pPr><w:spacing w:before="240" w:after="240"/></w:pPr><w:rPr><w:b/><w:color w:val="1F4E79"/><w:sz w:val="48"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:basedOn w:val="Normal"/><w:qFormat/><w:rPr><w:color w:val="5B6770"/><w:sz w:val="28"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/><w:pPr><w:keepNext/><w:spacing w:before="360" w:after="160"/></w:pPr><w:rPr><w:b/><w:color w:val="1F4E79"/><w:sz w:val="32"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/><w:pPr><w:keepNext/><w:spacing w:before="240" w:after="120"/></w:pPr><w:rPr><w:b/><w:color w:val="2F75B5"/><w:sz w:val="26"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="ListParagraph"><w:name w:val="List Paragraph"/><w:basedOn w:val="Normal"/><w:pPr><w:ind w:left="720"/></w:pPr></w:style>
  <w:style w:type="paragraph" w:styleId="Caption"><w:name w:val="Caption"/><w:basedOn w:val="Normal"/><w:pPr><w:spacing w:before="80" w:after="220"/></w:pPr><w:rPr><w:i/><w:color w:val="5B6770"/><w:sz w:val="20"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Code"><w:name w:val="Code"/><w:basedOn w:val="Normal"/><w:pPr><w:spacing w:before="0" w:after="0"/><w:shd w:fill="F2F2F2"/></w:pPr><w:rPr><w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/><w:sz w:val="19"/></w:rPr></w:style>
  <w:style w:type="table" w:styleId="TableGrid"><w:name w:val="Table Grid"/><w:basedOn w:val="TableNormal"/><w:uiPriority w:val="39"/><w:tblPr><w:tblBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="BFBFBF"/><w:left w:val="single" w:sz="4" w:space="0" w:color="BFBFBF"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="BFBFBF"/><w:right w:val="single" w:sz="4" w:space="0" w:color="BFBFBF"/><w:insideH w:val="single" w:sz="4" w:space="0" w:color="BFBFBF"/><w:insideV w:val="single" w:sz="4" w:space="0" w:color="BFBFBF"/></w:tblBorders><w:tblCellMar><w:top w:w="80" w:type="dxa"/><w:left w:w="80" w:type="dxa"/><w:bottom w:w="80" w:type="dxa"/><w:right w:w="80" w:type="dxa"/></w:tblCellMar></w:tblPr></w:style>
</w:styles>
"@

$numberingXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:abstractNum w:abstractNumId="2"><w:multiLevelType w:val="hybridMultilevel"/><w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%1."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr></w:lvl></w:abstractNum>
  <w:num w:numId="2"><w:abstractNumId w:val="2"/></w:num>
</w:numbering>
"@

$contentTypesXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Default Extension="png" ContentType="image/png"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
  <Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
"@

$relsXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
"@

$docRelsXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>
  $($script:imageRels -join "`n  ")
</Relationships>
"@

$coreXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Manual de instalacion local en MAMP</dc:title>
  <dc:subject>Sistema gestor de archivos de audio</dc:subject>
  <dc:creator>Codex</dc:creator>
  <cp:lastModifiedBy>Codex</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">2026-08-10T00:00:00Z</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">2026-08-10T00:00:00Z</dcterms:modified>
</cp:coreProperties>
"@

$appXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Word</Application>
  <DocSecurity>0</DocSecurity>
  <ScaleCrop>false</ScaleCrop>
  <Company>Arca de Salvacion Radio 95.3 FM</Company>
  <LinksUpToDate>false</LinksUpToDate>
  <SharedDoc>false</SharedDoc>
  <HyperlinksChanged>false</HyperlinksChanged>
  <AppVersion>16.0000</AppVersion>
</Properties>
"@

Set-Content -LiteralPath (Join-Path $buildDir '[Content_Types].xml') -Value $contentTypesXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $buildDir '_rels\.rels') -Value $relsXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $buildDir 'word\document.xml') -Value $documentXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $buildDir 'word\styles.xml') -Value $stylesXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $buildDir 'word\numbering.xml') -Value $numberingXml -Encoding UTF8
New-Item -ItemType Directory -Force -Path (Join-Path $buildDir 'word\_rels') | Out-Null
Set-Content -LiteralPath (Join-Path $buildDir 'word\_rels\document.xml.rels') -Value $docRelsXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $buildDir 'docProps\core.xml') -Value $coreXml -Encoding UTF8
Set-Content -LiteralPath (Join-Path $buildDir 'docProps\app.xml') -Value $appXml -Encoding UTF8

if (Test-Path $docxPath) {
    Remove-Item -LiteralPath $docxPath -Force
}

$zip = [System.IO.Compression.ZipFile]::Open($docxPath, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    $files = Get-ChildItem -LiteralPath $buildDir -Recurse -File
    foreach ($file in $files) {
        $relative = $file.FullName.Substring($buildDir.Length).TrimStart('\') -replace '\\', '/'
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $file.FullName, $relative) | Out-Null
    }
}
finally {
    $zip.Dispose()
}

Remove-Item -LiteralPath $buildDir -Recurse -Force

Write-Output $docxPath
