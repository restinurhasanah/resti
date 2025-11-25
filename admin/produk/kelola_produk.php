<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../index.php");
  exit;
}

include '../../koneksi.php';
include '../sidebar.php';


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
      <div class='produk-item'>
        <div class='card card-produk h-100'>
          <img src='".htmlspecialchars($foto)."' class='card-img-top'/>
          <div class='card-body text-center'>
            <h6 class='card-title text-truncate'>".htmlspecialchars($p['nama_produk'])."</h6>
            <small class='text-muted'>".htmlspecialchars($p['kategori'])."</small>
            <p class='harga2'>Rp ".number_format($p['harga'],0,',','.')."</p>
            <p class='stok2'>Stok: ".htmlspecialchars($p['stok'])."</p>

            <div class='d-flex justify-content-center gap-2 mt-2'>
              <a href='edit_produk.php?id=".$p['id_produk']."' class='btn-edit'>✏️ Edit</a>
              <a href='hapus_produk.php?id=".$p['id_produk']."' class='btn-hapus' onclick=\"return confirm('Yakin ingin hapus produk ini?')\">🗑️ Hapus</a>
            </div>
          </div>
        </div>
      </div>";
    endwhile;
  else:
    
    echo "<div class='text-center text-muted mt-4' style='width:100%'><p>⚠️ Belum ada produk ditemukan.</p></div>";
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
body {
    height:100vh;
    display: flex;
    flex-direction: column;
    background: linear-gradient(to bottom, #252525ff, #464646ff) !important;
    background-attachment: fixed;
    color: white;
    margin-bottom:30px;
}

.content {
  margin-left: 260px;
  padding: 25px;
  flex: 1; 
}

h4 { font-weight: 700; color: #fff; }

.btn-success {
  border-radius: 10px;
  font-weight: 600;
}

.search-bar { 
  max-width: 350px; 
  position: relative; 
  margin-bottom: 20px; 
}
#searchInput {
  padding-right: 35px; 
  order-radius: 10px; 
}
#clearSearch {
  position: absolute;
  right: 10px;
  top: 6px;
  font-size: 18px;
  cursor: pointer;
  border: none;
  background: transparent;
  display: none;
}

.card-produk {
  width: 210px;
  border: none;
  border-radius: 14px;
  background: #f5f5f5;
  overflow: hidden;
  box-shadow: 0px 3px 12px rgba(0,0,0,0.18);
  transition: .25s ease-in-out;
  margin-left: auto;
  margin-right: auto;
}
.card-produk:hover {
  transform: translateY(-6px);
  box-shadow: 0px 5px 16px rgba(0,0,0,0.25);
}

.card-produk img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  background: #eee;
}

.card-title { 
  font-weight: 700; 
  color: #222; 
}
.harga2 {
   font-size: 17px; 
   font-weight: bold; 
   margin-top: 8px;
   }
.stok2 {
   font-size: 13px; 
   color: #444; 
  }

.btn-edit, .btn-hapus {
  padding: 6px 10px;
  border-radius: 8px;
  color: white;
  text-decoration: none;
  font-weight: 600;
}
.btn-edit { 
  background: #ffb02e; 
}
.btn-edit:hover { 
  background: #e28c00; 
}

.btn-hapus { background: #d9534f; }
.btn-hapus:hover { background: #b52a22; }

#produkContainer {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  justify-content: center !important;
}

.produk-item {
  flex: 0 0 auto;
  display: flex;
  justify-content: center;
}

.footer-dashboard {
  width: calc(100% - 260px);
  margin-left: 260px;
  background: #262626;
  color: white;
  text-align: center;
  padding: 5px 0;
  border-top: 2px solid #333;
  font-weight: 600;
  position: fixed;
  bottom: 0;
  z-index: 99;
}
</style>
</head>

<body>

<div class="content">
  <div class="d-flex justify-content-between mb-4 ">
    <h4 style="text-align:center;">Manajemen Produk</h4>
    <a href="tambah_produk.php" class="btn btn-success">+ Tambah Produk</a>
  </div>

  <div class="search-bar">
    <input type="text" id="searchInput" class="form-control" placeholder="Cari nama produk...">
    <button id="clearSearch">×</button>
  </div>

  <div id="produkContainer">
    <?php
    $q = mysqli_query($koneksi, "SELECT * FROM produk WHERE is_deleted = 0 ORDER BY id_produk DESC");
    while ($p = mysqli_fetch_assoc($q)):
      $path_file = '../uploads/' . $p['foto'];
      $foto = (!empty($p['foto']) && file_exists($path_file)) ? $path_file : 'https://via.placeholder.com/300x160?text=No+Image';
    ?>
      <div class="produk-item">
        <div class="card card-produk">
          <img src="<?= $foto ?>">
          <div class="card-body text-center">
            <h6 class="card-title text-truncate"><?= $p['nama_produk'] ?></h6>
            <small class="text-muted"><?= $p['kategori'] ?></small>
            <p class="harga2">Rp <?= number_format($p['harga'],0,',','.') ?></p>
            <p class="stok2">Stok: <?= $p['stok'] ?></p>

            <div class="d-flex justify-content-center gap-2 mt-2">
              <a href="edit_produk.php?id=<?= $p['id_produk'] ?>" class="btn-edit">✏️ Edit</a>
              <a href="hapus_produk.php?id=<?= $p['id_produk'] ?>" class="btn-hapus" onclick="return confirm('Yakin ingin hapus produk ini?')">🗑️ Hapus</a>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<footer class="footer-dashboard">
  <p>© 2025 <strong>Kasir Wedangan Nusantara</strong></p>
</footer>

<script>
const searchInput = document.getElementById('searchInput');
const clearSearch = document.getElementById('clearSearch');
const produkContainer = document.getElementById('produkContainer');
let debounceTimer;

// ==== FUNGSI LOAD PRODUK ====
function loadProduk(query='') {
  fetch(`kelola_produk.php?ajax=1&search=${encodeURIComponent(query)}`)
    .then(res => res.text())
    .then(data => produkContainer.innerHTML = data);
}

// ==== PERBAIKAN SEARCH (langsung reset jika kosong) ====
searchInput.addEventListener('input', function() {
  const value = this.value.trim();

  // Jika input kosong → langsung reset cepat
  if (value === "") {
    clearTimeout(debounceTimer);
    clearSearch.style.display = "none";
    loadProduk(""); // langsung tampilkan semua produk
    return;
  }

  // Jika ada teks → debounce
  clearSearch.style.display = "block";
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    loadProduk(value);
  }, 180);
});

// Tombol clear (X)
clearSearch.addEventListener('click', function() {
  searchInput.value = '';
  clearSearch.style.display = 'none';
  loadProduk('');
});
</script>

</body>
</html>
// ...existing code...