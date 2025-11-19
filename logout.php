<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logout Berhasil | Resti Wedangan</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;

    /* Background gelap dengan efek buram */
    background:
        linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
        url('admin/kk.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #fff;
    text-align: center;
}

.container {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    padding: 50px 60px;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.5);
    animation: fadeIn 1s ease;
    max-width: 420px;
}

h1 {
    font-size: 34px;
    margin-bottom: 20px;
    background: linear-gradient(90deg, #ffffff, #bbbbbb);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

p {
    font-size: 17px;
    margin-bottom: 30px;
    color: #eeeeee;
    line-height: 1.6;
}

a {
    text-decoration: none;
    background: rgba(85, 85, 85, 0.9);
    color: #fff;
    padding: 12px 35px;
    border-radius: 50px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    transition: 0.3s ease;
}

a:hover {
    background: rgba(120,120,120,0.9);
    transform: scale(1.08);
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

@keyframes fadeIn {
    from {opacity: 0; transform: translateY(-20px);}
    to {opacity: 1; transform: translateY(0);}
}
</style>
</head>
<body>

<div class="container">
    <h1>✔ Logout Berhasil</h1>
    <p>Terima kasih telah menggunakan sistem kasir<br><strong>Resti Wedangan</strong>.<br>
    Semoga harimu menyenangkan — sampai jumpa lagi! 👋</p>
    <a href="index.php">Login Kembali</a>
</div>

</body>
</html>
