<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../index.php");
  exit;
}

include '../../koneksi.php';
include '../sidebar.php';

// --- Ambil data produk berdasarkan ID ---
$id_produk = (int)($_GET['id'] ?? 0);
$q = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = $id_produk");
$p = mysqli_fetch_assoc($q);

if (!$p) {
  echo "Produk tidak ditemukan";
  exit;
}

$pesanError = "";


if (isset($_POST['simpan'])) {
  $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama_produk']));
  $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
  $harga    = (int)$_POST['harga'];
  $stok     = (int)$_POST['stok'];
  $foto     = $p['foto'];

 
  if ($stok < 1) {
    $pesanError = "❌ Stok tidak boleh kurang dari 1!";
  }

 
  elseif ($harga < 1000) {
    $pesanError = "❌ Harga tidak boleh kurang dari Rp 1.000!";
  }

  else {
    $cekDuplikat = mysqli_query($koneksi, "
      SELECT id_produk FROM produk 
      WHERE LOWER(nama_produk) = LOWER('$nama') 
      AND LOWER(kategori) = LOWER('$kategori')
      AND id_produk != $id_produk
    ");
    if (mysqli_num_rows($cekDuplikat) > 0) {
      $pesanError = "❌ Produk dengan nama dan kategori yang sama sudah ada!";
    }
  }

  
  if (empty($pesanError)) {

    
    if (!empty($_FILES['foto']['name'])) {
      $folderUpload = "../uploads/";
      $ext  = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
      $namaBaru = time() . '_' . uniqid() . '.' . $ext;

      
      if (!empty($p['foto']) && file_exists($folderUpload . $p['foto'])) {
        unlink($folderUpload . $p['foto']);
      }

      
      move_uploaded_file($_FILES['foto']['tmp_name'], $folderUpload . $namaBaru);
      $foto = $namaBaru;
    }

    // Simpan ke database
    mysqli_query($koneksi, "
      UPDATE produk 
      SET nama_produk='$nama', kategori='$kategori', harga=$harga, stok=$stok, foto='$foto'
      WHERE id_produk=$id_produk
    ");

    echo "<script>alert('✅ Produk berhasil diperbarui!'); window.location='kelola_produk.php';</script>";
    exit;
  }
}

// ==== GET KATEGORI DARI DATABASE ====
$qKategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Produk</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(to bottom, #252525ff, #464646ff) !important;
      background-attachment: fixed;
      color: white;
      margin-bottom: 30px;
    }

    .content {
      margin-left: 260px;
      padding: 25px;
    }

    h4 {
      font-weight: 700;
      color: #fff;
      margin-bottom: 25px;
    }

    .card {
      background: #f5f5f5;
      border: none;
      border-radius: 10px;
      box-shadow: 0px 2px 8px rgba(0,0,0,0.3);
      color: #222;
      margin-bottom: 25px;
    }

    .card-header {
      background: #e8e8e8;
      border-bottom: 1px solid #ddd;
      border-radius: 10px 10px 0 0;
      padding: 15px 20px;
      font-weight: 600;
      color: #222;
    }

    .card-body {
      padding: 20px;
    }

    .form-control, .form-select {
      background: #fff;
      border: 1px solid #ddd;
      color: #222;
      border-radius: 6px;
    }

    .form-control:focus, .form-select:focus {
      background: #fff;
      color: #222;
      border-color: #ffb02e;
      box-shadow: 0 0 0 0.2rem rgba(255, 176, 46, 0.25);
    }

    .form-control::placeholder {
      color: #999;
    }

    .form-label {
      font-weight: 600;
      color: #222;
      margin-bottom: 8px;
    }

    .preview-img {
      width: 180px;
      height: 180px;
      object-fit: cover;
      border: 1px solid #ddd;
      border-radius: 10px;
      background: #fff;
      margin-bottom: 10px;
    }

    .btn-warning {
      background: #ffc107;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      color: #222;
    }

    .btn-warning:hover {
      background: #e0a800;
      color: #222;
    }

    .btn-dark {
      background: #6c757d;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      color: white;
    }

    .btn-dark:hover {
      background: #5a6268;
      color: white;
    }

    .alert {
      margin-bottom: 20px;
    }

    .footer-dashboard {
      width: calc(100% - 260px);
      margin-left: 260px;
      background: #262626;
      color: white;
      text-align: center;
      padding: 10px 0;
      border-top: 1px solid #333;
      font-weight: 600;
      position: fixed;
      bottom: 0;
      z-index: 99;
    }
  </style>
</head>

<body>
  <div class="content">
    <h4> Edit Produk</h4>

    <div class="card">
      <div class="card-header">
        Form Edit Produk
      </div>
      <div class="card-body">
        <?php if (!empty($pesanError)): ?>
          <div class="alert alert-danger"><?= $pesanError ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($p['nama_produk']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="kategori" class="form-select" required>
              <option value="">-- Pilih kategori --</option>
              <?php while ($k = mysqli_fetch_assoc($qKategori)): ?>
                <option value="<?= $k['nama_kategori'] ?>" <?= $p['kategori'] == $k['nama_kategori'] ? 'selected' : '' ?>>
                  <?= $k['nama_kategori'] ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Harga (Rp)</label>
              <input type="number" name="harga" class="form-control" value="<?= $p['harga'] ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Stok</label>
              <input type="number" name="stok" class="form-control" value="<?= $p['stok'] ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Foto Produk</label><br>
            <?php
              $pathFoto = "../uploads/" . $p['foto'];
              if (!empty($p['foto']) && file_exists($pathFoto)) {
                echo "<img src='$pathFoto' class='preview-img' alt='Foto Produk'>";
              } else {
                echo "<img src='../uploads/default.png' class='preview-img' alt='Default'>";
              }
            ?>
            <input type="file" name="foto" class="form-control mt-2" accept="image/*">
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" name="simpan" class="btn btn-warning"> Simpan Perubahan</button>
            <a href="kelola_produk.php" class="btn btn-dark"> Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <footer class="footer-dashboard">
    <p>© 2025 <strong>Kasir Wedangan Nusantara</strong></p>
  </footer>
</body>
</html>