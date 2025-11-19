<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<div class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white position-fixed shadow-lg"
     style="width: 250px; height: 100vh; left: 0; top: 0;">
  <!-- Judul / Brand -->
  <div class="text-center mb-4">
    <img src="http://localhost/KasirWedanganNusantara/admin/g.png" alt=""
         style="
           width:130px;
           height:auto;
           margin-bottom:10px;
           filter: drop-shadow(0 0 8px rgba(255,215,0,0.5));
         ">
    <h5 class="mt-2 mb-0 fw-bold text-light" style="font-size:18px;">Resti Wedangan</h5>
  </div>

  <!-- Menu Navigasi -->
  <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
      <a href="http://localhost/KasirWedanganNusantara/admin/dashboard.php"
         class="nav-link text-white <?= $current_page == 'dashboard.php' ? 'active bg-secondary' : ''; ?>">
        <i class="bi bi-house-door"></i> Dashboard
      </a>
    </li>

    <li class="nav-item">
      <a href="http://localhost/KasirWedanganNusantara/admin/produk/kelola_produk.php"
         class="nav-link text-white <?= $current_page == 'kelola_produk.php' ? 'active bg-secondary' : ''; ?>">
       <i class="bi bi-basket"></i> Produk/Menu
      </a>
    </li>

    <li class="nav-item">
      <a href="http://localhost/KasirWedanganNusantara/admin/transaksi.php"
         class="nav-link text-white <?= $current_page == 'transaksi.php' ? 'active bg-secondary' : ''; ?>">
        <i class="bi bi-cash-coin"></i> Transaksi
      </a>
    </li>

    <li class="nav-item">
      <a href="http://localhost/KasirWedanganNusantara/admin/laporan.php"
         class="nav-link text-white <?= $current_page == 'laporan.php' ? 'active bg-secondary' : ''; ?>">
        <i class="bi bi-journal-text"></i> Laporan
      </a>
    </li>

    <li class="nav-item">
      <a href="http://localhost/KasirWedanganNusantara/admin/laporan.php"
         class="nav-link text-white <?= $current_page == 'laporan.php' ? 'active bg-secondary' : ''; ?>">
        <i class="bi bi-journal-text"></i> grafik
      </a>
    </li>
  </ul>

  <!--  & Logout di bawah -->
   <a href="../logout.php" class="btn btn-outline-danger w-100">
      <i class="bi bi-box-arrow-right me-2"></i> Logout
    </a>
  </div>
</div>
