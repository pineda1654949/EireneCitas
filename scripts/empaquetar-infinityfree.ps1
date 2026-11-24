<#
.SYNOPSIS
    Arma el paquete de EireneCitas para subirlo a InfinityFree (hosting gratuito sin terminal).

.DESCRIPTION
    Parte del commit actual (git archive), sin tocar tu carpeta de trabajo:
      1. composer install --no-dev y npm run build en una copia temporal.
      2. Arma <Salida>\htdocs\ con el contenido de public\ y la aplicacion en htdocs\eirene\.
      3. Crea htdocs\eirene\.env desde deploy\infinityfree\env.infinityfree.example con una APP_KEY nueva.
      4. Con -GenerarSql: crea una base temporal en un MySQL/MariaDB LOCAL, ejecuta migraciones,
         seeders de produccion y crea el administrador; exporta <Salida>\eirene_produccion.sql
         (para importarlo en phpMyAdmin) y borra la base temporal.
    Ver docs/DESPLIEGUE.md, seccion 8.

.EXAMPLE
    .\scripts\empaquetar-infinityfree.ps1 -Dominio eirene.rf.gd -GenerarSql -MysqlBin C:\xampp\mysql\bin -MysqlPuerto 3307
#>
param(
    [string]$Salida = (Join-Path $env:USERPROFILE 'Downloads\EireneCitas_InfinityFree'),
    [string]$Dominio = '',
    [switch]$GenerarSql,
    [string]$MysqlBin = 'C:\xampp\mysql\bin',
    [int]$MysqlPuerto = 3306,
    [string]$MysqlUsuario = 'root',
    [string]$MysqlClave = '',
    [string]$AdminUsuario = 'admin'
)

$ErrorActionPreference = 'Stop'
$repo = Split-Path -Parent $PSScriptRoot
$tmp = Join-Path $env:TEMP ("eirene-paquete-" + (Get-Date -Format 'yyyyMMddHHmmss'))

