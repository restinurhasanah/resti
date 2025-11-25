<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../index.php");
  exit;
}

include '../koneksi.php';
include 'sidebar.php';

if (isset($_POST['tambah_kategori'])) {
  $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
  
  if ($nama != '') {
    $q = mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    if ($q) {
      echo "<script>alert('Kategori berhasil ditambahkan!'); window.location='kategori.php';</script>";
    }
  }
}

if (isset($_POST['edit_kategori'])) {
  $id = $_POST['id_kategori'];
  $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
  
  if ($nama != '') {
    $q = mysqli_query($koneksi, "UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori=$id");
    if ($q) {
      echo "<script>alert('Kategori berhasil diperbarui!'); window.location='kategori.php';</script>";
    }
  }
}

if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  $q = mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori=$id");
  if ($q) {
    echo "<script>alert('Kategori berhasil dihapus!'); window.location='kategori.php';</script>";
  }
}

$kategori_edit = null;
if (isset($_GET['edit'])) {
  $id = $_GET['edit'];
  $q = mysqli_query($koneksi, "SELECT * FROM kategori WHERE id_kategori=$id");
  $kategori_edit = mysqli_fetch_assoc($q);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #252525ff, #464646ff) !important;
            background-attachment: fixed;
            color: white;
            margin: 0;
            padding: 0;
            margin-bottom: 30px;
        }

        .content {
            margin-left: 260px;
            padding: 25px;
            min-height: 100vh;
        }

        h4 {
            font-weight: 700;
            color: #fff;
            margin-bottom: 25px;
        }

        .card {
            background: #333;
            border: none;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.3);
            color: white;
            margin-bottom: 25px;
        }

        .card-header {
            background: #444;
            border-bottom: 1px solid #555;
            border-radius: 10px 10px 0 0;
            padding: 15px 20px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        .form-control {
            background: #444;
            border: 1px solid #555;
            color: white;
            border-radius: 6px;
        }

        .form-control:focus {
            background: #444;
            color: white;
            border-color: #ffb02e;
            box-shadow: 0 0 0 0.2rem rgba(255, 176, 46, 0.25);
        }

        .form-control::placeholder {
            color: #999;
        }

        .form-label {
            font-weight: 600;
            color: #ddd;
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

        .table {
            color: white;
            margin-bottom: 0;
        }

        .table thead {
            background: #444;
            font-weight: 600;
        }

        .table tbody tr {
            border-top: 1px solid #444;
        }

        .table tbody tr:hover {
            background: #3a3a3a;
        }

        .table td {
            vertical-align: middle;
            padding: 12px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit {
            background: #ffb02e;
            color: white;
        }

        .btn-edit:hover {
            background: #e28c00;
            color: white;
        }

        .btn-hapus {
            background: #dc3545;
            color: white;
        }

        .btn-hapus:hover {
            background: #c82333;
            color: white;
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

        .text-muted {
            color: #aaa !important;
        }
    </style>
</head>
<body>

<div class="content">
    <h4>Kelola Kategori</h4>

    <!-- FORM TAMBAH / EDIT -->
    <div class="card">
        <div class="card-header">
            <?= $kategori_edit ? 'Edit Kategori' : 'Tambah Kategori Baru' ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php if ($kategori_edit): ?>
                    <input type="hidden" name="id_kategori" value="<?= $kategori_edit['id_kategori'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="nama_kategori" class="form-label">Nama Kategori</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="nama_kategori" 
                        name="nama_kategori" 
                        value="<?= $kategori_edit ? htmlspecialchars($kategori_edit['nama_kategori']) : '' ?>"
                        placeholder="Masukkan nama kategori..."
                        required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="<?= $kategori_edit ? 'edit_kategori' : 'tambah_kategori' ?>" class="btn btn-success">
                        <?= $kategori_edit ? 'Perbarui' : 'Tambah' ?>
                    </button>
                    <?php if ($kategori_edit): ?>
                        <a href="kategori.php" class="btn btn-batal">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DAFTAR KATEGORI -->
    <div class="card">
        <div class="card-header">
            Daftar Kategori
        </div>
        <div class="card-body" style="padding: 0;">
            <?php
            $q = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id_kategori DESC");
            
            if (mysqli_num_rows($q) > 0):
            ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Kategori</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($k = mysqli_fetch_assoc($q)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($k['nama_kategori']) ?></td>
                                <td>
                                    <a href="kategori.php?edit=<?= $k['id_kategori'] ?>" class="btn-sm btn-edit">Edit</a>
                                    <a href="kategori.php?hapus=<?= $k['id_kategori'] ?>" class="btn-sm btn-hapus" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center text-muted p-4">
                    <p>Belum ada kategori</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer-dashboard">
    <p>© 2025 <strong>Kasir Wedangan Nusantara</strong></p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>