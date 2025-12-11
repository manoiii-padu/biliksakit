<?php
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['peranan'] = $user['peranan'];
            
            redirect('dashboard.php');
        } else {
            $error = 'Kata laluan tidak betul!';
        }
    } else {
        $error = 'Nama pengguna tidak dijumpai!';
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk - Bilik Sakit Asrama KVSP1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
        }
        .left-panel {
            flex: 0 0 40%;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .right-panel {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            overflow-y: auto;
        }
        .login-container {
            width: 100%;
            max-width: 450px;
        }
        .logo-section {
            margin-bottom: 3rem;
        }
        .logo-section i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        .logo-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .logo-section h2 {
            font-size: 1.5rem;
            font-weight: 400;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        .logo-section .subtitle {
            font-size: 1rem;
            opacity: 0.85;
            margin-top: 0.5rem;
        }
        .info-section p {
            font-size: 1rem;
            line-height: 1.8;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        .features-list {
            list-style: none;
            padding: 0;
        }
        .features-list li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        .features-list i {
            color: #2ecc71;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        .login-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .login-subtitle {
            color: #7f8c8d;
            margin-bottom: 2.5rem;
            font-size: 1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        .input-group-custom {
            position: relative;
        }
        .input-group-custom i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            z-index: 10;
        }
        .input-group-custom .form-control,
        .input-group-custom .form-select {
            padding-left: 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            height: 50px;
            font-size: 0.95rem;
        }
        .input-group-custom .form-control:focus,
        .input-group-custom .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .input-group-custom select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        .btn-login {
            background: #3498db;
            border: none;
            color: white;
            padding: 14px 30px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s;
            font-size: 1rem;
        }
        .btn-login:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        .register-link {
            text-align: center;
            margin-top: 2rem;
            color: #7f8c8d;
            font-size: 0.95rem;
        }
        .register-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .left-panel {
                flex: 0 0 auto;
                padding: 2rem;
            }
            .right-panel {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Left Panel - Information -->
    <div class="left-panel">
        <div class="logo-section">
            <i class="fas fa-hospital"></i>
            <h1>Bilik Sakit Asrama</h1>
            <h2>KVSP1</h2>
            <p class="subtitle">Sistem Pengurusan Kesihatan Pelajar</p>
        </div>
        
        <div class="info-section">
            <p>Sistem pengurusan kesihatan pelajar asrama yang cekap dan sistematik.</p>
            
            <ul class="features-list">
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Log harian pelajar</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Pengurusan bilik isolasi</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Kelulusan warden</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Laporan automatik</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Right Panel - Login Form -->
    <div class="right-panel">
        <div class="login-container">
            <h1 class="login-title">Log Masuk</h1>
            <p class="login-subtitle">Masukkan maklumat pengguna anda</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label for="username" class="form-label">ID Pengguna</label>
                    <div class="input-group-custom">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Masukkan ID pengguna anda" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Kata Laluan</label>
                    <div class="input-group-custom">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Masukkan kata laluan anda" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="peranan" class="form-label">Peranan Pengguna</label>
                    <div class="input-group-custom">
                        <i class="fas fa-user-tag"></i>
                        <select class="form-control" id="peranan" name="peranan" style="padding-left: 45px;">
                            <option value="">-- Pilih Peranan --</option>
                            <option value="biro">Biro Bilik Sakit (Pendaftaran & log harian)</option>
                            <option value="petugas">Warden (Kelulusan bilik isolasi)</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login">
                    Log Masuk
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            
            <div class="register-link">
                <p>Belum ada akaun? <a href="register.php">Daftar di sini</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
