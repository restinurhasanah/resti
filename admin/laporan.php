<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
  header("Location: ../index.php");
  exit;
}

include '../koneksi.php';
include 'sidebar.php';

// ===== Ambil daftar kasir/admin =====
$kasirQuery = mysqli_query($koneksi, "SELECT id_user, username FROM user ORDER BY username");

// ===== Filter GET =====
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$kasir = $_GET['kasir'] ?? '';
$tahunSekarang = 2025;

// ===== Query TOTAL =====
$sqlTotal = "SELECT 
                COALESCE(SUM(total_harga), 0) AS total_pendapatan,
                COUNT(id_transaksi) AS total_transaksi
              FROM transaksi t
              LEFT JOIN user u ON t.id_user = u.id_user
              WHERE 1";

if ($from && $to)      $sqlTotal .= " AND DATE(tgl_transaksi) BETWEEN '$from' AND '$to'";
if ($kasir)            $sqlTotal .= " AND t.id_user = '$kasir'";

$dataTotal = mysqli_fetch_assoc(mysqli_query($koneksi, $sqlTotal));

// ===== Query TABEL =====
$sql = "SELECT t.*, u.username 
        FROM transaksi t 
        LEFT JOIN user u ON t.id_user = u.id_user 
        WHERE 1";

if ($from && $to)  $sql .= " AND DATE(t.tgl_transaksi) BETWEEN '$from' AND '$to'";
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
  background: linear-gradient(to bottom, #252525ff, #464646ff);
  font-family: 'Poppins', sans-serif;
}


.main-wrapper {
    margin-left:260px;
    padding:20px;
    padding-bottom:80px;
    width: calc(100% - 260px);
}


.footer-dashboard {
  width: calc(100% - 260px);
  margin-left: 260px;
  background: #262626;
  color: white;
  text-align: center;
  padding: 10px 0;
  border-top: 2px solid #333;
  font-weight: 600;
  position: fixed;
  bottom: 0;
  z-index: 99;
}

@media print {
  .no-print { display: none !important; }
  .footer-dashboard { display: block; position: relative; bottom: unset; }
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


.search-wrapper {
    position: relative;
    width: 280px;
}

.search-input {
    width: 100%;
    padding: 10px 35px 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    outline: none;
    font-size: 14px;
    background: white;
    color: black;
}

.search-input:focus {
    border-color: #888;
}

.clear-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: #444;
    cursor: pointer;
    display: none;
    user-select: none;
}
</style>
</head>
<body>

<div class="main-wrapper">

  <h4 class="mb-4 fw-bold text-secondary text-center text-light">📄 LAPORAN TRANSAKSI</h4>

  <!-- ============= FILTER BAR ============= -->
  <form class="no-print mb-4" method="GET" id="filterForm">
      <div class="d-flex justify-content-center">
          <div class="row g-3 align-items-end w-100">
              <div class="col-md-4 text-end">
                  <label class="form-label fw-semibold">Dari Tanggal</label>
                  <input type="date" name="from" class="form-control" value="<?= $from ?>" id="fromDate">
              </div>
              <div class="col-md-4 text-start">
                  <label class="form-label fw-semibold">Sampai Tanggal</label>
                  <input type="date" name="to" class="form-control" value="<?= $to ?>" id="toDate">
              </div>
              <div class="col-md-4 text-end">
                  <button type="button" class="btn btn-success w-100" onclick="printReport()">
                      🖨 Cetak Laporan
                  </button>
              </div>

          </div>
      </div>
  </form>
 
  <div id="print-area" class="bg-white p-4 rounded shadow-sm">

    <div class="text-center mb-4 border-bottom pb-2">
      <h4 class="fw-bold mb-0">LAPORAN TRANSAKSI</h4>
      <p class="text-muted mb-0">KASIR WEDANGAN NUSANTARA</p>

      <small class="text-secondary">
        <?php if ($from && $to): ?>
          Periode: <?= date('d/m/Y', strtotime($from)) ?> - <?= date('d/m/Y', strtotime($to)) ?>
        <?php else: ?>
          Periode: Tahun <?= $tahunSekarang ?>
        <?php endif; ?>
      </small>
    </div>

    <!--  SEARCH BAR -->
    <div class="no-print mb-3 d-flex justify-content-end">
        <div class="search-wrapper">
            <input type="text" id="search" class="search-input" placeholder="Cari transaksi...">
            <span class="clear-btn" id="clearBtn">&times;</span>
        </div>
    </div>

    
    <div class="alert alert-info py-2 mb-2">
      <strong>Total Transaksi:</strong> <?= number_format($dataTotal['total_transaksi'],0,',','.') ?> transaksi
    </div>

    <div class="alert alert-success py-2">
      <strong>Total Pendapatan:</strong> Rp <?= number_format($dataTotal['total_pendapatan'],0,',','.') ?>
    </div>

    
    <table class="table table-bordered table-striped mt-3 w-100" id="transaksiTable">
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
            <a href="cetak_struk.php?id=<?= $r['id_transaksi'] ?>" class="btn btn-outline-primary btn-sm">Struk</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

  </div>
</div>


<div class="footer-dashboard">
  © <?= date('Y') ?> Kasir Wedangan Nusantara
</div>

<script>

document.getElementById('fromDate').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

document.getElementById('toDate').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});


const input = document.getElementById("search");
const clearBtn = document.getElementById("clearBtn");
const table = document.getElementById("transaksiTable").getElementsByTagName("tbody")[0];

input.addEventListener("input", function () {
    clearBtn.style.display = input.value.length > 0 ? "block" : "none";

    let filter = input.value.toLowerCase();
    let rows = table.getElementsByTagName("tr");

    for (let i = 0; i < rows.length; i++) {
        let text = rows[i].innerText.toLowerCase();
        rows[i].style.display = text.includes(filter) ? "" : "none";
    }
});

clearBtn.addEventListener("click", function () {
    input.value = "";
    clearBtn.style.display = "none";

    let rows = table.getElementsByTagName("tr");
    for (let i = 0; i < rows.length; i++) {
        rows[i].style.display = "";
    }

    input.focus();
});

// Function for Print
function printReport() {
    window.print();
}
</script>

</body>
</html>
