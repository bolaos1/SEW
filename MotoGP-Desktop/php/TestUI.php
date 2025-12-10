<?php

require_once 'Test.php';
require_once 'Configuracion.php';

session_start();
if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = new Test();
}
$test = $_SESSION['test'];

$mensaje = '';

$fase = $test->getFase();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'iniciar':
            $profesion          = trim($_POST['profesion']            ?? '');
            $edad               = (int)($_POST['edad']                ?? 0);
            $genero             = trim($_POST['genero']               ?? '');
            $periciaInformatica = trim($_POST['pericia_informatica']  ?? '');
            $dispositivo        = $_POST['dispositivo']               ?? 'ordenador';

            // Validación únicamente en PHP
            if ($profesion === '' || $edad <= 0 || $genero === '' || $periciaInformatica === '') {
                $mensaje = 'Debes rellenar todos los datos del usuario antes de iniciar la prueba.';
                break;
            }

            $test->setDatosUsuario(
                $profesion,
                $edad,
                $genero,
                $periciaInformatica,
                $dispositivo
            );
            $test->iniciar(); // aquí puedes registrar la hora de inicio en la clase Test
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

        case 'finalizar':
            $respuesta = $_POST['respuesta'] ?? '';
            $test->guardarRespuestaActual($respuesta);

            if ($test->respuestaActualVacia()) {
                $mensaje = 'Debes responder también a esta pregunta antes de terminar.';
            } else {
                $test->finalizarPreguntas(); // aquí puedes registrar la hora de fin en la clase Test
            }
            break;

        case 'comentarios_siguiente':
            $comentariosUsuario = $_POST['comentarios_usuario'] ?? '';
            $propuestasMejora   = $_POST['propuestas_mejora']   ?? '';
            $valoracion         = (int)($_POST['valoracion']    ?? 0);

            $test->setComentariosUsuario(
                $comentariosUsuario,
                $propuestasMejora,
                $valoracion
            );
            $test->setFase('observaciones_facilitador');
            break;

        case 'subir':
            $comentariosUsuario     = $test->getComentariosUsuario();
            $propuestasMejora       = $test->getPropuestasMejora();
            $valoracion             = $test->getValoracion();
            $comentariosFacilitador = $_POST['comentarios_facilitador'] ?? '';
            $datosUsuario           = $test->getDatosUsuario();

            $config = new Configuracion();

            if ($config->guardarPruebaUsabilidad(
                $datosUsuario['profesion'],
                $datosUsuario['edad'],
                $datosUsuario['genero'],
                $datosUsuario['pericia_informatica'],
                $datosUsuario['dispositivo'],
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
    $fase = $test->getFase();
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

    <?php if ($fase === 'datos_usuario'): ?>

        <section>
            <h2>Datos del usuario</h2>
            <form method="post">
                <article>
                    <p>
                        <label>
                            Profesión:
                            <input type="text" name="profesion" required>
                        </label>
                    </p>
                    <p>
                        <label>
                            Edad:
                            <input type="number" name="edad" min="1" max="120" required>
                        </label>
                    </p>
                    <p>
                        <label>
                            Género:
                            <select name="genero" required>
                                <option value="" selected disabled>Selecciona una opción</option>
                                <option value="masculino">masculino</option>
                                <option value="femenino">femenino</option>
                                <option value="otro">otro</option>
                            </select>
                        </label>
                    </p>
                    <p>
                        <label>
                            Pericia informática:
                            <input type="text" name="pericia_informatica" required>
                        </label>
                    </p>
                    <p>
                        <label>
                            Dispositivo utilizado:
                            <select name="dispositivo">
                                <option value="ordenador">Ordenador</option>
                                <option value="tableta">Tableta</option>
                                <option value="telefono">Teléfono</option>
                            </select>
                        </label>
                    </p>
                </article>

                <p>
                    <button type="submit" name="accion" value="iniciar">
                        Iniciar prueba
                    </button>
                </p>
            </form>
        </section>

    <?php elseif ($fase === 'preguntas'): ?>

        <section>
            <h2>
                Pregunta <?php echo $test->getNumeroPreguntaActual(); ?>
                de <?php echo $test->getTotalPreguntas(); ?>
            </h2>

            <article>
                <p><?php echo htmlspecialchars($test->getPreguntaActual()); ?></p>

                <form method="post">
                    <p>
                        <label>
                            Respuesta:
                            <input
                                type="text"
                                name="respuesta"
                                required>
                        </label>
                    </p>

                    <p>
                        <?php if ($test->esUltimaPregunta()): ?>
                            <button type="submit" name="accion" value="finalizar">
                                Finalizar prueba
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

    <?php elseif ($fase === 'comentarios_usuario'): ?>

        <section>
            <h2>Comentarios del usuario</h2>
            <form method="post" action="">
                <article>
                    <p>
                        <label>
                            Comentarios:
                            <textarea name="comentarios_usuario" rows="4"></textarea>
                        </label>
                    </p>
                    <p>
                        <label>
                            Propuestas de mejora:
                            <textarea name="propuestas_mejora" rows="4"></textarea>
                        </label>
                    </p>
                    <p>
                        <label>
                            Valoración de la aplicación (0-10):
                            <input type="number" name="valoracion" min="0" max="10" required>
                        </label>
                    </p>
                </article>

                <p>
                    <button type="submit" name="accion" value="comentarios_siguiente">
                        Siguiente
                    </button>
                </p>
            </form>
        </section>

    <?php elseif ($fase === 'observaciones_facilitador'): ?>

        <section>
            <h2>Observaciones del facilitador</h2>
            <form method="post" action="">
                <article>
                    <p>
                        <label>
                            Comentarios del facilitador:
                            <textarea name="comentarios_facilitador" rows="4"></textarea>
                        </label>
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
