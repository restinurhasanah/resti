<?php
session_start();
include "koneksi.php";

// Proses tambah admin baru
if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_plain = mysqli_real_escape_string($koneksi, $_POST['password']);

    // Cek apakah username atau password sudah digunakan
    $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username' OR password='$password_plain'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username atau Password sudah digunakan, silakan ganti!";
    } else {
        // Hash password untuk keamanan
        $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

        // Tambahkan kolom status agar admin baru langsung aktif
        $insert = mysqli_query($koneksi, "INSERT INTO user (nama, username, password, level, status) 
                                          VALUES ('$nama', '$username', '$password_hash', 'admin', 'aktif')");

        if ($insert) {
            $success = "Akun admin baru berhasil dibuat!";
        } else {
            $error = "Gagal membuat akun admin: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Buat Akun Admin Baru</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin:0;
    padding:0;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family: Arial, sans-serif;
    background: 
        linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
        url('admin/kk.jpg') no-repeat center center fixed;
    background-size: cover;
}

.card-login {
    width: 350px;
    padding: 30px 20px;
    background: rgba(255,255,255,0.25);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    text-align: center;
    color: #fff;
}

.card-login img {
    width: 80px;
    display: block;
    margin: 0 auto 10px auto;
}

.card-login h4 {
    font-size: 16px;
    margin-bottom: 20px;
    font-weight: 500;
}

.card-login h3 {
    font-size: 20px;
    margin-bottom: 20px;
    font-weight: 600;
}

.card-login .form-control {
    border-radius: 10px;
    border: none;
    padding: 12px;
    margin-bottom: 15px;
    background: rgba(255,255,255,0.2);
    color: #fff;
}
.card-login .form-control::placeholder {
    color: #e0e0e0;
}

.card-login .btn {
    border-radius: 10px;
    background: #555;
    border: none;
    font-weight: 600;
    transition: 0.3s;
}
.card-login .btn:hover {
    background: #777;
}

.card-login .alert {
    font-size: 14px;
    background: rgba(255,0,0,0.7);
    border: none;
    color: #fff;
}
.card-login .alert-success {
    background: rgba(0,128,0,0.7);
}
</style>
</head>
<body>

<div class="card card-login">
    <img src="admin/g.png" alt="Logo Resti Wedangan">
    <h4>Resti Wedangan</h4>

    <h3>Registrasi Admin Baru</h3>

    <?php if(isset($error)){ echo "<div class='alert alert-danger text-center'>$error</div>"; } ?>
    <?php if(isset($success)){ echo "<div class='alert alert-success text-center'>$success</div>"; } ?>

    <form method="POST">
        <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
        <input type="text" name="username" class="form-control" placeholder="Username" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button name="register" class="btn w-100">Simpan Akun</button>

        <div class="mt-3">
            <a href="login_admin.php" class="text-white text-decoration-none small"> Kembali ke Login</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
