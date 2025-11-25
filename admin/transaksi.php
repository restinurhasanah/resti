<?php 
session_start();
if (!isset($_SESSION['level']) || !in_array($_SESSION['level'], ['admin','kasir'])) {
  header("Location: ../../welcome.php");
  exit;
}
include '../koneksi.php';

$produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY nama_produk");

if (isset($_POST['simpan'])) {
  $id_user = $_SESSION['id_user'];
  $total_harga = (int)$_POST['total_harga'];
  $bayar = (int)$_POST['bayar'];
  $nama_pelanggan = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']); // Menangkap nama pelanggan

  if ($bayar < $total_harga) {
    echo "<script>alert('Uang tidak cukup! Transaksi dibatalkan.'); window.history.back();</script>";
    exit;
  }

  $kembalian = $bayar - $total_harga;
  $metode_pembayaran = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);
  $kode_transaksi = 'TRX' . date('YmdHis');

  // Menyimpan transaksi termasuk nama pelanggan
  mysqli_query($koneksi, "INSERT INTO transaksi 
    (kode_transaksi, tgl_transaksi, id_user, total_harga, bayar, kembalian, metode_pembayaran, nama_pelanggan)
    VALUES 
    ('$kode_transaksi', NOW(), '$id_user', '$total_harga', '$bayar', '$kembalian', '$metode_pembayaran', '$nama_pelanggan')");
  
  $id_transaksi = mysqli_insert_id($koneksi);

  foreach ($_POST['produk'] as $i => $id_produk) {
    $qty = (int)$_POST['qty'][$i];
    $harga = (int)$_POST['harga'][$i];
    $subtotal = $qty * $harga;

    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT stok FROM produk WHERE id_produk='$id_produk'"));
    if ($cek['stok'] < $qty) {
      echo "<script>alert('Stok produk tidak mencukupi!'); window.history.back();</script>";
      exit;
    }

    mysqli_query($koneksi, "INSERT INTO detail_transaksi (id_transaksi, id_produk, qty, subtotal)
                            VALUES ('$id_transaksi', '$id_produk', '$qty', '$subtotal')");
    mysqli_query($koneksi, "UPDATE produk SET stok = stok - $qty WHERE id_produk = '$id_produk'");
  }

  $_SESSION['id_transaksi_cetak'] = $id_transaksi;
  header("Location: cetak_struk.php");
  exit;
}
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>🧾 Transaksi Kasir</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
  height:100vh;
  background: linear-gradient(to bottom, #252525ff, #464646ff);
  font-family: 'Poppins', sans-serif;
}


.content {
  margin-left: 260px;
  padding: 25px;
}


.card {
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.07);
  transition: .2s;
}
.card:hover {
  transform: translateY(-3px);
}


.table thead {
  background-color: #e9ecef;
}

.scroll-area {
  max-height: 300px;
  overflow-y: auto;
}


.input-rp { position: relative; }
.input-rp span {
  position: absolute;
  left: 10px;
  top: 8px;
  color: #6c757d;
}
.input-rp input { padding-left: 35px; }

.footer {
  width: calc(100% - 260px);
  margin-left: 260px;
  background: #262626;
  color: white;
  text-align: center;
  padding: 18px 0;
  border-top: 2px solid #333;
  font-weight: 600;
  position: fixed;
  bottom: 0;
  z-index: 99;
}
</style>

</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
  <h4 class="mb-4 fw-bold text-secondary fw-semibold text-light" style="text-align:center;">
    🧾 Transaksi Penjualan
  </h4>

  <div class="row g-4">

    <!--PRODUK -->
    <div class="col-md-7">
      <div class="card">
        <div class="card-body">
          <h6 class="fw-bold mb-3 text-secondary">📦 Daftar Produk</h6>

          <table class="table table-bordered table-hover align-middle text-center">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no=1; while($p = mysqli_fetch_assoc($produk)): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                <td>Rp <?= number_format($p['harga'],0,',','.') ?></td>
                <td><?= $p['stok'] ?></td>
                <td>
                  <button type="button" class="btn btn-secondary btn-sm tambahKeranjang"
                          data-id="<?= $p['id_produk'] ?>"
                          data-nama="<?= htmlspecialchars($p['nama_produk']) ?>"
                          data-harga="<?= $p['harga'] ?>"
                          data-stok="<?= $p['stok'] ?>">Tambah</button>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>

        </div>
      </div>
    </div>

    <!--KERANJANG -->
    <div class="col-md-5">
      <form method="post" onsubmit="return cekPembayaran();">

        <div class="card">
          <div class="card-body">
            <h6 class="fw-bold text-center text-secondary mb-3">🛒 Keranjang Belanja</h6>

            <div class="scroll-area">
              <table class="table table-sm text-center" id="tabelCart">
                <thead>
                  <tr><th>Produk</th><th>Qty</th><th>Subtotal</th><th></th></tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <!-- Input Nama Pelanggan -->
            <div class="mt-3">
              <label class="form-label">Nama Pelanggan</label>
              <input type="text" name="nama_pelanggan" class="form-control" required>
            </div>

            <div class="mt-3">
              <label class="form-label">Metode Pembayaran</label>
              <input type="text" id="metode_pembayaran" name="metode_pembayaran" class="form-control" required>
            </div>

            <div class="mt-3">
              <label class="form-label">Total Harga</label>
              <div class="input-rp">
                <span>Rp</span>
                <input type="text" id="total_display" class="form-control" readonly>
              </div>
              <input type="hidden" name="total_harga" id="total_harga">
            </div>

            <div class="mt-3">
              <label class="form-label">Bayar</label>
              <div class="input-rp">
                <span>Rp</span>
                <input type="text" id="bayar_display" class="form-control" required>
              </div>
              <input type="hidden" name="bayar" id="bayar">
            </div>

            <div class="mt-3">
              <label class="form-label">Kembalian</label>
              <div class="input-rp">
                <span>Rp</span>
                <input type="text" id="kembalian_display" class="form-control" readonly>
              </div>
              <input type="hidden" name="kembalian" id="kembalian">
            </div>

            <div class="text-center mt-4">
              <button type="submit" name="simpan" class="btn btn-secondary px-4 py-2">
                ✔ Simpan Transaksi
              </button>
            </div>

          </div>
        </div>

      </form>
    </div>

  </div>
