<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../index.php");
  exit;
}

include '../koneksi.php';
include 'sidebar.php';

// ==== BAGIAN AJAX TANPA FILE TERPISAH ====
if (isset($_GET['ajax'])) {
  $search = $_GET['search'] ?? '';
  $where = "is_deleted = 0";
  if ($search != '') {
    $search = mysqli_real_escape_string($koneksi, $search);
    $where .= " AND nama_produk LIKE '%$search%'";
  }

  $q = mysqli_query($koneksi, "SELECT * FROM produk WHERE $where ORDER BY id_produk DESC");
  if (mysqli_num_rows($q) > 0) {
    while ($p = mysqli_fetch_assoc($q)) {
      $foto = !empty($p['foto']) && file_exists("uploads/" . $p['foto'])
        ? "uploads/" . $p['foto']
        : "uploads/default.png";
      echo "
      <div class='produk-item'>
        <div class='card card-produk h-100'>
          <img src='$foto' alt='".htmlspecialchars($p['nama_produk'])."'>
          <div class='card-body'>
            <h6 class='card-title text-truncate'>".htmlspecialchars($p['nama_produk'])."</h6>
            <small class='text-muted d-block'>".htmlspecialchars($p['kategori'])."</small>
            <p class='harga'>Rp ".number_format($p['harga'],0,',','.')."</p>
            <p class='stok'>Stok: ".$p['stok']."</p>
          </div>
        </div>
      </div>";
    }
  } else {
    echo "<div class='col-12 text-center text-muted'><p>Tidak ada produk ditemukan.</p></div>";
  }
  exit;
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; }
    .content { margin-left:260px; padding:20px; }

    .search-bar { max-width: 350px; margin-bottom: 25px; position: relative; }
    .search-bar input { padding-right: 35px; }
    #clearSearch {
      border: none;
      background: transparent;
      position: absolute;
      right: 10px;
      top: 6px;
      font-size: 18px;
      cursor: pointer;
      color: #999;
      display: none;
    }

    /* ===== Card Produk ===== */
    .card-produk {
      width: 220px;
      margin:0;
      border: none;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: all .2s ease;
      background: #fff;
      display: flex;
      flex-direction: column;
    }

    .card-produk img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      display: block;
      background: #f9f9f9;
    }

    .card-body { padding:12px; text-align:center; }
    .harga { font-weight:600; color:#000; margin-top:6px; }
    .stok { font-size:13px; color:#555; }

    /* ===== Grid Produk ===== */
    #produkContainer {
      display:flex;
      flex-wrap:wrap;
      gap:15px;
      justify-content:flex-start;
    }

    .produk-item {
      flex: 0 0 auto;
      display:flex;
      justify-content:center;
    }
  </style>
</head>

<body>
  <div class="content">
    <h3 class="mb-4">Daftar Produk</h3>

    <!-- Input Pencarian -->
    <div class="search-bar">
      <input type="text" id="searchInput" class="form-control" placeholder="Cari produk...">
      <button id="clearSearch">×</button>
    </div>

    <!-- Hasil Produk -->
    <div id="produkContainer">
      <?php
      $qProduk = mysqli_query($koneksi, "SELECT * FROM produk WHERE is_deleted = 0 ORDER BY id_produk DESC");
      if (mysqli_num_rows($qProduk) > 0):
        while ($p = mysqli_fetch_assoc($qProduk)):
          $foto = !empty($p['foto']) && file_exists("uploads/" . $p['foto'])
            ? "uploads/" . $p['foto']
            : "uploads/default.png";
      ?>
        <div class="produk-item">
          <div class="card card-produk h-100">
            <img src="<?= $foto ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>">
            <div class="card-body">
              <h6 class="card-title text-truncate"><?= htmlspecialchars($p['nama_produk']) ?></h6>
              <small class="text-muted d-block"><?= htmlspecialchars($p['kategori']) ?></small>
              <p class="harga">Rp <?= number_format($p['harga'],0,',','.') ?></p>
              <p class="stok">Stok: <?= $p['stok'] ?></p>
            </div>
          </div>
        </div>
      <?php endwhile; else: ?>
        <div class="col-12 text-center text-muted">
          <p>Tidak ada produk ditemukan.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    const produkContainer = document.getElementById('produkContainer');
    let debounceTimer;

    function loadProduk(query='') {
      fetch(`?ajax=1&search=${encodeURIComponent(query)}`)
        .then(res => res.text())
        .then(data => produkContainer.innerHTML = data);
    }

    searchInput.addEventListener('input', function() {
      clearSearch.style.display = this.value ? 'block' : 'none';
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        loadProduk(this.value.trim());
      }, 200);
    });

    clearSearch.addEventListener('click', function() {
      searchInput.value = '';
      clearSearch.style.display = 'none';
      loadProduk();
    });

    document.addEventListener('DOMContentLoaded', () => {
      loadProduk();
    });
  </script>
</body>
</html>
