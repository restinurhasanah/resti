<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Selamat Datang | Resti Wedangan</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

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

    /* HALAMAN MASUK SMOOTH */
    animation: fadeInPage 1.2s ease-out forwards;
    opacity: 0;
  }

  @keyframes fadeInPage {
      0%   { opacity: 0; transform: translateY(40px); }
      100% { opacity: 1; transform: translateY(0); }
  }

  /* HALAMAN KELUAR */
  .fadeOut {
      animation: fadeOutPage 0.6s ease-out forwards;
  }
  @keyframes fadeOutPage {
      0%   { opacity: 1; }
      100% { opacity: 0; transform: scale(0.95); }
  }

  .container {
      width: 450px;
      padding: 40px 30px;
      background: rgba(255,255,255,0.25);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      box-shadow: 0 10px 35px rgba(0,0,0,0.3);
      text-align: center;
      color: #fff;
      animation: containerPop 1.4s ease forwards;
      transform: scale(0.85);
      opacity: 0;
  }

  @keyframes containerPop {
      0%   { opacity: 0; transform: scale(0.85) translateY(30px); }
      100% { opacity: 1; transform: scale(1) translateY(0); }
  }

  .container img {
      width: 180px;
      height: auto;
      margin-bottom: 20px;
      filter: drop-shadow(0 0 8px rgba(255, 200, 0, 0.6));

      /* LOGO TERBANG */
      animation: floatLogo 3s ease-in-out infinite;
  }

  @keyframes floatLogo {
      0%   { transform: translateY(0); }
      50%  { transform: translateY(-10px); }
      100% { transform: translateY(0); }
  }

  h1 {
      font-size: 32px;
      margin-bottom: 10px;
      background: linear-gradient(90deg, #000000ff, #555555ff 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
  }

  .text-gradient {
      font-size: 18px;
      font-weight: 600;
      line-height: 1.4;
      margin-bottom: 30px;
      background: linear-gradient(90deg, #cccccc, #ffffff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
  }

  a.button {
      display: inline-block;
      text-decoration: none;
      background: #555;
      color: #fff;
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: 600;
      box-shadow: 0 3px 10px rgba(0,0,0,0.2);
      transition: 0.3s;
      margin-top: 20px;
      animation: butIn 1.8s ease forwards;
      opacity: 0;
  }

  @keyframes butIn {
      0%   { opacity: 0; transform: translateY(20px); }
      100% { opacity: 1; transform: translateY(0); }
  }

  a.button:hover {
      background: #777;
      transform: scale(1.05);
      box-shadow: 0 4px 14px rgba(14, 14, 14, 0.3);
  }
</style>
</head>

<body id="pageBody">

<div class="container">
  <img src="admin/g.png" alt="Logo Resti Wedangan">
  <h1>Selamat Datang</h1>
  <p class="text-gradient">di Sistem Kasir <strong>Resti Wedangan</strong><br>Tempat nikmatnya rasa dan pelayanan hangat</p>

  <a href="#" onclick="goLogin()" class="button">Masuk ke Login Admin</a>
</div>

<script>
function goLogin() {
    document.getElementById("pageBody").classList.add("fadeOut");

    setTimeout(function() {
        window.location.href = "login_admin.php";
    }, 500); // waktu fade-out
}
</script>

</body>
</html>
