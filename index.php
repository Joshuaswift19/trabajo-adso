<?php
session_start(); // Iniciar sesión

// Tiempo máximo de inactividad en segundos (10 minutos = 600 segundos)
$tiempo_maximo_inactividad = 600;

// Verificar si existe la última actividad
if (isset($_SESSION['ultima_actividad'])) {
  $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];
  if ($tiempo_inactivo > $tiempo_maximo_inactividad) {
    // Destruir la sesión y redirigir al usuario a index.php
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
  }
}

// Actualizar el tiempo de la última actividad
$_SESSION['ultima_actividad'] = time();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Repositorio Estudiantil</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link rel="shortcut icon" href="assets/img/logo.png" type="png" />
  <script src="assets/js/index.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</head>

<body>

  <div class="body">
    <?php include 'navbar.php'; ?> <!-- Incluir el navbar -->
    <!-- Main Content -->
    <main class="container my-5">
      <!-- Carousel -->
      <div class="full-width-carousel">
        <div id="programmingCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
          <div class="carousel-inner">
            <div class="carousel-item active" data-bs-interval="3000">
              <a href="http://localhost:3000/explorar.php">
                <img src="https://images.pexels.com/photos/159711/books-bookstore-book-reading-159711.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" class="d-block w-100 img-fluid" alt="Libros de programación">
              </a>
              <div class="carousel-caption">
                <h5 class="text-white">Tu Biblioteca Virtual para Dominar el Código</h5>
                <p class="text-white">Descubre miles de libros digitales sobre Python, JavaScript y más para impulsar tu carrera como programador.</p>
              </div>
            </div>
            <div class="carousel-item" data-bs-interval="3000">
              <a href="http://localhost:3000/explorar.php">
                <img src="https://images.pexels.com/photos/267669/pexels-photo-267669.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" class="d-block w-100 img-fluid" alt="Libro y portátil con código">
              </a>
              <div class="carousel-caption">
                <h5 class="text-white">Recursos Digitales para Programadores</h5>
                <p class="text-white">Explora guías y libros electrónicos sobre desarrollo web, móvil e inteligencia artificial desde cualquier dispositivo.</p>
              </div>
            </div>
            <div class="carousel-item" data-bs-interval="3000">
              <a href="http://localhost:3000/explorar.php">
                <img src="https://images.pexels.com/photos/3183183/pexels-photo-3183183.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" class="d-block w-100 img-fluid" alt="Tableta con libro digital">
              </a>
              <div class="carousel-caption">
                <h5 class="text-white">Aprende Programación a Tu Ritmo</h5>
                <p class="text-white">Accede a materiales exclusivos sobre algoritmos, frameworks y herramientas modernas para crear proyectos innovadores.</p>
              </div>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#programmingCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#programmingCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
          </button>
        </div>
      </div>

      <!-- Header with Subtitle and Book Carousel -->
      <div class="text-center my-5">
        <h1 class="fw-bold display-5">Biblioteca SENA</h1>
        <p class="text-muted lead">Explora un mundo de conocimiento en programación a través de nuestros libros, pensados para quienes buscan aprender, crear y transformar con código.</p>
        <h2 class="fw-bold text-dark mt-4">Los Libros de Programación que Están Revolucionando el Momento</h2>
      </div>

      <!-- Book Carousel -->
      <div class="container-fluid p-0 mb-5">
        <div id="booksCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <!-- First Slide -->
            <div class="carousel-item active" data-bs-interval="5000">
              <div class="row row-cols-1 row-cols-md-3 g-4">
                <!-- Card 1 -->
                <div class="col">
                  <div class="card h-100">
                    <div class="image-container position-relative">
                      <img src="https://images.pexels.com/photos/159711/books-bookstore-book-reading-159711.jpeg?auto=compress&cs=tinysrgb&w=600" class="card-img-top" alt="Python Avanzado">
                      <div class="card-overlay p-3 text-white">
                        <h5 class="text-white">Python Avanzado</h5>
                        <h6 class="text-white mb-2">Autor: Laura Martínez</h6>
                        <p>¡Desata el poder de Python! Aprende técnicas avanzadas para crear inteligencia artificial, automatizar procesos y dominar algoritmos complejos. Escrito por Laura Martínez, experta en ciencia de datos, este libro te llevará al siguiente nivel en programación.</p>
                      </div>
                    </div>
                    <div class="card-body">
                      <h5 class="card-title">Python Avanzado</h5>
                      <h6 class="card-subtitle mb-2 text-muted">Autor: Laura Martínez</h6>
                      <p class="card-text">Domina Python con técnicas avanzadas, desde estructuras de datos hasta inteligencia artificial.</p>
                      <a href="explorar.php" class="text-primary text-decoration-none" style="z-index: 10;">Ver Más</a>
                    </div>
                  </div>
                </div>
                <!-- Card 2 -->
                <div class="col">
                  <div class="card h-100">
                    <div class="image-container position-relative">
                      <img src="https://images.pexels.com/photos/267669/pexels-photo-267669.jpeg?auto=compress&cs=tinysrgb&w=600" class="card-img-top" alt="JavaScript Moderno">
                      <div class="card-overlay p-3 text-white">
                        <h5 class="text-white">JavaScript Moderno</h5>
                        <h6 class="text-white mb-2">Autor: Carlos Pérez</h6>
                        <p>¡Crea aplicaciones web que sorprendan! Descubre las últimas herramientas de JavaScript para construir interfaces dinámicas y rápidas. Carlos Pérez, desarrollador líder, te guía a través de frameworks modernos y proyectos reales.</p>
                      </div>
                    </div>
                    <div class="card-body">
                      <h5 class="card-title">JavaScript Moderno</h5>
                      <h6 class="card-subtitle mb-2 text-muted">Autor: Carlos Pérez</h6>
                      <p class="card-text">Aprende las últimas características de JavaScript para construir aplicaciones web dinámicas.</p>
                      <a href="explorar.php" class="text-primary text-decoration-none" style="z-index: 10;">Ver Más</a>
                    </div>
                  </div>
                </div>
                <!-- Card 3 -->
                <div class="col">
                  <div class="card h-100">
                    <div class="image-container position-relative">
                      <img src="https://images.pexels.com/photos/3183183/pexels-photo-3183183.jpeg?auto=compress&cs=tinysrgb&w=600" class="card-img-top" alt="Algoritmos Esenciales">
                      <div class="card-overlay p-3 text-white">
                        <h5 class="text-white">Algoritmos Esenciales</h5>
                        <h6 class="text-white mb-2">Autor: Sofía Ramírez</h6>
                        <p>¡Resuelve problemas como un genio! Este libro revela los secretos de los algoritmos y estructuras de datos que impulsan la tecnología actual. Sofía Ramírez, pionera en informática, comparte su experiencia en un viaje fascinante.</p>
                      </div>
                    </div>
                    <div class="card-body">
                      <h5 class="card-title">Algoritmos Esenciales</h5>
                      <h6 class="card-subtitle mb-2 text-muted">Autor: Sofía Ramírez</h6>
                      <p class="card-text">Explora algoritmos clave y estructuras de datos para resolver problemas complejos.</p>
                      <a href="explorar.php" class="text-primary text-decoration-none" style="z-index: 10;">Ver Más</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Second Slide -->
            <div class="carousel-item" data-bs-interval="5000">
              <div class="row row-cols-1 row-cols-md-3 g-4">
                <!-- Card 4 -->
                <div class="col">
                  <div class="card h-100">
                    <div class="image-container position-relative">
                      <img src="https://images.pexels.com/photos/159711/books-bookstore-book-reading-159711.jpeg?auto=compress&cs=tinysrgb&w=600" class="card-img-top" alt="Desarrollo Web Full Stack">
                      <div class="card-overlay p-3 text-white">
                        <h5 class="text-white">Desarrollo Web Full Stack</h5>
                        <h6 class="text-white mb-2">Autor: Diego Vargas</h6>
                        <p>¡Conviértete en un maestro del desarrollo web! Desde HTML hasta Node.js, este libro te enseña a construir aplicaciones completas. Diego Vargas, creador de startups, te inspira con proyectos prácticos y trucos esenciales.</p>
                      </div>
                    </div>
                    <div class="card-body">
                      <h5 class="card-title">Desarrollo Web Full Stack</h5>
                      <h6 class="card-subtitle mb-2 text-muted">Autor: Diego Vargas</h6>
                      <p class="card-text">Conviértete en un experto en desarrollo web con HTML, CSS, JavaScript y Node.js.</p>
                      <a href="explorar.php" class="text-primary text-decoration-none" style="z-index: 10;">Ver Más</a>
                    </div>
                  </div>
                </div>
                <!-- Card 5 -->
                <div class="col">
                  <div class="card h-100">
                    <div class="image-container position-relative">
                      <img src="https://images.pexels.com/photos/267669/pexels-photo-267669.jpeg?auto=compress&cs=tinysrgb&w=600" class="card-img-top" alt="Inteligencia Artificial Básica">
                      <div class="card-overlay p-3 text-white">
                        <h5 class="text-white">Inteligencia Artificial Básica</h5>
                        <h6 class="text-white mb-2">Autor: Ana Torres</h6>
                        <p>¡Explora el futuro con IA! Aprende los fundamentos del aprendizaje automático y redes neuronales en este libro imprescindible. Ana Torres, investigadora en IA, te guía en un viaje accesible y emocionante.</p>
                      </div>
                    </div>
                    <div class="card-body">
                      <h5 class="card-title">Inteligencia Artificial Básica</h5>
                      <h6 class="card-subtitle mb-2 text-muted">Autor: Ana Torres</h6>
                      <p class="card-text">Introduce los fundamentos de IA, desde aprendizaje automático hasta redes neuronales.</p>
                      <a href="explorar.php" class="text-primary text-decoration-none" style="z-index: 10;">Ver Más</a>
                    </div>
                  </div>
                </div>
                <!-- Card 6 -->
                <div class="col">
                  <div class="card h-100">
                    <div class="image-container position-relative">
                      <img src="https://images.pexels.com/photos/3183183/pexels-photo-3183183.jpeg?auto=compress&cs=tinysrgb&w=600" class="card-img-top" alt="Bases de Datos Modernas">
                      <div class="card-overlay p-3 text-white">
                        <h5 class="text-white">Bases de Datos Modernas</h5>
                        <h6 class="text-white mb-2">Autor: Miguel López</h6>
                        <p>¡Domina los datos del mundo digital! Descubre SQL, NoSQL y estrategias para gestionar información en apps modernas. Miguel López, experto en bases de datos, te equipa con habilidades demandadas por la industria.</p>
                      </div>
                    </div>
                    <div class="card-body">
                      <h5 class="card-title">Bases de Datos Modernas</h5>
                      <h6 class="card-subtitle mb-2 text-muted">Autor: Miguel López</h6>
                      <p class="card-text">Aprende SQL y NoSQL para gestionar datos en aplicaciones modernas.</p>
                      <a href="explorar.php" class="text-primary text-decoration-none" style="z-index: 10;">Ver Más</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#booksCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#booksCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
          </button>
        </div>
      </div>

      <!-- Sección con fondo fijo -->
      <div class="fixed-bg-section"></div>

      <!-- Nueva sección de frases -->
      <div class="quotes-section">
        <h1 class="fw-bold display-5">¿Sabías que...?</h1>
        <div class="quote">
          "La programación no es solo una habilidad, sino una forma de pensar."
          <div class="author">– Steve Jobs</div>
        </div>
        <div class="quote">
          "El verdadero peligro no es que los ordenadores empiecen a pensar como los hombres, sino que los hombres empiecen a pensar como los ordenadores."
          <div class="author">– Arthur C. Clarke</div>
        </div>
        <div class="quote">
          "Cualquier idiota puede escribir un programa que una computadora entiende, los verdaderos programadores pueden escribir código que los humanos entienden."
          <div class="author">– Martin Fowler</div>
        </div>
      </div>

    </main>
    <?php include 'footer.php'; ?> <!-- Incluir el footer -->
  </div>
  <script src="assets/js/search.js"></script>
</body>

</html>