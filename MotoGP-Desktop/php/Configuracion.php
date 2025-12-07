<?php
class Configuracion {
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'UO302313_DB';
    private const DB_USER = 'DBUSER2025';
    private const DB_PASS = 'DBPSWD2025';

    private $conexion; 

    public function __construct() {
        $this->conexion = new mysqli(
            self::DB_HOST,
            self::DB_USER,
            self::DB_PASS
        );

        if ($this->conexion->connect_errno) {
            throw new Exception(
                "Error de conexión (" . $this->conexion->connect_errno . "): " .
                $this->conexion->connect_error
            );
        }

        $this->conexion->set_charset("utf8mb4");
    }

    private function usarBaseDatos(): bool {
        return $this->conexion->select_db(self::DB_NAME);
    }

    public function existeBaseDatos(): bool {
        $resultado = $this->conexion->query(
            "SHOW DATABASES LIKE '" . self::DB_NAME . "'"
        );
        return $resultado && $resultado->num_rows > 0;
    }

    public function reiniciar(): bool {
        if (!$this->usarBaseDatos()) {
            return false;
        }

        $ok  = $this->conexion->query("DELETE FROM observaciones_facilitador");
        $ok &= $this->conexion->query("DELETE FROM pruebas_usabilidad");
        $ok &= $this->conexion->query("DELETE FROM usuarios");

        return (bool) $ok;
    }

    public function eliminar(): bool {
        $sql = "DROP DATABASE IF EXISTS " . self::DB_NAME;
        $ok  = $this->conexion->query($sql);
        return (bool) $ok;
    }

    public function exportar(): bool {
        if (!$this->usarBaseDatos()) {
            return false;
        }

        $ok  = $this->exportarTabla("usuarios");
        $ok &= $this->exportarTabla("pruebas_usabilidad");
        $ok &= $this->exportarTabla("observaciones_facilitador");

        return (bool) $ok;
    }

    private function exportarTabla(string $tabla): bool {
        $resultado = $this->conexion->query("SELECT * FROM $tabla");
        if (!$resultado) {
            return false;
        }

        $rutaArchivo = $tabla . ".csv"; 
        $archivo = fopen($rutaArchivo, "w");
        if (!$archivo) {
            $resultado->free();
            return false;
        }

        $campos = $resultado->fetch_fields();
        $cabeceras = [];
        foreach ($campos as $campo) {
            $cabeceras[] = $campo->name;
        }
        fputcsv($archivo, $cabeceras, ';');

        while ($fila = $resultado->fetch_assoc()) {
            fputcsv($archivo, $fila, ';');
        }

        fclose($archivo);
        $resultado->free();
        return true;
    }
    public function importar(): bool {
    if (!$this->usarBaseDatos()) {
        return false;
    }

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $tmpName      = $_FILES['csv_file']['tmp_name'];
    $originalName = $_FILES['csv_file']['name'];

    $tabla = pathinfo($originalName, PATHINFO_FILENAME);

    $tablasPermitidas = ['usuarios', 'pruebas_usabilidad', 'observaciones_facilitador'];
    if (!in_array($tabla, $tablasPermitidas, true)) {
        return false;
    }

    return $this->importarTabla($tabla, $tmpName);
}

private function importarTabla(string $tabla, string $rutaArchivo): bool {
    if (!is_readable($rutaArchivo)) {
        return false;
    }

    $archivo = fopen($rutaArchivo, 'r');
    if (!$archivo) {
        return false;
    }

    $cabeceras = fgetcsv($archivo, 0, ';');
    if ($cabeceras === false || count($cabeceras) === 0) {
        fclose($archivo);
        return false;
    }

    if (!$this->conexion->query("DELETE FROM `$tabla`")) {
        fclose($archivo);
        return false;
    }

    $columnList = '`' . implode('`,`', $cabeceras) . '`';

    while (($fila = fgetcsv($archivo, 0, ';')) !== false) { 
        if (count($fila) !== count($cabeceras)) {
            continue;
        }

        $values = [];
        foreach ($fila as $valor) {
            $values[] = "'" . $this->conexion->real_escape_string($valor) . "'";
        }

        $sqlInsert = "INSERT INTO `$tabla` ($columnList) VALUES (" . implode(',', $values) . ")";

        if (!$this->conexion->query($sqlInsert)) {
            fclose($archivo);
            return false;
        }
    }

    fclose($archivo);
    return true;
}

public function guardarPruebaUsabilidad(
    string $dispositivo,
    Test $test,
    string $comentariosUsuario,
    string $propuestasMejora,
    int $valoracion,
    string $comentariosFacilitador
): bool {
    if (!$this->usarBaseDatos()) {
        return false;
    }

    $tiempoSegundos = (int) round($test->getDuracionSegundos());

    $this->conexion->begin_transaction();

    try {
        $sqlPrueba = "INSERT INTO pruebas_usabilidad
            (dispositivo, tiempo_segundos, completada,
             comentarios_usuario, propuestas_mejora, valoracion)
            VALUES (?, ?, ?, 1, ?, ?, ?)";

        $stmtPrueba = $this->conexion->prepare($sqlPrueba);
        if (!$stmtPrueba) {
            throw new Exception("Error al preparar pruebas_usabilidad");
        }

        $stmtPrueba->bind_param(
            "isissi",
            $idUsuario,
            $dispositivo,
            $tiempoSegundos,
            $comentariosUsuario,
            $propuestasMejora,
            $valoracion
        );

        if (!$stmtPrueba->execute()) {
            throw new Exception("Error al ejecutar pruebas_usabilidad");
        }

        $idPrueba = $this->conexion->insert_id; 
        $stmtPrueba->close();

        $respuestas = $test->getRespuestas();

        $sqlResp = "INSERT INTO respuestas_prueba
                    (id_prueba, num_pregunta, respuesta)
                    VALUES (?, ?, ?)";

        $stmtResp = $this->conexion->prepare($sqlResp);
        if (!$stmtResp) {
            throw new Exception("Error al preparar respuestas_prueba");
        }

        foreach ($respuestas as $indice => $respuesta) {
            $numPregunta = $indice + 1;
            $stmtResp->bind_param("iis", $idPrueba, $numPregunta, $respuesta);
            if (!$stmtResp->execute()) {
                throw new Exception("Error al insertar respuesta $numPregunta");
            }
        }

        $stmtResp->close();

        if (trim($comentariosFacilitador) !== '') {
            $sqlObs = "INSERT INTO observaciones_facilitador
                       (id_prueba, comentarios_facilitador)
                       VALUES (?, ?)";

            $stmtObs = $this->conexion->prepare($sqlObs);
            if (!$stmtObs) {
                throw new Exception("Error al preparar observaciones_facilitador");
            }

            $stmtObs->bind_param("is", $idPrueba, $comentariosFacilitador);

            if (!$stmtObs->execute()) {
                throw new Exception("Error al insertar observaciones_facilitador");
            }

            $stmtObs->close();
        }

        $this->conexion->commit();
        return true;

    } catch (Exception $e) {
        $this->conexion->rollback();
        return false;
    }
}



}
?>
