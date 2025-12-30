<?php
require_once 'db.php';

$csvFile = __DIR__ . DIRECTORY_SEPARATOR . 'BDcapeletti.csv';

if (!file_exists($csvFile)) {
    die("No se encontró el archivo CSV en: " . $csvFile);
}

$handle = fopen($csvFile, 'r');
if ($handle === false) {
    die("No se pudo abrir el archivo CSV");
}

$line = 0;
$insertados = 0;
$skip = 0;

while (($data = fgetcsv($handle, 0, ';')) !== false) {
    $line++;

    // Saltar encabezado
    if ($line === 1) {
        continue;
    }

    // El archivo tiene 16 columnas fijas
    if (count($data) !== 16) {
        // Línea rara, la ignoramos
        $skip++;
        continue;
    }

    list(
        $nombre,
        $zona,
        $registrado_por,
        $cuil,
        $tipo_accidente,
        $fecha_accidente,
        $dias_ilt,
        $lesion_1,
        $diag_1,
        $secuelas,
        $localidad,
        $provincia,
        $cuit_empleador,
        $tipo_registro,
        $egreso,
        $captador
    ) = $data;

    // Mapeo a tu tabla leads
    $stmt = $pdo->prepare("
        INSERT INTO leads (
            nombre,
            descripcion_lesion,
            art,
            cuil,
            tipo_contingencia,
            fecha_accidente,
            edad,
            tipo_lesion,
            diagnostico,
            reingreso,
            localidad,
            provincia,
            cuit_empresa,
            tipo_tramite,
            observaciones,
            captador_asignado
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    try {
        $stmt->execute([
            $nombre,
            $zona,
            $registrado_por,
            $cuil,
            $tipo_accidente,
            $fecha_accidente,
            $dias_ilt,
            $lesion_1,
            $diag_1,
            $secuelas,
            $localidad,
            $provincia,
            $cuit_empleador,
            $tipo_registro,
            $egreso,
            $captador
        ]);
        $insertados++;
    } catch (PDOException $e) {
        // Si alguna fila explota por caracteres raros, la salteamos y seguimos
        $skip++;
        // Si querés debuggear: descomentá la línea siguiente
        // echo "Error en línea $line: " . $e->getMessage() . "<br>";
    }
}

fclose($handle);

echo "Importación finalizada.<br>";
echo "Filas insertadas: $insertados<br>";
echo "Filas saltadas: $skip<br>";
