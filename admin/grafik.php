<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../welcome.php");
  exit;
}
include '../koneksi.php';
include 'sidebar.php';

// Ambil tahun dari GET (default 2025 sesuai permintaan)
$tahun = isset($_GET['tahun']) && is_numeric($_GET['tahun']) ? (int)$_GET['tahun'] : 2025;

// Query ringkasan tahun terpilih
$sqlTotal = "SELECT 
                COALESCE(SUM(total_harga),0) AS total_pendapatan,
                COUNT(id_transaksi) AS total_transaksi
             FROM transaksi
             WHERE YEAR(tgl_transaksi) = '$tahun'";
$dataTotal = mysqli_fetch_assoc(mysqli_query($koneksi, $sqlTotal));

// Query pendapatan per bulan (1..12)
$sqlBulan = "SELECT MONTH(tgl_transaksi) AS bulan, COALESCE(SUM(total_harga),0) AS total
             FROM transaksi
             WHERE YEAR(tgl_transaksi) = '$tahun'
             GROUP BY MONTH(tgl_transaksi)";
$resBulan = mysqli_query($koneksi, $sqlBulan);

// siapkan array 12 bulan default 0
$monthly = array_fill(1,12,0);
while ($row = mysqli_fetch_assoc($resBulan)) {
  $b = (int)$row['bulan'];
  $monthly[$b] = (int)$row['total'];
}

// data untuk chart
$labels = [];
$dataVals = [];
for ($m=1; $m<=12; $m++) {
  $labels[] = date('F', mktime(0,0,0,$m,1)); // Bahasa Inggris month names — ok; bisa diganti jika mau ID
  $dataVals[] = $monthly[$m];
}
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Grafik Pendapatan <?= $tahun ?> — Kasir Wedangan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
  background:#1f1f1f; /* agar serasi dark dashboard */
  color: white;
  font-family: 'Poppins', sans-serif;
  min-height:100vh;
}

/* content area mengikuti sidebar */
.container-main {
  margin-left: 260px;
  padding: 24px;
  padding-bottom: 120px; /* ruang untuk footer */
}

/* Card style */
.card-graph {
  background: #2a2a2a;
  border: none;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.6);
  color: white;
}

/* small summary cards */
.summary {
  background: linear-gradient(90deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
  border: 1px solid rgba(255,255,255,0.03);
  padding: 14px;
  border-radius: 10px;
  text-align: center;
}

/* chart container */
.chart-wrap {
  background: #222;
  padding: 18px;
  border-radius: 10px;
}

/* footer same as dashboard */
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

/* responsive tweaks */
@media (max-width: 900px) {
  .container-main { padding: 12px; margin-left: 0; }
  .footer-dashboard { width: 100%; margin-left: 0; }
}
</style>
</head>
<body>

<div class="container-main">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">📊 Grafik Pendapatan Tahun <?= $tahun ?></h3>

    <form method="GET" class="d-flex gap-2 align-items-center">
      <label class="text-muted mb-0">Tahun</label>
      <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
        <?php
          // tampilkan pilihan tahun (2023..2026 misal), default 2025
          for($y = 2023; $y <= 2026; $y++) {
            $sel = ($y == $tahun) ? 'selected' : '';
            echo "<option value=\"$y\" $sel>$y</option>";
          }
        ?>
      </select>
    </form>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="summary">
        <div class="text-muted small">Total Pendapatan (<?= $tahun ?>)</div>
        <div class="h5 fw-bold">Rp <?= number_format($dataTotal['total_pendapatan'],0,',','.') ?></div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="summary">
        <div class="text-muted small">Total Transaksi (<?= $tahun ?>)</div>
        <div class="h5 fw-bold"><?= number_format($dataTotal['total_transaksi'],0,',','.') ?></div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="summary">
        <div class="text-muted small">Rata-rata / Bulan</div>
        <div class="h5 fw-bold">
          Rp <?= number_format( ($dataTotal['total_pendapatan']/12),0,',','.') ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-graph p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">Pendapatan per Bulan (<?= $tahun ?>)</h5>
      <small class="text-muted">Sumber: tabel transaksi</small>
    </div>

    <div class="chart-wrap">
      <canvas id="pendapatanChart" height="120"></canvas>
    </div>
  </div>

  <!-- Optional: tabel ringkasan bulan -->
  <div class="card card-graph p-3">
    <h6 class="mb-3">Rincian Bulanan</h6>
    <div class="table-responsive">
      <table class="table table-sm table-dark table-striped mb-0">
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
  </div>

</div>

<!-- FOOTER DASHBOARD -->
<div class="footer-dashboard">
  © <?= date('Y') ?> Kasir Wedangan Nusantara — All Rights Reserved.
</div>

<script>
// ambil data dari PHP
const labels = <?= json_encode($labels) ?>;
const dataVals = <?= json_encode($dataVals) ?>;

// buat chart
const ctx = document.getElementById('pendapatanChart').getContext('2d');
const pendapatanChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'Pendapatan (Rp)',
      data: dataVals,
      backgroundColor: 'rgba(255, 187, 51, 0.9)', // warna oranye cerah
      borderColor: 'rgba(255, 187, 51, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: {
        ticks: {
          callback: function(value) {
            return value.toLocaleString('id-ID');
          }
        }
      }
    },
    plugins: {
      tooltip: {
        callbacks: {
          label: function(context) {
            let v = context.raw || 0;
            return 'Rp ' + v.toLocaleString('id-ID');
          }
        }
      },
      legend: { display: false }
    }
  }
});
</script>

</body>
</html>
