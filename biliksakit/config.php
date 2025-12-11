<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'biliksakit');

// Koneksi Database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Sambungan gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Fungsi untuk check login
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

// Fungsi untuk check peranan
function checkRole($allowedRoles) {
    checkLogin();
    if (!in_array($_SESSION['peranan'], $allowedRoles)) {
        redirect('dashboard.php');
    }
}
?>


