<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../welcome.php");
  exit;
}
include '../../koneksi.php';
$msg = '';

// Jika form disubmit
if (isset($_POST['simpan'])) {
  $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
  $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
  $harga = (int)$_POST['harga'];
  $stok = (int)$_POST['stok'];
  $foto = '';

  // === Validasi Nama + Kategori Sama ===
  $cek_duplikat = mysqli_query($koneksi, "
      SELECT * FROM produk 
      WHERE LOWER(nama_produk) = LOWER('$nama') 
      AND LOWER(kategori) = LOWER('$kategori')
  ");
  if (mysqli_num_rows($cek_duplikat) > 0) {
    $msg = "<div class='alert alert-danger'>❌ Produk dengan nama dan kategori yang sama sudah ada!</div>";
  } 
  // === Validasi Harga Minimal 1000 ===
  elseif ($harga < 1000) {
    $msg = "<div class='alert alert-danger'>❌ Harga produk tidak boleh kurang dari 1000!</div>";
  } 
  // === Validasi Stok Harus Lebih Dari 0 ===
  elseif ($stok <= 0) {
    $msg = "<div class='alert alert-danger'>❌ Stok produk harus lebih dari 0!</div>";
  } 
  else {
    // Upload foto jika ada
    if (!empty($_FILES['foto']['name'])) {
      $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
      $foto = time() . '_' . uniqid() . '.' . $ext;
      move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto);
    }

    // Simpan data produk
    $insert = mysqli_query($koneksi, "INSERT INTO produk (nama_produk, kategori, harga, stok, foto) 
                                      VALUES ('$nama', '$kategori', '$harga', '$stok', '$foto')");
    if ($insert) {
      // Simpan pesan sukses ke session
      $_SESSION['pesan'] = [
        'type' => 'success',
        'text' => '✅ Produk berhasil ditambahkan!'
      ];

      // Pindah otomatis ke halaman kelola produk
      header("Location: kelola_produk.php");
      exit;
    } else {
      $msg = "<div class='alert alert-danger'>❌ Gagal menambahkan produk: " . mysqli_error($koneksi) . "</div>";
    }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tambah Produk</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <?php include '../sidebar.php'; ?>
  <div class="content" style="margin-left:260px;padding:20px">
    <h4>Tambah Produk</h4>
    <div class="card p-3 shadow-sm">

      <!-- Tampilkan pesan -->
      <?php if ($msg != '') echo $msg; ?>

      <form method="post" enctype="multipart/form-data">
        <div class="mb-2"><input name="nama_produk" class="form-control" placeholder="Nama produk" required></div>
        <div class="mb-2">
          <select name="kategori" class="form-select" required>
            <option value="">Pilih kategori</option>
            <option value="Dingin">Dingin</option>
            <option value="Panas">Panas</option>
          </select>
        </div>
        <div class="mb-2"><input name="harga" type="number" class="form-control" placeholder="Harga" required></div>
        <div class="mb-2"><input name="stok" type="number" class="form-control" placeholder="Stok" required></div>
        <div class="mb-2"><input name="foto" type="file" class="form-control" accept="image/*"></div>
        <div class="text-end">
          <button name="simpan" class="btn btn-success">Simpan</button> 
          <a href="kelola_produk.php" class="btn btn-dark">Batal</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
