<?php
include '../koneksi.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_transaksi.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['id_transaksi', 'id_user', 'total', 'tanggal', 'metode']);

$qr = mysqli_query($conn, "SELECT id_transaksi,id_user,total,tanggal,metode FROM transaksi ORDER BY tanggal DESC");
while ($r = mysqli_fetch_assoc($qr)) fputcsv($out, $r);
fclose($out);
exit;
