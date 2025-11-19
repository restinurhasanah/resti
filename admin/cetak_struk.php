<?php
session_start();
include '../koneksi.php';

// Ambil ID transaksi dari parameter atau session
$id_transaksi = (int)($_GET['id'] ?? ($_SESSION['id_transaksi_cetak'] ?? 0));
if(!$id_transaksi){
  echo "Transaksi tidak ditemukan.";
  exit;
}

// Ambil data transaksi + kasir
$q = "SELECT t.*, u.username AS kasir 
      FROM transaksi t 
      JOIN user u ON t.id_user = u.id_user 
      WHERE t.id_transaksi = $id_transaksi";
$trx = mysqli_fetch_assoc(mysqli_query($koneksi, $q));

// Ambil detail produk
$items = mysqli_query($koneksi, "SELECT d.*, p.nama_produk 
  FROM detail_transaksi d 
  JOIN produk p ON d.id_produk = p.id_produk 
  WHERE d.id_transaksi = $id_transaksi");
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Struk Pembayaran</title>
<style>
.sidebar{display:none !important;}
body{
  font-family: Arial, sans-serif;
  font-size: 14px;
  color: #000;
  background: #f9f9f9;
  margin: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.wrapper{
  width: 340px;
  background: #fff;
  padding: 15px 18px;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-shadow: 0 0 8px rgba(0,0,0,0.1);
}
h4{
  text-align: center;
  margin: 0 0 8px 0;
  font-size: 16px;
  font-weight: bold;
}
.header-info{
  text-align: center;
  margin-bottom: 10px;
  line-height: 1.4em;
  font-size: 13px;
}
hr{
  border: none;
  border-top: 1px dashed #000;
  margin: 10px 0;
}
table{
  width: 100%;
  border-collapse: collapse;
  margin-top: 5px;
}
td{
  padding: 4px 0;
  vertical-align: top;
}
.text-end{
  text-align: right;
}
.text-center{
  text-align: center;
}
.footer{
  text-align: center;
  margin-top: 15px;
  line-height: 1.4em;
  font-size: 13px;
}
button{
  display: block;
  width: 100%;
  margin-top: 12px;
  padding: 8px;
  font-size: 14px;
  border: none;
  background: #000;
  color: #fff;
  border-radius: 5px;
  cursor: pointer;
}
button:hover{
  background: #333;
}
.btn-kembali {
  display: block;
  margin-top: 20px;
  background: #333;
  color: #fff;
  padding: 10px 14px;
  border-radius: 4px;
  text-decoration: none;
  font-size: 14px;
  text-align: center;
}
@media print{
  button, .sidebar, .navbar, .btn-kembali {
    display: none !important;
  }
  body{
    background:#fff;
    margin:0;
    align-items: flex-start;
  }
  .wrapper{
    box-shadow:none;
    border:none;
    margin:0 auto;
  }
}
</style>
</head>
<body>
<div class="wrapper">
  <h4>WEDANGAN NUSANTARA</h4>
  <div class="header-info">
    Jl. Cahaya No. 14, Sukarno<br>
    Telp: 0838-3931-6597
  </div>
  <hr>
  <p>
    <strong>No. Transaksi:</strong> <?= htmlspecialchars($trx['kode_transaksi']) ?><br>
    <strong>Tanggal:</strong> <?= date('d-m-Y H:i', strtotime($trx['tgl_transaksi'])) ?><br>
    <strong>Kasir:</strong> <?= htmlspecialchars($trx['kasir']) ?><br>
  
  </p>
  <hr>
  <table>
    <?php while($r = mysqli_fetch_assoc($items)): ?>
      <tr>
        <td><?= htmlspecialchars($r['nama_produk']) ?> (x<?= $r['qty'] ?>)</td>
        <td class="text-end">Rp <?= number_format($r['subtotal'],0,',','.') ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
  <hr>
  <p class="text-end">
    <strong>Total : Rp <?= number_format($trx['total_harga'],0,',','.') ?></strong><br>
    Bayar &nbsp;&nbsp;&nbsp;: Rp <?= number_format($trx['bayar'],0,',','.') ?><br>
    Kembali : Rp <?= number_format($trx['kembalian'],0,',','.') ?><br>
    Metode : <?= htmlspecialchars($trx['metode_pembayaran']) ?>
  </p>
  <hr>
  <div class="footer">
     Terima kasih telah berbelanja di toko kami<br>
Semoga hari anda menyenangkan dan si dia bisa kembali<br>
    — Wedangan Nusantara —
  </div>
  <button onclick="window.print()"> Cetak Struk</button>
</div>

<!-- Tombol Kembali di luar wrapper -->
<a href="laporan.php" class="btn-kembali">Kembali ke Laporan</a>

</body>
</html>
