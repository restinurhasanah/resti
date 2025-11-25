<?php
session_start();
if (empty($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    header("Location: ../index.php");
    exit; 
}
include '../../koneksi.php';
include '../sidebar.php';
$msg = '';

if (isset($_POST['simpan'])) {
  $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
  $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
  $harga = (int)$_POST['harga'];
  $stok = (int)$_POST['stok'];
  $foto = '';

  $cek_duplikat = mysqli_query($koneksi, "
      SELECT * FROM produk 
      WHERE LOWER(nama_produk) = LOWER('$nama') 
      AND LOWER(kategori) = LOWER('$kategori')
  ");
  if (mysqli_num_rows($cek_duplikat) > 0) {
    $msg = "<div class='alert alert-danger'>❌ Produk dengan nama dan kategori yang sama sudah ada!</div>";
  } 
  elseif ($harga < 1000) {
    $msg = "<div class='alert alert-danger'>❌ Harga produk tidak boleh kurang dari 1000!</div>";
  } 
  elseif ($stok <= 0) {
    $msg = "<div class='alert alert-danger'>❌ Stok produk harus lebih dari 0!</div>";
  } 
  else {
    if (!empty($_FILES['foto']['name'])) {
      $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
      $foto = time() . '_' . uniqid() . '.' . $ext;
      move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto);
    }
    $insert = mysqli_query($koneksi, "INSERT INTO produk (nama_produk, kategori, harga, stok, foto) 
                                      VALUES ('$nama', '$kategori', '$harga', '$stok', '$foto')");
    if ($insert) {
      echo "<script>alert('✅ Produk berhasil ditambahkan!'); window.location='kelola_produk.php';</script>";
      exit;
    } else {
      $msg = "<div class='alert alert-danger'>❌ Gagal menambahkan produk: " . mysqli_error($koneksi) . "</div>";
    }
  }
}

$qKategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tambah Produk</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to bottom, #252525ff, #464646ff) !important;
      background-attachment: fixed;
      color: white;
      min-height: 100vh;
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

    .btn-success {
      background: #28a745;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      color: white;
    }

    .btn-success:hover {
      background: #218838;
    }

    .btn-batal {
      background: #6c757d;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      color: white;
    }

    .btn-batal:hover {
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

    <div class="card">
      <div class="card-header">
        Form Tambah Produk Baru
      </div>
      <div class="card-body">
        <?php if ($msg != '') echo $msg; ?>

        <form method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Nama Produk</label>
            <input name="nama_produk" class="form-control" type="text" placeholder="Masukkan nama produk" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="kategori" class="form-select" required>
              <option value="">-- Pilih kategori --</option>
              <?php while ($k = mysqli_fetch_assoc($qKategori)): ?>
                <option value="<?= $k['nama_kategori'] ?>"><?= $k['nama_kategori'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Harga</label>
            <input name="harga" type="number" class="form-control" placeholder="Masukkan harga" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Stok</label>
            <input name="stok" type="number" class="form-control" placeholder="Masukkan stok" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Foto Produk</label>
            <input name="foto" type="file" class="form-control" accept="image/*">
          </div>

          <div class="d-flex gap-2">
            <button name="simpan" class="btn btn-success"> Simpan</button> 
            <a href="kelola_produk.php" class="btn btn-batal"> Batal</a>
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