function Paso($texto) { Write-Host "`n==> $texto" -ForegroundColor Cyan }
function Ejecutar($exe, [string[]]$argumentos) {
    & $exe @argumentos
    if ($LASTEXITCODE -ne 0) { throw "Fallo: $exe $($argumentos -join ' ')" }
}
function ClaveAleatoria([int]$largo) {
    $letras = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ'.ToCharArray()
    $numeros = '23456789'.ToCharArray()
    $todos = $letras + $numeros
    $bytes = New-Object byte[] $largo
    [Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
    $c = ($bytes | ForEach-Object { $todos[$_ % $todos.Length] }) -join ''
    # garantiza al menos una letra y un numero (politica de contrasenas del sistema)
    return $c.Substring(0, $largo - 2) + $letras[$bytes[0] % $letras.Length] + $numeros[$bytes[1] % $numeros.Length]
}

if (Test-Path $Salida) { throw "La carpeta de salida ya existe: $Salida. Borrala o usa -Salida." }

Paso "Copiando el commit $(git -C $repo rev-parse --short HEAD) a una carpeta temporal"
New-Item -ItemType Directory -Force $tmp | Out-Null
Ejecutar git @('-C', $repo, 'archive', '--format=zip', '-o', "$tmp.zip", 'HEAD')
Expand-Archive "$tmp.zip" -DestinationPath $tmp
Remove-Item "$tmp.zip"
Copy-Item (Join-Path $tmp '.env.example') (Join-Path $tmp '.env')

Push-Location $tmp
try {
    Paso 'Instalando dependencias PHP de produccion (composer install --no-dev)'
    Ejecutar composer @('install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--prefer-dist', '--no-progress')
    Paso 'Compilando el frontend (npm ci + npm run build)'
    Ejecutar npm @('ci', '--no-audit', '--no-fund')
    Ejecutar npm @('run', 'build')
} finally { Pop-Location }

Paso "Armando $Salida"
$htdocs = Join-Path $Salida 'htdocs'
$app = Join-Path $htdocs 'eirene'
New-Item -ItemType Directory -Force $app | Out-Null

# Contenido de public/ en la raiz web, con el index.php y el .htaccess adaptados
Get-ChildItem -Force (Join-Path $tmp 'public') | Copy-Item -Destination $htdocs -Recurse -Force
Copy-Item (Join-Path $repo 'deploy\infinityfree\index.php') (Join-Path $htdocs 'index.php') -Force
Copy-Item (Join-Path $repo 'deploy\infinityfree\htaccess-raiz') (Join-Path $htdocs '.htaccess') -Force

# Aplicacion (sin tests, docs, node_modules ni archivos de desarrollo)
foreach ($item in 'app', 'bootstrap', 'config', 'database', 'lang', 'routes', 'vendor', 'artisan', 'composer.json', 'composer.lock') {
    Copy-Item (Join-Path $tmp $item) (Join-Path $app $item) -Recurse -Force
}
New-Item -ItemType Directory -Force (Join-Path $app 'resources') | Out-Null
Copy-Item (Join-Path $tmp 'resources\views') (Join-Path $app 'resources\views') -Recurse -Force
Copy-Item (Join-Path $repo 'deploy\infinityfree\htaccess-aplicacion') (Join-Path $app '.htaccess') -Force

# storage vacio con la estructura que Laravel necesita
foreach ($dir in 'app\private', 'app\public', 'framework\cache\data', 'framework\sessions', 'framework\views', 'logs') {
    New-Item -ItemType Directory -Force (Join-Path $app "storage\$dir") | Out-Null
}
# las cachés de bootstrap llevan rutas absolutas de tu PC: solo se dejan las de descubrimiento de paquetes
Get-ChildItem (Join-Path $app 'bootstrap\cache') -Filter '*.php' |
    Where-Object { $_.Name -notin 'packages.php', 'services.php' } | Remove-Item -Force

# .env de produccion con una APP_KEY nueva
$bytes = New-Object byte[] 32
[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
$appKey = 'base64:' + [Convert]::ToBase64String($bytes)
$contenidoEnv = Get-Content (Join-Path $repo 'deploy\infinityfree\env.infinityfree.example') -Raw
$contenidoEnv = $contenidoEnv -replace '(?m)^APP_KEY=.*$', "APP_KEY=$appKey"
if ($Dominio) { $contenidoEnv = $contenidoEnv -replace '<tu-subdominio>', $Dominio }
[IO.File]::WriteAllText((Join-Path $app '.env'), $contenidoEnv, (New-Object Text.UTF8Encoding $false))

$credenciales = @(
    "EireneCitas - datos del paquete para InfinityFree ($(Get-Date -Format 'yyyy-MM-dd HH:mm'))",
    "Commit: $(git -C $repo rev-parse HEAD)",
    '',
    "APP_KEY (respaldala: cifra las sesiones y las notas clinicas; si se pierde, las notas no se recuperan):",
    $appKey
)

if ($GenerarSql) {
    Paso 'Generando eirene_produccion.sql con un MySQL/MariaDB local'
    $base = 'eirene_paquete_' + (Get-Date -Format 'yyyyMMddHHmmss')
    $mysql = Join-Path $MysqlBin 'mysql.exe'
    $dump = Join-Path $MysqlBin 'mysqldump.exe'
    $conexion = @('-h', '127.0.0.1', '-P', "$MysqlPuerto", '-u', $MysqlUsuario)
    if ($MysqlClave) { $conexion += "-p$MysqlClave" }
    $adminClave = ClaveAleatoria 14

    Ejecutar $mysql ($conexion + @('-e', "CREATE DATABASE $base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"))
    try {
        # Variables de entorno: tienen prioridad sobre el .env de la copia temporal
        $vars = @{ APP_ENV = 'production'; APP_KEY = $appKey; DB_CONNECTION = 'mysql'; DB_HOST = '127.0.0.1'
                   DB_PORT = "$MysqlPuerto"; DB_DATABASE = $base; DB_USERNAME = $MysqlUsuario; DB_PASSWORD = $MysqlClave
                   CACHE_STORE = 'array'; QUEUE_CONNECTION = 'sync'; MAIL_MAILER = 'log' }
        $previas = @{}
        foreach ($k in $vars.Keys) { $previas[$k] = [Environment]::GetEnvironmentVariable($k); [Environment]::SetEnvironmentVariable($k, $vars[$k]) }
        Push-Location $tmp
        try {
            Ejecutar php @('artisan', 'migrate', '--force', '--no-interaction')
            Ejecutar php @('artisan', 'db:seed', '--force', '--no-interaction')
            Ejecutar php @('artisan', 'eirene:crear-admin', "--usuario=$AdminUsuario", "--contrasena=$adminClave", '--no-interaction')
        } finally {
            Pop-Location
            foreach ($k in $vars.Keys) { [Environment]::SetEnvironmentVariable($k, $previas[$k]) }
        }
        $sql = Join-Path $Salida 'eirene_produccion.sql'
        & $dump @($conexion + @('--single-transaction', '--skip-comments', '--no-tablespaces', '--default-character-set=utf8mb4', "--result-file=$sql", $base))
        if ($LASTEXITCODE -ne 0) { throw 'Fallo mysqldump' }
    } finally {
        & $mysql @($conexion + @('-e', "DROP DATABASE IF EXISTS $base"))
    }
    $credenciales += @('', 'Administrador inicial (cambia la contrasena despues del primer ingreso):', "  Usuario:    $AdminUsuario", "  Contrasena: $adminClave")
}

[IO.File]::WriteAllLines((Join-Path $Salida 'CREDENCIALES.txt'), $credenciales, (New-Object Text.UTF8Encoding $false))
Remove-Item -Recurse -Force $tmp

$archivos = (Get-ChildItem -Recurse -Force $htdocs -File).Count
$carpetas = (Get-ChildItem -Recurse -Force $htdocs -Directory).Count
Paso 'Listo'
Write-Host "Paquete:   $Salida"
Write-Host "Archivos:  $archivos  |  Carpetas: $carpetas  (InfinityFree limita los inodos por cuenta)"
Write-Host 'Siguiente: completa los datos <...> de htdocs\eirene\.env y sigue docs/DESPLIEGUE.md, seccion 8.'
