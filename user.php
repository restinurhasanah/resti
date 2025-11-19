<?php
include "koneksi.php";

//password saya sendiri
$username = "resti";
$password = password_hash("resti123", PASSWORD_DEFAULT);
$nama = "Resti";
$level = "admin";

$query1 = mysqli_query($koneksi, "INSERT INTO user (nama, username, password, level)
VALUES ('$nama', '$username', '$password', '$level')");
if ($query1 && $query2) {
    echo "✅ Akun admin (Resti) berhasil dibuat.";
} else {
    echo "❌ Gagal membuat akun. " . mysqli_error($koneksi);
}
?>
