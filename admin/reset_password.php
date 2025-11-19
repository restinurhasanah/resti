<?php
include '../koneksi.php';
session_start();
?>

<?php include 'sidebar.php'; ?> <!-- sidebar kamu -->

<style>
  body {
    background-color: #ffffff;
    color: #333;
  }
  .card {
    background-color: #f5f5f5;
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }
  .card .form-control {
    background-color: #fff;
    border: 1px solid #ccc;
    color: #333;
  }
  .card .form-control:focus {
    border-color: #888;
    box-shadow: none;
  }
  .btn-primary {
    background-color: #6c757d;
    border: none;
  }
  .btn-primary:hover {
    background-color: #5a6268;
  }
  .icon-lock {
    font-size: 55px;
    color: #6c757d;
    margin-bottom: 15px;
  }
  .toggle-password {
    position: absolute;
    right: 10px;
    top: 10px;
    cursor: pointer;
    color: #888;
  }
  .toggle-password:hover {
    color: #333;
  }
</style>

<div class="container-fluid px-4">
  <h4 class="mt-4 mb-4 text-center text-dark fw-bold">Reset Password</h4>

  <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4 shadow-lg" style="width: 100%; max-width: 450px;">
      <div class="card-body">
        <div class="text-center">
          <i class="bi bi-lock-fill icon-lock"></i>
          <h5 class="mb-3 text-dark">Atur Ulang Password Anda</h5>
          <p class="text-muted">Masukkan username, password lama, dan password baru Anda di bawah ini.</p>
        </div>

        <form method="POST" action="">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" name="username" id="username" required>
          </div>

          <div class="mb-3 position-relative">
            <label for="old_password" class="form-label">Password Lama</label>
            <input type="password" class="form-control" name="old_password" id="old_password" required minlength="6">
            <i class="toggle-password bi bi-eye-slash" id="toggleOld"></i>
          </div>

          <div class="mb-3 position-relative">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" class="form-control" name="password" id="password" required minlength="6">
            <i class="toggle-password bi bi-eye-slash" id="togglePassword"></i>
          </div>

          <div class="mb-3 position-relative">
            <label for="confirm" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" name="confirm" id="confirm" required minlength="6">
            <i class="toggle-password bi bi-eye-slash" id="toggleConfirm"></i>
          </div>

          <button type="submit" name="reset" class="btn btn-primary w-100 mt-2">
            Simpan Perubahan
          </button>
        </form>

        <?php
        if (isset($_POST['reset'])) {
            $username = mysqli_real_escape_string($koneksi, $_POST['username']);
            $old_pass = $_POST['old_password'];
            $password = $_POST['password'];
            $confirm  = $_POST['confirm'];

            $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
            if (mysqli_num_rows($cek) > 0) {
                $data = mysqli_fetch_assoc($cek);
                if (!password_verify($old_pass, $data['password'])) {
                    echo "<div class='alert alert-danger mt-3 text-center'>❌ Password lama salah!</div>";
                } elseif ($password !== $confirm) {
                    echo "<div class='alert alert-warning mt-3 text-center'>⚠️ Password baru tidak cocok!</div>";
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $update = mysqli_query($koneksi, "UPDATE user SET password='$hash' WHERE username='$username'");
                    if ($update) {
                        echo "<div class='alert alert-success mt-3 text-center'>✅ Password berhasil diubah!</div>";
                    } else {
                        echo "<div class='alert alert-danger mt-3 text-center'>❌ Gagal mengubah password.</div>";
                    }
                }
            } else {
                echo "<div class='alert alert-danger mt-3 text-center'>⚠️ Username tidak ditemukan!</div>";
            }
        }
        ?>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Icons dan toggle password -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<script>
  // Toggle password visibility
  const toggleOld = document.querySelector("#toggleOld");
  const oldPass = document.querySelector("#old_password");
  const togglePassword = document.querySelector("#togglePassword");
  const password = document.querySelector("#password");
  const toggleConfirm = document.querySelector("#toggleConfirm");
  const confirm = document.querySelector("#confirm");

  toggleOld.addEventListener("click", () => {
    const type = oldPass.getAttribute("type") === "password" ? "text" : "password";
    oldPass.setAttribute("type", type);
    toggleOld.classList.toggle("bi-eye");
    toggleOld.classList.toggle("bi-eye-slash");
  });

  togglePassword.addEventListener("click", () => {
    const type = password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);
    togglePassword.classList.toggle("bi-eye");
    togglePassword.classList.toggle("bi-eye-slash");
  });

  toggleConfirm.addEventListener("click", () => {
    const type = confirm.getAttribute("type") === "password" ? "text" : "password";
    confirm.setAttribute("type", type);
    toggleConfirm.classList.toggle("bi-eye");
    toggleConfirm.classList.toggle("bi-eye-slash");
  });
</script>
