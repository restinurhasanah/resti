<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../welcome.php");
  exit;
}

include '../koneksi.php';
include 'sidebar.php';

// ===== Ambil daftar kasir/admin dari tabel user =====
$kasirQuery = mysqli_query($koneksi, "SELECT id_user, username FROM user ORDER BY username");

// ===== Ambil filter GET =====
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$kasir = $_GET['kasir'] ?? '';
$bulan = $_GET['bulan'] ?? '';
$tahunSekarang = 2025;

// ===== Query TOTAL =====
$sqlTotal = "SELECT 
                COALESCE(SUM(total_harga), 0) AS total_pendapatan,
                COUNT(id_transaksi) AS total_transaksi
              FROM transaksi t
              LEFT JOIN user u ON t.id_user = u.id_user
              WHERE 1";

if ($from && $to)      $sqlTotal .= " AND DATE(tgl_transaksi) BETWEEN '$from' AND '$to'";
if ($bulan)            $sqlTotal .= " AND MONTH(tgl_transaksi) = '$bulan'";
if ($kasir)            $sqlTotal .= " AND t.id_user = '$kasir'";

$dataTotal = mysqli_fetch_assoc(mysqli_query($koneksi, $sqlTotal));

// ===== Query TABEL =====
$sql = "SELECT t.*, u.username 
        FROM transaksi t 
        LEFT JOIN user u ON t.id_user = u.id_user 
        WHERE 1";

if ($from && $to)  $sql .= " AND DATE(t.tgl_transaksi) BETWEEN '$from' AND '$to'";
if ($bulan)        $sql .= " AND MONTH(t.tgl_transaksi) = '$bulan'";
if ($kasir)        $sql .= " AND t.id_user = '$kasir'";

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
.table thead { background-color: #dee2e6; }
h4 { color: #555; }

/* FOOTER SAMA SEPERTI DASHBOARD */
.footer-dashboard {
    width: calc(100% - 260px);
    margin-left: 260px;
    background-color: #262626;
    color: white;
    text-align: center;
    padding: 12px 0;
    border-top: 1px solid #444;
    font-size: 14px;
    position: fixed;
    bottom: 0;
}

@media print {
  .no-print { display: none !important; }
  .footer-dashboard { display: none; }
  body * { visibility: hidden; }
  #print-area, #print-area * { visibility: visible; }
  #print-area {
    position: absolute;
    left: 0; top: 0;
    width: 100%;
    padding: 20px;
    background: white !important;
  }
}
</style>
</head>
<body>

<div style="margin-left:260px; padding:20px; padding-bottom:80px;">

  <h4 class="mb-4 fw-bold text-secondary text-center">📄 LAPORAN TRANSAKSI</h4>

  <!-- ======================= FILTER BARU ======================= -->
  <form class="no-print mb-4" method="GET">
      <div class="d-flex justify-content-center">
          <div class="row g-3" style="max-width: 900px;">

              <!-- Periode Tanggal -->
              <div class="col-md-6">
                  <label class="form-label fw-semibold">Dari Tanggal</label>
                  <input type="date" name="from" class="form-control" 
                         value="<?= $from ?>" onchange="this.form.submit()">
              </div>

              <div class="col-md-6">
                  <label class="form-label fw-semibold">Sampai Tanggal</label>
                  <input type="date" name="to" class="form-control" 
                         value="<?= $to ?>" onchange="this.form.submit()">
              </div>

              <!-- Periode Bulan -->
              <div class="col-md-12">
                  <label class="form-label fw-semibold">Periode Bulan</label>
                  <select name="bulan" class="form-control" onchange="this.form.submit()">
                      <option value="">-- Semua Bulan --</option>
                      <?php for($i=1;$i<=12;$i++): ?>
                          <option value="<?= $i ?>" <?= ($bulan==$i?'selected':'') ?>>
                              <?= date('F', mktime(0,0,0,$i,10)) ?> 2025
                          </option>
                      <?php endfor; ?>
                  </select>
              </div>

              <!-- Filter Kasir -->
              <div class="col-md-12">
                  <label class="form-label fw-semibold">Filter Berdasarkan Kasir:</label><br>

                  <div class="d-flex flex-wrap gap-3">
                      <label>
                          <input type="radio" name="kasir" value=""
                            <?= ($kasir==''?'checked':'') ?> onchange="this.form.submit()">
                          <strong>Semua Kasir</strong>
                      </label>

                      <?php while($k = mysqli_fetch_assoc($kasirQuery)): ?>
                          <label>
                              <input type="radio" name="kasir" 
                                value="<?= $k['id_user'] ?>"
                                <?= ($kasir==$k['id_user']?'checked':'') ?> 
                                onchange="this.form.submit()">
                              <?= htmlspecialchars($k['username']) ?>
                          </label>
                      <?php endwhile; ?>
                  </div>
              </div>

              <!-- Tombol Cetak -->
              <div class="col-md-4 mx-auto">
                  <button type="button" class="btn btn-success w-100" onclick="printLaporan()">
                      🖨 Cetak Laporan
                  </button>
              </div>

          </div>
      </div>
  </form>
  <!-- ======================= END FILTER ======================= -->


  <!-- ================== AREA CETAK ================== -->
  <div id="print-area" class="bg-white p-4 rounded shadow-sm">

    <div class="text-center mb-4 border-bottom pb-3">
      <h4 class="fw-bold mb-0">LAPORAN TRANSAKSI</h4>
      <p class="text-muted mb-0">KASIR WEDANGAN NUSANTARA</p>

      <small class="text-secondary">
        <?php if ($from && $to): ?>
          Periode: <?= date('d/m/Y', strtotime($from)) ?> - <?= date('d/m/Y', strtotime($to)) ?>
        <?php elseif($bulan): ?>
          Periode: Bulan <?= date('F', mktime(0,0,0,$bulan,1)) ?> <?= $tahunSekarang ?>
        <?php else: ?>
          Periode: Tahun <?= $tahunSekarang ?>
        <?php endif; ?>
      </small>
    </div>

    <!-- Ringkasan -->
    <div class="alert alert-info py-2 mb-2">
      <strong>Total Transaksi:</strong> <?= number_format($dataTotal['total_transaksi'],0,',','.') ?> transaksi
    </div>

    <div class="alert alert-success py-2">
      <strong>Total Pendapatan:</strong> Rp <?= number_format($dataTotal['total_pendapatan'],0,',','.') ?>
    </div>

    <!-- TABEL -->
    <table class="table table-bordered table-striped mt-3">
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
          <td><?= $r['kode_transaksi'] ?></td>
          <td><?= date('d-m-Y H:i', strtotime($r['tgl_transaksi'])) ?></td>
          <td><?= htmlspecialchars($r['username']) ?></td>
          <td>Rp <?= number_format($r['total_harga'],0,',','.') ?></td>
          <td>Rp <?= number_format($r['bayar'],0,',','.') ?></td>
          <td>Rp <?= number_format($r['kembalian'],0,',','.') ?></td>
          <td><?= $r['metode_pembayaran'] ?></td>
          <td class="no-print text-center">
            <a href="cetak_struk.php?id=<?= $r['id_transaksi'] ?>" class="btn btn-outline-primary btn-sm">Lihat Struk</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

  </div>
</div>

<!-- FOOTER DASHBOARD -->
<div class="footer-dashboard">
  © <?= date('Y') ?> Kasir Wedangan Nusantara — All Rights Reserved.
</div>

<script>
function printLaporan() {
  window.print();
}
</script>

</body>
</html>
