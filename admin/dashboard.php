<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../index.php");
  exit;
}

include '../koneksi.php';
include 'sidebar.php';

// ==== AJAX ====
if (isset($_GET['ajax'])) {
  $search = $_GET['search'] ?? '';
  $filter = $_GET['filter'] ?? '';
  $where = "is_deleted = 0";

  if ($search != '') {
    $search = mysqli_real_escape_string($koneksi, $search);
    $where .= " AND nama_produk LIKE '%$search%'";
  }

  if ($filter != '') {
    $f = mysqli_real_escape_string($koneksi, $filter);
    $where .= " AND kategori = '$f'";
  }

  $q = mysqli_query($koneksi, "SELECT * FROM produk WHERE $where ORDER BY id_produk DESC");
  if (mysqli_num_rows($q) > 0) {
    while ($p = mysqli_fetch_assoc($q)) {
      $foto = !empty($p['foto']) && file_exists("uploads/" . $p['foto'])
        ? "uploads/" . $p['foto']
        : "uploads/default.png";

      echo "
      <div class='produk-item'>
        <div class='card card-produk'>
          <img src='$foto'>
          <div class='card-body'>
            <h6 class='card-title text-truncate'>".$p['nama_produk']."</h6>
            <small class='text-muted d-block'>".$p['kategori']."</small>
            <p class='harga'>Rp ".number_format($p['harga'],0,',','.')."</p>
            <p class='stok'>Stok: ".$p['stok']."</p>
          </div>
        </div>
      </div>";
    }
  } else {
    echo "<div class='text-center text-muted w-100'><p>Tidak ada produk ditemukan.</p></div>";
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
/* === BACKGROUND DARK MODE === */
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

body {
  display: flex;
  flex-direction: column;
  background: linear-gradient(to bottom, #252525ff, #464646ff) !important;
  background-attachment: fixed;
  color: white;
}

/* CONTENT AREA */
.content {
  flex: 1;
  margin-left:260px;
  padding:20px;
  display:flex;
  flex-direction:column;
  align-items:center;
}

/* Jam Operasional */
#jamBukaBox {
  background:#2a2a2a;
  color:#fff;
  padding:20px;
  border-radius:14px;
  width:100%;
  max-width:600px;
  text-align:center;
  margin-bottom:20px;
  box-shadow:0 3px 10px rgba(0,0,0,0.6);
}

#jamDigital { 
  font-size:26px; 
  font-weight:bold; 
  margin-top:5px; 
}

/* FILTER */
.filter-menu {
  background:#2a2a2a;
  padding:10px 15px;
  border-radius:12px;
  display:flex;
  gap:20px;
  margin-bottom:20px;
  color:white;
  box-shadow:0 2px 6px rgba(0,0,0,0.6);
}

.search-bar {
  max-width:350px;
  width:100%;
  position:relative;
  margin-bottom:20px;
}

.search-bar input {
  background:white;
  border:none;
  color:white;
  padding-right:35px;           /* ruang untuk X */
}

.search-bar input::placeholder {
  color:#bbb;
}

#clearSearch {
  position:absolute;
  right:12px;                    /* 🔥 nempel kanan */
  top:50%;                       /* 🔥 di tengah */
  transform:translateY(-50%); 
  background:transparent;
  border:none;
  font-size:18px;
  color:white;
  cursor:pointer;
  display:none;
}


/* CARD PRODUK */
.card-produk {
  width:180px;
  border:none;
  border-radius:12px;
  background:#ffffff;
  overflow:hidden;
  box-shadow:0 2px 7px rgba(255,255,255,0.1);
  transition:.2s;
}

.card-produk:hover { transform:scale(1.05); }

.card-produk img {
  width:100%;
  height:180px;
  object-fit:cover;
}

/* GRID */
#produkContainer {
  display:flex;
  flex-wrap:wrap;
  gap:18px;
  justify-content:center;
  width:100%;
}

footer {

  padding:18px;
  text-align:center;
  background:#1f1f1f;
  border-top:2px solid #333;
  font-weight:600;
  color:white;
  position: fixed;     /* 🔥 baru */
  bottom: 0;           /* 🔥 biar nempel bawah */
  left: 260px;         /* 🔥 biar sejajar sidebar */
  width: calc(100% - 260px); /* 🔥 biar melebar tapi tidak nutup sidebar */
  z-index: 100;
}


</style>

</style>
</head>

<body>

<div class="content">

  <div id="jamBukaBox">
    <strong>Jam Operasional</strong>
    <div>Buka 08:00 — Tutup 22:00</div>
    <div id="jamDigital">00:00:00</div>
  </div>

  <h3 class="mb-3 text-center text-white">Daftar Produk</h3>

  <div class="filter-menu">
    <label><input type="radio" name="filter" value="" checked> Semua</label>
    <label><input type="radio" name="filter" value="Dingin"> Dingin</label>
    <label><input type="radio" name="filter" value="Panas"> Panas</label>
  </div>

  <div class="search-bar">
    <input type="text" id="searchInput" class="form-control" placeholder="Cari produk...">
    <button id="clearSearch">×</button>
  </div>

  <div id="produkContainer">
    <?php
    $qProduk = mysqli_query($koneksi, "SELECT * FROM produk WHERE is_deleted = 0 ORDER BY id_produk DESC");
    while ($p = mysqli_fetch_assoc($qProduk)):
      $foto = !empty($p['foto']) && file_exists("uploads/".$p['foto'])
        ? "uploads/".$p['foto']
        : "uploads/default.png";
    ?>
    <div class="produk-item">
      <div class="card card-produk">
        <img src="<?= $foto ?>">
        <div class="card-body">
          <h6 class="text-truncate"><?= $p['nama_produk'] ?></h6>
          <small class="text-muted d-block"><?= $p['kategori'] ?></small>
          <p class="harga">Rp <?= number_format($p['harga'],0,',','.') ?></p>
          <p class="stok">Stok: <?= $p['stok'] ?></p>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

</div>

<footer>
  © 2025 <strong>Kasir Wedangan Nusantara</strong> — Dashboard Admin
</footer>

<script>
function loadProduk(search='', filter='') {
  fetch(`?ajax=1&search=${encodeURIComponent(search)}&filter=${filter}`)
    .then(r => r.text())
    .then(d => produkContainer.innerHTML = d);
}

const searchInput = document.getElementById('searchInput');
const clearSearch = document.getElementById('clearSearch');
const produkContainer = document.getElementById('produkContainer');

searchInput.addEventListener('input', function() {
  clearSearch.style.display = this.value ? 'block' : 'none';
  const filter = document.querySelector('input[name="filter"]:checked').value;
  loadProduk(this.value.trim(), filter);
});

clearSearch.addEventListener('click', function() {
  searchInput.value = '';
  this.style.display = 'none';
  const filter = document.querySelector('input[name="filter"]:checked').value;
  loadProduk('', filter);
});

document.querySelectorAll('input[name="filter"]').forEach(r => {
  r.addEventListener('change', function() {
    loadProduk(searchInput.value.trim(), this.value);
  });
});

document.addEventListener('DOMContentLoaded', () => loadProduk());

// JAM DIGITAL
function updateClock() {
  const now = new Date();
  document.getElementById('jamDigital').textContent =
    now.toLocaleTimeString('id-ID');
}
setInterval(updateClock, 1000); updateClock();
</script>

</body>
</html>
