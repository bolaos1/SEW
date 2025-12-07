<?php

require_once 'Test.php';
require_once 'Configuracion.php';

session_start();
if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = new Test();
}
$test = $_SESSION['test'];

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'iniciar':
            $test->iniciar();
            break;

        case 'siguiente':
            $respuesta = $_POST['respuesta'] ?? '';
            $test->guardarRespuestaActual($respuesta);

            if ($test->respuestaActualVacia()) {
                $mensaje = 'Debes responder antes de pasar a la siguiente pregunta.';
            } else {
                $test->siguientePregunta();
            }
            break;

        case 'terminar':
            $respuesta = $_POST['respuesta'] ?? '';
            $test->guardarRespuestaActual($respuesta);

            if ($test->respuestaActualVacia()) {
                $mensaje = 'Debes responder también a esta pregunta antes de terminar.';
            } else {
                $test->terminar();
            }
            break;

        case 'subir':
            $profesion            = $_POST['profesion']             ?? '';
            $edad                 = (int)($_POST['edad']            ?? 0);
            $genero               = $_POST['genero']                ?? '';
            $periciaInformatica   = $_POST['pericia_informatica']   ?? '';
            $comentariosUsuario   = $_POST['comentarios_usuario']   ?? '';
            $propuestasMejora     = $_POST['propuestas_mejora']     ?? '';
            $valoracion           = (int)($_POST['valoracion']      ?? 0);
            $comentariosFacilitador = $_POST['comentarios_facilitador'] ?? '';
            $dispositivo          = $_POST['dispositivo']           ?? 'ordenador';

            $config = new Configuracion();

            if ($config->guardarPruebaUsabilidad(
                $profesion,
                $edad,
                $genero,
                $periciaInformatica,
                $dispositivo,
                $test,
                $comentariosUsuario,
                $propuestasMejora,
                $valoracion,
                $comentariosFacilitador

            )) {
                $mensaje = 'Prueba guardada correctamente en la base de datos.';
                unset($_SESSION['test']);
                $test = new Test();
                $_SESSION['test'] = $test;
            } else {
                $mensaje = 'Se ha producido un error al guardar la prueba.';
            }
            break;
    }


    $_SESSION['test'] = $test;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoGP-Desktop - Prueba Usabilidad</title>
    <meta name="author" content="David Fernando Bolanos Lopez" />
    <meta name="description" content="Prueba de usabilidad dedicada al servidor web" />
    <meta name="keywords" content="Prueba,Usabilidad, MotoGP" />
    <link rel="stylesheet" href="../estilo/estilo.css" />
    <link rel="stylesheet" href="../estilo/layout.css" />
    <link rel="icon" href="multimedia/icon.ico" type="image/png" sizes="32x32">
</head>
<body>
<main>
    <header>
        <h1>Prueba de usabilidad</h1>
    </header>

    <?php if ($mensaje !== ''): ?>
    <section>
        <h2>Mensaje</h2>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
    </section>
    <?php endif; ?>

    <?php if (!$test->estaIniciada()): ?>

        <section>
            <h2>Iniciar prueba</h2>
            <p>Cuando pulses el botón se iniciará la prueba de usabilidad.</p>
            <form method="post" action="">
                <p>
                    <button type="submit" name="accion" value="iniciar">
                        Iniciar prueba
                    </button>
                </p>
            </form>
        </section>

    <?php elseif (!$test->estaFinalizada()): ?>

        <section>
            <h2>
                Pregunta <?php echo $test->getNumeroPreguntaActual(); ?>
                de <?php echo $test->getTotalPreguntas(); ?>
            </h2>

            <article>
                <p><?php echo htmlspecialchars($test->getPreguntaActual()); ?></p>

                <form method="post" action="">
                    <p>
                        <label for="respuesta">Respuesta:</label>
                        <input
                            type="text"
                            id="respuesta"
                            name="respuesta"
                            required>
                    </p>

                    <p>
                        <?php if ($test->esUltimaPregunta()): ?>
                            <button type="submit" name="accion" value="terminar">
                                Terminar prueba
                            </button>
                        <?php else: ?>
                            <button type="submit" name="accion" value="siguiente">
                                Siguiente pregunta
                            </button>
                        <?php endif; ?>
                    </p>
                </form>
            </article>
        </section>

    <?php else: ?>

        <section>
            <h2>Comentarios finales</h2>
            <form method="post" action="">
                <article>
                    <h3>Datos del usuario</h3>
                    <p>
                        <label for="profesion">Profesión:</label>
                        <input type="text" id="profesion" name="profesion" required>
                    </p>
                    <p>
                        <label for="edad">Edad:</label>
                        <input type="number" id="edad" name="edad" min="0" max="120" required>
                    </p>
                    <p>
                        <label for="genero">Género:</label>
                        <input type="text" id="genero" name="genero" required>
                    </p>
                    <p>
                        <label for="pericia_informatica">Pericia informática:</label>
                        <input type="text" id="pericia_informatica" name="pericia_informatica" required>
                    </p>
                    <p>
                        <label for="dispositivo">Dispositivo utilizado:</label>
                        <select id="dispositivo" name="dispositivo">
                            <option value="ordenador">Ordenador</option>
                            <option value="tableta">Tableta</option>
                            <option value="telefono">Teléfono</option>
                        </select>
                    </p>
                    <p>
                        <label for="valoracion">Valoración de la aplicación (0-10):</label>
                        <input type="number" id="valoracion" name="valoracion" min="0" max="10" required>
                    </p>
                </article>

                <article>
                    <h3>Comentarios del usuario</h3>
                    <p>
                        <label for="comentarios_usuario">Comentarios:</label>
                        <textarea id="comentarios_usuario" name="comentarios_usuario" rows="4"></textarea>
                    </p>
                    <p>
                        <label for="propuestas_mejora">Propuestas de mejora:</label>
                        <textarea id="propuestas_mejora" name="propuestas_mejora" rows="4"></textarea>
                    </p>
                </article>

                <article>
                    <h3>Observaciones del facilitador</h3>
                    <p>
                        <label for="comentarios_facilitador">Comentarios del facilitador:</label>
                        <textarea id="comentarios_facilitador" name="comentarios_facilitador" rows="4"></textarea>
                    </p>
                </article>

                <p>
                    <button type="submit" name="accion" value="subir">
                        Subir resultados
                    </button>
                </p>
            </form>
        </section>

    <?php endif; ?>

</main>
</body>
</html>
