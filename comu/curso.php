<?php
session_start(); // Iniciar sesión
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
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="shortcut icon" href="../assets/img/logo.png" type="png" />
  <script src="assets/js/index.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</head>

<body>

  <div class="body">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg custom-navbar">
      <div class="container-fluid">
        
        <div class="d-flex align-items-center">
          <img src="../assets/img/logo.png" alt="Logo" class="logo-img">
          <a class="navbar-brand fw-bold fs-4" href="#">Adso</a>
        </div>

       
          <div class="custom-search-bar-wrapper d-flex align-items-center">
            <div class="custom-search-bar">
              <i class="custom-search-icon fas fa-search"></i>
              <input type="search" placeholder="Search..." class="custom-search-input">
            </div>
          </div>

          <div class="user-menu">
            <?php if (isset($_SESSION['usuario_id'])): ?>
             
            <?php else: ?>
              <
              <div class="d-flex gap-3">
                <a class="btn btn-outline-primary" href="login.php">Iniciar sesión</a>
                
              </div>
            <?php endif; ?>
          </div>
        </div>
    </nav>

    
    <div
      class="offcanvas offcanvas-end"
      tabindex="-1"
      id="mobileMenu"
      aria-labelledby="mobileMenuLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileMenuLabel">Menu</h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="offcanvas"
          aria-label="Close"></button>
      </div>
      <div class="offcanvas-body d-flex flex-column justify-content-between">
        
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link" href="/#">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/#">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/#">Pricing</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/#">Contact</a>
          </li>
        </ul>

        
        <div class="dropdown mt-auto">
          <button
            class="btn btn-outline-secondary rounded-circle"
            type="button"
            id="mobileUserMenu"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="fa-solid fa-user"></i>
          </button>
          <ul class="dropdown-menu" aria-labelledby="mobileUserMenu">
            <li><a class="dropdown-item" href="#">Settings</a></li>
            <li><a class="dropdown-item" href="#">Earnings</a></li>
            <li>
              <hr class="dropdown-divider" />
            </li>
            <li><a class="dropdown-item" href="#">Log out</a></li>
          </ul>
        </div>
      </div>
    </div>
    <main>

     <p>los temas de la a evualar son </p>
     <div style="width: 400px; height: 210px; overflow: hidden;">
  <iframe 
    src="https://www.youtube.com/embed/QCw0L6FupQ0?start=5563" 
    title="YouTube video" 
    width="400" 
    height="210" 
    frameborder="0" 
    allowfullscreen>
    <div class="opacity-75">...</div>
  </iframe>
</div>


    </main>

    <footer class="custom-footer">
  <div class="container">
    
    <div class="row justify-content-between mb-4 flex-column flex-lg-row text-center text-lg-start">

     
      <div class="col-lg-3 col-12 mb-4 mb-lg-0">
        <h3 class="footer-heading">DESARROLLO</h3>
        <ul class="list-unstyled">
          <li><a href="/#" class="footer-link">Programación</a></li>
          <li><a href="/#" class="footer-link">Desarrollo Web</a></li>
          <li><a href="/#" class="footer-link">Desarrollo de Aplicaciones</a></li>
        </ul>
      </div>

      
      <div class="col-lg-3 col-12 mb-4 mb-lg-0">
        <h3 class="footer-heading">TECNOLOGÍAS</h3>
        <ul class="list-unstyled">
          <li><a href="/#" class="footer-link">Ciberseguridad</a></li>
          <li><a href="/#" class="footer-link">Bases de Datos</a></li>
          <li><a href="/#" class="footer-link">Cloud Computing</a></li>
          <li><a href="/#" class="footer-link">DevOps y Automatización</a></li>
        </ul>
      </div>

      
      <div class="col-lg-3 col-12 mb-4 mb-lg-0">
        <h3 class="footer-heading">INNOVACIÓN</h3>
        <ul class="list-unstyled">
          <li><a href="/#" class="footer-link">Inteligencia Artificial / Machine Learning</a></li>
          <li><a href="/#" class="footer-link">Diseño UX/UI</a></li>
        </ul>
      </div>

    </div>
  </div>
</footer>

       
        <hr class="my-4" />

        
        <div class="row flex-column flex-lg-row align-items-center align-items-lg-start">
         
          <div class="col-lg-6 col-12 mb-4 mb-lg-0 text-center text-lg-start">
            <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
              <img src="../assets/img/logo.png" alt="Logo" class="footer-logo me-2" />
              <span class="footer-text">© 2025 Adso, Inc</span>
            </div>
          </div>

         
          <div class="col-lg-6 col-12 text-center text-lg-end">
            <div class="d-flex justify-content-center justify-content-lg-end gap-3">
              <a href="/#" class="social-link"><i class="fab fa-instagram"></i></a>
              <a href="/#" class="social-link"><i class="fab fa-linkedin"></i></a>
              <a href="/#" class="social-link"><i class="fab fa-facebook"></i></a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>

</body>

</html>