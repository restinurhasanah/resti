<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../welcome.php");
  exit;
}
include '../koneksi.php';
include 'sidebar.php';

// Ambil tahun dari GET
$tahun = isset($_GET['tahun']) && is_numeric($_GET['tahun']) ? (int)$_GET['tahun'] : 2025;

// Ringkasan tahunan
$sqlTotal = "SELECT 
                COALESCE(SUM(total_harga),0) AS total_pendapatan,
                COUNT(id_transaksi) AS total_transaksi
             FROM transaksi
             WHERE YEAR(tgl_transaksi) = '$tahun'";
$dataTotal = mysqli_fetch_assoc(mysqli_query($koneksi, $sqlTotal));

// Pendapatan per bulan
$sqlBulan = "SELECT MONTH(tgl_transaksi) AS bulan, COALESCE(SUM(total_harga),0) AS total
             FROM transaksi
             WHERE YEAR(tgl_transaksi) = '$tahun'
             GROUP BY MONTH(tgl_transaksi)";
$resBulan = mysqli_query($koneksi, $sqlBulan);

$monthly = array_fill(1,12,0);
while ($row = mysqli_fetch_assoc($resBulan)) {
  $monthly[(int)$row['bulan']] = (int)$row['total'];
}

// Hitung bulan aktif (yang ada transaksi)
$bulanAktif = 0;
foreach ($monthly as $m) {
  if ($m > 0) $bulanAktif++;
}

// Rata-rata per bulan yang BENAR (dibagi bulan yang ada transaksi)
$rataRata = $bulanAktif > 0 ? ($dataTotal['total_pendapatan'] / $bulanAktif) : 0;

// Array grafik
$labels = [];
$dataVals = [];
for ($m=1; $m<=12; $m++) {
  $labels[] = date('F', mktime(0,0,0,$m,1));
  $dataVals[] = $monthly[$m];
}

$maxPendapatan = max($dataVals);
$bulanMax = $labels[array_search($maxPendapatan, $dataVals)];
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Grafik Pendapatan <?= $tahun ?> — Kasir Wedangan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
  background:#1f1f1f;
  color:white;
  font-family:'Poppins', sans-serif;
  min-height:100vh;
}
.container-main {
  margin-left:260px;
  padding:24px;
  padding-bottom:150px;
}
.card-graph {
  background:#2a2a2a;
  border:none;
  border-radius:12px;
  box-shadow:0 6px 18px rgba(0,0,0,0.6);
  color:white;
}
.summary {
  background:#2e2e2e;
  border:1px solid rgba(255,255,255,0.08);
  padding:18px;
  border-radius:12px;
  text-align:center;
}
.summary-title { font-size:13px; color:#bbbbbb; }
.summary-value { font-size:22px; font-weight:700; margin-top:4px; }
.summary-desc { margin-top:6px; font-size:12px; color:#aaaaaa; font-style:italic; }
.chart-wrap { background:#222; padding:18px; border-radius:10px; }
.footer-dashboard {
    width: calc(100% - 260px);
    margin-left: 260px;
    background-color: #262626;
    color: white;
    text-align: center;
    padding: 12px 0;
    border-top: 1px solid #444;
    position: fixed;
    bottom: 0;
}
.keterangan-box {
    background:#333;
    padding:14px;
    border-radius:10px;
    font-size:14px;
    margin-top:15px;
}
.keterangan-box ul { margin-bottom:0; }
</style>
</head>
<body>

<div class="container-main">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">📊 Grafik Pendapatan Tahun <?= $tahun ?></h3>

    <form method="GET" class="d-flex gap-2 align-items-center">
      <label class="text-muted mb-0">Tahun</label>
      <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
        <?php for($y=2023; $y<=2026; $y++): ?>
          <option value="<?= $y ?>" <?= ($y==$tahun?'selected':'') ?>>
            <?= $y ?>
          </option>
        <?php endfor; ?>
      </select>
    </form>
  </div>

  <!-- SUMMARY -->
  <div class="row g-3 mb-3">

    <div class="col-md-4">
      <div class="summary">
        <div class="summary-title">Total Pendapatan</div>
        <div class="summary-value">Rp <?= number_format($dataTotal['total_pendapatan'],0,',','.') ?></div>
        <div class="summary-desc">Total omset selama tahun <?= $tahun ?>.</div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="summary">
        <div class="summary-title">Total Transaksi</div>
        <div class="summary-value"><?= number_format($dataTotal['total_transaksi'],0,',','.') ?></div>
        <div class="summary-desc">Jumlah transaksi masuk setahun.</div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="summary">
        <div class="summary-title">Rata-rata / Bulan</div>
        <div class="summary-value">Rp <?= number_format($rataRata,0,',','.') ?></div>
        <div class="summary-desc">Rata-rata berdasarkan bulan aktif.</div>
      </div>
    </div>

  </div>

  <!-- GRAFIK -->
  <div class="card card-graph p-3 mb-4">

    <h5 class="mb-2">📈 Pendapatan per Bulan (<?= $tahun ?>)</h5>

    <div class="chart-wrap">
      <canvas id="pendapatanChart" height="120"></canvas>
    </div>

    <div class="mt-3 small text-muted">
      Grafik ini digunakan untuk melihat pola pendapatan sepanjang tahun.
    </div>
  </div>

  <!-- TABEL -->
  <div class="card card-graph p-3 mb-3">
    <h6 class="mb-3">📅 Rincian Pendapatan Bulanan</h6>
    <div class="table-responsive">
      <table class="table table-dark table-striped table-sm mb-0">
        <thead>
          <tr>
            <th>Bulan</th>
            <th class="text-end">Pendapatan (Rp)</th>
          </tr>
        </thead>
        <tbody>
          <?php for($i=1;$i<=12;$i++): ?>
          <tr>
            <td><?= date('F', mktime(0,0,0,$i,1)) ?></td>
            <td class="text-end"><?= number_format($monthly[$i],0,',','.') ?></td>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>

    <!-- KETERANGAN -->
    <div class="keterangan-box mt-3">
      <strong>Keterangan Grafik:</strong>
      <ul>
        <li>Bar kuning menunjukkan total pendapatan tiap bulan.</li>
        <li>Pendapatan tertinggi terjadi pada bulan <b><?= $bulanMax ?></b>.</li>
        <li>Arahkan mouse ke grafik untuk detail angka.</li>
        <li>Angka 0 artinya tidak ada transaksi bulan itu.</li>
      </ul>
    </div>
  </div>

</div>

<div class="footer-dashboard">
  © <?= date('Y') ?> Kasir Wedangan Nusantara — All Rights Reserved.
</div>

<script>
const labels = <?= json_encode($labels) ?>;
const dataVals = <?= json_encode($dataVals) ?>;

new Chart(document.getElementById('pendapatanChart'), {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'Pendapatan (Rp)',
      data: dataVals,
      backgroundColor: 'rgba(255,187,51,0.9)',
      borderColor: 'rgba(255,187,51,1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive:true,
    scales:{
      y:{ticks:{callback:(v)=>v.toLocaleString('id-ID')}}
    },
    plugins:{
      tooltip:{
        callbacks:{
          label:(ctx)=>"Rp " + (ctx.raw||0).toLocaleString('id-ID')
        }
      },
      legend:{display:false}
    }
  }
});
</script>

</body>
</html>
