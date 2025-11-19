<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../welcome.php");
  exit;
}
include '../koneksi.php';
include 'sidebar.php';

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$tahunSekarang = date('Y');

// === TOTAL PENDAPATAN & TRANSAKSI ===
$sqlTotal = "SELECT 
                COALESCE(SUM(total_harga), 0) AS total_pendapatan,
                COUNT(id_transaksi) AS total_transaksi
              FROM transaksi 
              WHERE 1";
if ($from && $to) $sqlTotal .= " AND DATE(tgl_transaksi) BETWEEN '$from' AND '$to'";
$dataTotal = mysqli_fetch_assoc(mysqli_query($koneksi, $sqlTotal));

// === DATA TRANSAKSI ===
$sql = "SELECT t.*, u.username 
        FROM transaksi t 
        LEFT JOIN user u ON t.id_user = u.id_user 
        WHERE 1";
if ($from && $to) $sql .= " AND DATE(t.tgl_transaksi) BETWEEN '$from' AND '$to'";
$sql .= " ORDER BY t.tgl_transaksi DESC";
$res = mysqli_query($koneksi, $sql);
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Laporan Transaksi <?= $tahunSekarang ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
  background-color: #f2f3f5;
  font-family: 'Poppins', sans-serif;
}
h4 { color: #555; }
.table thead { background-color: #dee2e6; }
@media print {
  body * { visibility: hidden; }
  #print-area, #print-area * { visibility: visible; }
  #print-area {
    position: absolute;
    left: 0; top: 0;
    width: 100%;
    background: white;
    padding: 20px;
  }
  .no-print { display: none !important; }
}
</style>
</head>
<body>
<div style="margin-left:260px; padding:20px">
  <h4 class="mb-3 fw-bold text-secondary"> Laporan Transaksi</h4>

  <!-- Filter tanggal otomatis -->
  <form class="row g-2 mb-4" method="GET">
    <div class="col-md-3">
      <label class="form-label">Dari Tanggal</label>
      <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-3">
      <label class="form-label">Sampai Tanggal</label>
      <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-3 d-flex align-items-end no-print">
      <button type="button" class="btn btn-success w-100" onclick="printLaporan()"> Cetak Laporan</button>
    </div>
  </form>

  <!-- Area yang dicetak -->
  <div id="print-area" class="bg-white p-4 rounded shadow-sm">
    <!-- Header Laporan -->
    <div class="text-center mb-4 border-bottom pb-3">
      <div class="d-flex justify-content-center align-items-center mb-2">
    
        <div>
          <h4 class="fw-bold mb-0">LAPORAN TRANSAKSI</h4>
          <p class="text-muted mb-0">KASIR WEDANGAN NUSANTARA</p>
        </div>
      </div>
      <small class="text-secondary">
        <?php if ($from && $to): ?>
          Periode: <?= date('d/m/Y', strtotime($from)) ?> - <?= date('d/m/Y', strtotime($to)) ?>
        <?php else: ?>
          Periode: Tahun <?= $tahunSekarang ?>
        <?php endif; ?>
      </small>
    </div>

    <!-- Ringkasan -->
    <div class="alert alert-info py-2">
      <strong>Total Transaksi:</strong> <?= number_format($dataTotal['total_transaksi'], 0, ',', '.') ?> transaksi
    </div>
    <div class="alert alert-success py-2">
      <strong>Total Pendapatan:</strong> Rp <?= number_format($dataTotal['total_pendapatan'], 0, ',', '.') ?>
    </div>

    <!-- Tabel transaksi -->
    <div class="card p-3 border-0">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-secondary text-center">
          <tr>
            <th>No</th>
            <th>Kode Transaksi</th>
            <th>Tanggal</th>
            <th>Kasir</th>
            <th>Total Harga</th>
            <th>Bayar</th>
            <th>Kembalian</th>
            <th>Metode</th>
            <th class="no-print">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; while($r = mysqli_fetch_assoc($res)): ?>
          <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><?= htmlspecialchars($r['kode_transaksi']) ?></td>
            <td><?= date('d-m-Y H:i', strtotime($r['tgl_transaksi'])) ?></td>
            <td><?= htmlspecialchars($r['username']) ?></td>
            <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
            <td>Rp <?= number_format($r['bayar'], 0, ',', '.') ?></td>
            <td>Rp <?= number_format($r['kembalian'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($r['metode_pembayaran']) ?></td>
            <td class="text-center no-print">
              <a href="cetak_struk.php?id=<?= $r['id_transaksi'] ?>" class="btn btn-outline-primary btn-sm">Lihat Struk</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    
  </div>
</div>

<script>
function printLaporan() {
  window.print();
}
</script>
</body>
</html>
