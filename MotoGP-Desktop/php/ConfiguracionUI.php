<?php
require_once 'Configuracion.php';

$mensaje = '';

try {
    $configuracion = new Configuracion();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'reiniciar':
                $mensaje = $configuracion->reiniciar()
                    ? 'La base de datos se ha reiniciado correctamente.'
                    : 'Se ha producido un error al reiniciar la base de datos.';
                break;

            case 'eliminar':
                $mensaje = $configuracion->eliminar()
                    ? 'La base de datos se ha eliminado correctamente.'
                    : 'Se ha producido un error al eliminar la base de datos.';
                break;

            case 'exportar':
                $mensaje = $configuracion->exportar()
                    ? 'Los datos se han exportado correctamente en ficheros CSV.'
                    : 'Se ha producido un error al exportar los datos.';
                break;
            case 'importar':
                $mensaje = $configuracion->importar()
                    ? 'Los datos se han importado correctamente desde los ficheros CSV.'
                    : 'Se ha producido un error al importar los datos.';
                    break;
        }
    }
} catch (Exception $e) {
    $mensaje = 'No se ha podido establecer la conexión con la base de datos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoGP-Desktop - Configuración</title>
    <meta name="author" content="David Fernando Bolanos Lopez" />
    <meta name="description" content="Configuración de la base de datos de las pruebas de usabilidad" />
    <meta name="keywords" content="configuracion, soporte, administrador, base de datos" />
    <link rel="stylesheet" href="../estilo/estilo.css" />
    <link rel="stylesheet" href="../estilo/layout.css" />
    <link rel="icon" href="multimedia/icon.ico" type="image/png" sizes="32x32">
</head>
<body>
    <main>
        <header>
            <h1>Configuración de la base de datos</h1>
        </header>

        <?php if ($mensaje !== ''): ?>
        <section>
            <h2>Resultado de la operación</h2>
            <p><?php echo htmlspecialchars($mensaje); ?></p>
        </section>
        <?php endif; ?>

        <section>
            <h2>Opciones de configuración</h2>

            <article>
                <h3>Reiniciar base de datos</h3>
                <p>Elimina todos los datos de las tablas, manteniendo la estructura.</p>
                <form method="post" action="">
                    <p>
                        <button type="submit" name="accion" value="reiniciar">
                            Reiniciar base de datos
                        </button>
                    </p>
                </form>
            </article>

            <article>
                <h3>Eliminar base de datos</h3>
                <p>Elimina completamente la base de datos, sus tablas y los datos asociados.</p>
                <form method="post" action="">
                    <p>
                        <button type="submit" name="accion" value="eliminar">
                            Eliminar base de datos
                        </button>
                    </p>
                </form>
            </article>

            <article>
                <h3>Exportar datos</h3>
                <p>Exporta el contenido de las tablas a ficheros en formato CSV.</p>
                <form method="post" action="">
                    <p>
                        <button type="submit" name="accion" value="exportar">
                            Exportar datos a CSV
                        </button>
                    </p>
                </form>
            </article>

            <article>
                <h3>Importar datos</h3>
                <p>
                    Importa los datos desde un fichero CSV generado previamente con la opción de exportación.
                    El nombre del fichero debe coincidir con la tabla de destino por ejemplo: usuarios.csv.
                </p>
                <form method="post" action="" enctype="multipart/form-data">
                    <p>
                        <label for="csv_file">Seleccionar archivo CSV:</label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    </p>
                    <p>
                        <button type="submit" name="accion" value="importar">
                            Importar
                        </button>
                    </p>
                </form>
            </article>            
        </section>
    </main>
</body>
</html>
