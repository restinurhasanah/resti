<?php
include '../../koneksi.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('ID Produk tidak ditemukan!');window.location='produk.php';</script>";
    exit;
}

$id = $_GET['id'];


// Hapus produk jika aman
$hapus = mysqli_query($koneksi, "UPDATE produk SET is_deleted = 1 WHERE id_produk = '$id';
");

if ($hapus) {
    echo "<script>
        alert('✅ Produk berhasil dihapus!');
        window.location='kelola_produk.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Gagal menghapus produk!');
        window.location='kelola_produk.php';
    </script>";
}
?>
