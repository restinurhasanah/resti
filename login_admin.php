<?php
session_start();
include 'koneksi.php';


if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    /* ============================================================
       1. LOGIN ADMIN RESMI (TIDAK MENGGUNAKAN DATABASE)
       ============================================================ */
    if ($username === "adminresmi" && $password === "123") {
        $_SESSION['id_user'] = 0;
        $_SESSION['nama'] = "Admin Resmi";
        $_SESSION['level'] = "adminresmi";

        echo "<script>window.location.href='data_admin.php';</script>";
        exit();
    }

    /* ============================================================
       2. LOGIN USER BIASA (ADMIN / KASIR BERDASARKAN TABEL USER)
       ============================================================ */
    $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
    $data = mysqli_fetch_array($query);

    if($data){
        if(password_verify($password, $data['password'])){
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['nama'] = $data['nama']; 
            $_SESSION['level'] = $data['level'];

            if($data['level'] == 'admin'){
                echo "<script>window.location.href='admin/dashboard.php';</script>";
                exit();
            } elseif($data['level'] == 'pengganti'){
                echo "<script>window.location.href='admin/dashboard.php';</script>";
                exit();
            }
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin</title>
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

    animation: fadeIn 1.2s ease-out forwards;
    opacity: 0;
}

@keyframes fadeIn {
    0%   { opacity: 0; }
    100% { opacity: 1; }
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
    animation: popUp 1s ease forwards;
    opacity: 0;
    transform: translateY(30px);
}

@keyframes popUp {
    0%   { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}

.card-login img {
    width: 80px;
    margin: 0 auto 10px auto;
    animation: floatLogo 3s ease-in-out infinite;
}

@keyframes floatLogo {
    0%   { transform: translateY(0); }
    50%  { transform: translateY(-8px); }
    100% { transform: translateY(0); }
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
</style>
</head>
<body>

<div class="card card-login">
    <img src="admin/g.png" alt="Logo Resti Wedangan">
    <h4>Resti Wedangan</h4>

    <h3>Login Admin</h3>
    <?php if(isset($error)){ echo "<div class='alert alert-danger'>$error</div>"; } ?>

    <form method="POST">
        <input type="text" name="username" class="form-control" placeholder="Username" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
       <a href="admin/dashboard.php"><button name="login" class="btn w-100">Masuk</button></a> 
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
