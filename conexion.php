<?php
try {
    $dsn = getenv('DATABASE_URL');
    if (!$dsn) {
        throw new Exception("DATABASE_URL no está configurado");
    }
    // Registrar controladores PDO disponibles
    $drivers = PDO::getAvailableDrivers();
    error_log("Controladores PDO disponibles: " . implode(", ", $drivers));
    // Registrar el DSN
    error_log("DSN: " . $dsn);
    // Intentar conexión con DSN parseado
    $parsed = parse_url($dsn);
    $host = $parsed['host'] ?? '';
    $port = $parsed['port'] ?? '5432';
    $dbname = ltrim($parsed['path'], '/') ?? '';
    $user = $parsed['user'] ?? '';
    $pass = $parsed['pass'] ?? '';
    $pdo_dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    error_log("PDO DSN: " . $pdo_dsn);
    $pdo = new PDO($pdo_dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    error_log("Conexión a la base de datos exitosa");
} catch (Exception $e) {
    $pdo = null;
    error_log("Error al conectar a la base de datos: " . $e->getMessage());
    echo "Error de conexión: " . $e->getMessage();
    exit;
}
?>