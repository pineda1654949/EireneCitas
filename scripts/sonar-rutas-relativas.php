<?php

/*
 * Convierte las rutas absolutas de los reportes de PHPUnit en rutas relativas
 * a la raiz del proyecto. Asi SonarQube encuentra los archivos aunque el
 * analisis corra en otra maquina (el runner Linux de GitLab, por ejemplo).
 *
 * Uso: php scripts/sonar-rutas-relativas.php build/logs/clover.xml build/logs/junit.xml
 */

$raiz = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/').'/';

foreach (array_slice($argv, 1) as $reporte) {
    $xml = file_get_contents($reporte);

    $xml = preg_replace_callback(
        '/(\b(?:name|file)=")([^"]+)(")/',
        function (array $m) use ($raiz): string {
            $ruta = str_replace('\\', '/', $m[2]);

            if (stripos($ruta, $raiz) !== 0) {
                return $m[0];
            }

            return $m[1].substr($ruta, strlen($raiz)).$m[3];
        },
        $xml
    );

    file_put_contents($reporte, $xml);
    echo "Rutas relativas: {$reporte}".PHP_EOL;
}
