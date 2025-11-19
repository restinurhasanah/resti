<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../index.php");
  exit;
}

include '../../koneksi.php';
include '../sidebar.php';

// ==== BAGIAN AJAX TANPA FILE TERPISAH ====
if (isset($_GET['ajax'])) {
  $search = $_GET['search'] ?? '';
  $where = "is_deleted = 0";
  if ($search != '') {
    $search = mysqli_real_escape_string($koneksi, $search);
    $where .= " AND nama_produk LIKE '%$search%'";
  }
  $q = mysqli_query($koneksi, "SELECT * FROM produk WHERE $where ORDER BY id_produk DESC");

  if (mysqli_num_rows($q) > 0):
    while ($p = mysqli_fetch_assoc($q)):
      $path_file = '../uploads/' . $p['foto'];
      $foto = (!empty($p['foto']) && file_exists($path_file))
        ? $path_file
        : 'https://via.placeholder.com/300x160?text=No+Image';
      echo "
      <div class='col-6 col-md-4 col-lg-3 produk-item'>
        <div class='card card-produk h-100'>
          <img src='".htmlspecialchars($foto)."' class='card-img-top' alt='".htmlspecialchars($p['nama_produk'])."'/>
          <div class='card-body text-center'>
            <h6 class='card-title text-truncate'>".htmlspecialchars($p['nama_produk'])."</h6>
            <small class='text-muted'>".htmlspecialchars($p['kategori'])."</small>
            <p class='fw-bold mt-2 mb-1 text-dark'>Rp ".number_format($p['harga'],0,',','.')."</p>
            <p class='small text-secondary mb-3'>Stok: ".htmlspecialchars($p['stok'])."</p>
            <div class='d-flex justify-content-center gap-2'>
              <a href='edit_produk.php?id=".$p['id_produk']."' class='btn btn-warning btn-sm'>✏️ Edit</a>
              <a href='hapus_produk.php?id=".$p['id_produk']."' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin ingin hapus produk ini?')\">🗑️ Hapus</a>
            </div>
          </div>
        </div>
      </div>
      ";
    endwhile;
  else:
    echo "<div class='col-12 text-center text-muted mt-4'><p>⚠️ Belum ada produk ditemukan.</p></div>";
  endif;
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🛍️ Kelola Produk</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .content { margin-left: 260px; padding: 25px; }
    .search-bar { max-width: 350px; margin-bottom: 25px; position: relative; }
    #clearSearch {
      position: absolute;
      right: 10px;
      top: 6px;
      font-size: 18px;
      cursor: pointer;
      border: none;
      background: transparent;
      color: #999;
      display: none;
    }

    /* ===== Card Produk ===== */
    .card-produk {
        width: 220px;
        margin: 0;
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
    height: 220px;       /* tinggi tetap */
    object-fit: cover;   /* gambar mengisi card penuh */
    display: block;
    background: #f9f9f9;
}


    .card-body { text-align: center; }
    .card-title { font-weight: 600; }

   #produkContainer {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;             /* jarak antar card */
    justify-content: flex-start;
}

.produk-item {
    flex: 0 0 auto;
    display: flex;
    justify-content: center;
}

  </style>
</head>

<body>
  <div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">Manajemen Produk</h4>
      <a href="tambah_produk.php" class="btn btn-success">+ Tambah Produk</a>
    </div>

    <!-- 🔍 Pencarian Otomatis -->
    <div class="search-bar">
      <input type="text" id="searchInput" class="form-control" placeholder="Cari nama produk..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      <button id="clearSearch">×</button>
    </div>

    <!-- Daftar Produk -->
    <div id="produkContainer">
      <?php
      $q = mysqli_query($koneksi, "SELECT * FROM produk WHERE is_deleted = 0 ORDER BY id_produk DESC");
      if (mysqli_num_rows($q) > 0):
        while ($p = mysqli_fetch_assoc($q)):
          $path_file = '../uploads/' . $p['foto'];
          $foto = (!empty($p['foto']) && file_exists($path_file)) ? $path_file : 'https://via.placeholder.com/300x160?text=No+Image';
      ?>
          <div class="produk-item">
            <div class="card card-produk h-100">
              <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>">
              <div class="card-body text-center">
                <h6 class="card-title text-truncate"><?= htmlspecialchars($p['nama_produk']) ?></h6>
                <small class="text-muted"><?= htmlspecialchars($p['kategori']) ?></small>
                <p class="fw-bold mt-2 mb-1 text-dark">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
                <p class="small text-secondary mb-3">Stok: <?= htmlspecialchars($p['stok']) ?></p>
                <div class="d-flex justify-content-center gap-2">
                  <a href="edit_produk.php?id=<?= $p['id_produk'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                  <a href="hapus_produk.php?id=<?= $p['id_produk'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus produk ini?')">🗑️ Hapus</a>
                </div>
              </div>
            </div>
          </div>
      <?php
        endwhile;
      else:
        echo "<div class='col-12 text-center text-muted mt-4'><p>⚠️ Belum ada produk ditemukan.</p></div>";
      endif;
      ?>
    </div>
  </div>

  <script>
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    const produkContainer = document.getElementById('produkContainer');
    let debounceTimer;

    function loadProduk(query='') {
      fetch(`kelola_produk.php?ajax=1&search=${encodeURIComponent(query)}`)
        .then(res => res.text())
        .then(data => produkContainer.innerHTML = data);
    }

    searchInput.addEventListener('input', function() {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        loadProduk(this.value.trim());
        clearSearch.style.display = this.value ? 'block' : 'none';
      }, 200);
    });

    clearSearch.addEventListener('click', function() {
      searchInput.value = '';
      clearSearch.style.display = 'none';
      loadProduk();
    });

    // ==== NOTIFIKASI DESKTOP ====
    document.addEventListener('DOMContentLoaded', () => {
      <?php if (isset($_SESSION['pesan'])): ?>
        const pesan = <?= json_encode($_SESSION['pesan']) ?>;
        if (Notification.permission === "granted") {
          new Notification(pesan.text);
        } else if (Notification.permission !== "denied") {
          Notification.requestPermission().then(permission => {
            if (permission === "granted") new Notification(pesan.text);
          });
        }
      <?php unset($_SESSION['pesan']); endif; ?>
    });
  </script>
</body>
</html>
