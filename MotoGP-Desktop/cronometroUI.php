<?php
session_start();
require_once 'cronometro.php';

if (isset($_SESSION['cronometro'])) {
    $cronometro = unserialize($_SESSION['cronometro']);
} else {
    $cronometro = new Cronometro();
}

$mensaje = "Pulsa los botones para controlar el cronómetro.";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['botonArrancar'])) {
        $cronometro->arrancar();
        $mensaje = "Cronómetro arrancado.";
    }

    if (isset($_POST['botonParar'])) {
        $cronometro->parar();
        $mensaje = "Cronómetro detenido. ";
    }

    if (isset($_POST['botonMostrar'])) {
        $mensaje = "Tiempo transcurrido: " . $cronometro->mostrar();
    }

    if (isset($_POST['botonReiniciar'])) {
        $cronometro->reiniciar();
        $mensaje = "Cronómetro reiniciado.";
    }
}

$_SESSION['cronometro'] = serialize($cronometro);
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>MotoGP-Desktop: Cronómetro PHP</title>
    <meta name='author' content='David Fernando Bolanos Lopez' />
    <meta name='description' content='Cronómetro en PHP del proyecto MotoGP-Desktop' />
    <meta name='keywords' content='moto,motogp,cronometro,php' />
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />
    <link rel='stylesheet' href='estilo/estilo.css' />
    <link rel='stylesheet' href='estilo/layout.css' />
    <link rel='icon' href='multimedia/icon.ico' type='image/png' sizes='32x32'>
</head>

<body>
    <header>
        <h1>MotoGP Desktop</h1>
        <nav>
            <a href='index.html' title='Indice de MotoGP-Desktop'>Inicio</a>
            <a href='piloto.html' title='Informacion del piloto'>Piloto</a>
            <a href='circuito.html' title='Cirucuito a recorrer'>Circuito</a>
            <a href='meteorologia.html' title='Clima durante carrera'>Meteorologia</a>
            <a href='clasificaciones.php' title='Clasificacion actualizada'>Clasificaciones</a>
            <a class='active' href='juegos.html' title='Juegos desarrollados'>Juegos</a>
            <a href='ayuda.html' title='Ayuda'>Ayuda</a>
        </nav>
    </header>

    <p>Estás en:
        <a href='index.html'>Inicio</a> >
        <a href='juegos.html'>Juegos</a> >
        <strong>Cronómetro PHP</strong>
    </p>

    <main>
        <section>
            <h2>Cronómetro</h2>

            <p><?php echo htmlspecialchars($mensaje); ?></p>

            <form action='#' method='post' name='botones'>
                <p>
                    <input type='submit' name='botonArrancar' value='Arrancar' />
                    <input type='submit' name='botonParar' value='Parar' />
                    <input type='submit' name='botonMostrar' value='Mostrar' />
                </p>
            </form>
        </section>
    </main>
</body>
</html>