</div>


<div class="footer">
  © <?= date('Y') ?> <strong>Kasir Wedangan</strong>
</div>


<script>
const cartBody = document.querySelector('#tabelCart tbody');
const totalInput = document.getElementById('total_harga');
const totalDisplay = document.getElementById('total_display');
const bayarDisplay = document.getElementById('bayar_display');
const bayarInput = document.getElementById('bayar');
const kembalianDisplay = document.getElementById('kembalian_display');
const kembalianInput = document.getElementById('kembalian');

document.querySelectorAll('.tambahKeranjang').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const nama = btn.dataset.nama;
    const harga = parseInt(btn.dataset.harga);
    const stok = parseInt(btn.dataset.stok);

    let row = cartBody.querySelector(`tr[data-id="${id}"]`);
    if (row) {
      let qtyInput = row.querySelector('.qty');
      let newQty = parseInt(qtyInput.value) + 1;
      if (newQty > stok) {
        alert(`Stok produk ${nama} hanya ${stok}`);
        return;
      }
      qtyInput.value = newQty;
      updateSubtotal(row);
    } else {
      if (stok <= 0) {
        alert(`Stok produk ${nama} habis!`);
        return;
      }
      let tr = document.createElement('tr');
      tr.dataset.id = id;
      tr.dataset.stok = stok;
      tr.innerHTML = `
        <td>${nama}<input type="hidden" name="produk[]" value="${id}">
            <input type="hidden" name="harga[]" value="${harga}"></td>
        <td><input type="number" name="qty[]" class="form-control form-control-sm qty text-center" value="1" min="1" max="${stok}" style="width:70px;"></td>
        <td><input type="text" name="subtotal[]" class="form-control form-control-sm subtotal text-center" value="Rp ${harga.toLocaleString('id-ID')}" readonly style="width:110px;"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger hapus">x</button></td>`;
      cartBody.appendChild(tr);
    }
    hitungTotal();
  });
});

cartBody.addEventListener('input', e => {
  if (e.target.classList.contains('qty')) {
    const row = e.target.closest('tr');
    const stok = parseInt(row.dataset.stok);
    if (parseInt(e.target.value) > stok) {
      alert(`Jumlah melebihi stok (${stok})`);
      e.target.value = stok;
    }
    updateSubtotal(row);
  }
});

cartBody.addEventListener('click', e => {
  if (e.target.classList.contains('hapus')) {
    e.target.closest('tr').remove();
    hitungTotal();
  }
});

function updateSubtotal(row) {
  const harga = parseInt(row.querySelector('input[name="harga[]"]').value);
  const qty = parseInt(row.querySelector('.qty').value);
  const subtotal = harga * qty;
  row.querySelector('.subtotal').value = 'Rp ' + subtotal.toLocaleString('id-ID');
  hitungTotal();
}

function hitungTotal() {
  let total = 0;
  document.querySelectorAll('.subtotal').forEach(s => {
    let val = s.value.replace(/[^\d]/g, '');
    total += parseInt(val || 0);
  });
  totalInput.value = total;
  totalDisplay.value = total.toLocaleString('id-ID');

  const bayar = parseInt(bayarInput.value) || 0;
  const kembalian = bayar - total;
  kembalianInput.value = kembalian;
  kembalianDisplay.value = (kembalian >= 0 ? kembalian.toLocaleString('id-ID') : '0');
}

bayarDisplay.addEventListener('input', () => {
  let val = bayarDisplay.value.replace(/[^\d]/g, '');
  bayarInput.value = val;
  bayarDisplay.value = parseInt(val || 0).toLocaleString('id-ID');
  hitungTotal();
});

function cekPembayaran() {
  const total = parseInt(totalInput.value);
  const bayar = parseInt(bayarInput.value);
  if (bayar < total) {
    alert('Uang tidak cukup untuk menyelesaikan transaksi!');
    return false;
  }
  return true;
}
</script>
</body>
</html>
