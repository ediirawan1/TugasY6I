<?php
require 'config.php';

if (!isset($_SESSION['log'])) {
    header('location:login.php');
    exit();
}

if (isset($_POST['addDataBarang'])) {
    $namaBarang = trim($_POST['nama_barang']);
    $jumlahBarang = intval($_POST['jumlah_barang']);

    if (empty($namaBarang) || $jumlahBarang <= 0) {
    
    } else {
        $result = mysqli_query($conn, "SELECT MAX(kode_barang) as max_kode FROM data_barang");
        $data = mysqli_fetch_assoc($result);
        $kodeTerakhir = $data['max_kode']; 
        if ($kodeTerakhir) {
            $urutan = (int)substr($kodeTerakhir, 2) + 1;
        } else {
            $urutan = 1; 
        }

        $kodeBaru = 'KB' . str_pad($urutan, 4, '0', STR_PAD_LEFT);

        $stmt = mysqli_prepare($conn, "INSERT INTO data_barang (kode_barang, nama_barang, jumlah_barang) VALUES (?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssi", $kodeBaru, $namaBarang, $jumlahBarang);
            if (mysqli_stmt_execute($stmt)) {
                echo "<script>
                    alert('Data berhasil ditambahkan!');
                    window.location.href = 'index.php';
                </script>";
                exit();
            } else {
                echo "<script>alert('Gagal menambahkan data.');</script>";
            }
        } else {
            echo "<script>alert('Gagal mempersiapkan statement.');</script>";
        }
    }
}

if (isset($_POST['updatebarang'])) {
  $kodebarang =  $_POST['kode_barang'];
  $namabarang =  $_POST['nama_barang'];

  $update = mysqli_query($conn,"UPDATE data_barang SET nama_barang='$namabarang' WHERE kode_barang='$kodebarang'");
  if ($update) {
     echo "<script>
                alert('Data berhasil diubah!');
                window.location.href = 'index.php';
              </script>";
      exit();
  }
    echo "<script>
            alert('Gagal mengubah data!');
            window.location.href = 'index.php';
          </script>";
    exit();
}

if (isset($_POST['deletebarang'])) {
    $kodebarang = $_POST['kode_barang'];

    mysqli_query($conn, "DELETE FROM barang_masuk WHERE kode_barang='$kodebarang'");

    // Baru hapus dari data_barang
    $delete = mysqli_query($conn, "DELETE FROM data_barang WHERE kode_barang='$kodebarang'");

    if ($delete) {
        echo "<script>
                alert('Data berhasil dihapus!');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>alert('Gagal menghapus data');</script>";
    }
}

?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Data Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script
      src="https://use.fontawesome.com/releases/v6.3.0/js/all.js"
      crossorigin="anonymous"
    ></script>
  </head>
  <body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
      <!-- Navbar Brand-->
      <a class="navbar-brand ps-3" href="index.php">Kelompok 4</a>
      <!-- Sidebar Toggle-->
      <button
        class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0"
        id="sidebarToggle"
        href="#!"
      >
        <i class="fas fa-bars"></i>
      </button>
    </nav>
    <div id="layoutSidenav">
      <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
          <div class="sb-sidenav-menu">
           <div class="nav">
             <div class="sb-sidenav-footer">
              <div class="small"></div>
               <?php
                  if (isset($_SESSION['username'])) {
                      echo '<i class="fa-solid fa-user-check" style="color: #63E6BE;"></i> <strong>Welcome,</strong> ' ,htmlspecialchars($_SESSION['username']);
                  } else {
                      echo "Guest";
                  }
                  ?>
              </div>
              <a class="nav-link" href="index.php">
                <div class="sb-nav-link-icon">
                  <i class="fa-solid fa-house-laptop"></i>
                </div>
                Data Barang
              </a>
              <a class="nav-link" href="barangMasuk.php">
                <div class="sb-nav-link-icon">
                  <i class="fa-solid fa-cart-plus"></i>
                </div>
                Barang Masuk
              </a>
              <a class="nav-link" href="barangKeluar.php">
                <div class="sb-nav-link-icon">
                  <i class="fa-solid fa-truck-arrow-right"></i>
                </div>
                Barang Keluar
              </a>
              <a class="nav-link" href="logout.php" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <div class="sb-nav-link-icon">
                  <i class="fa-solid fa-right-from-bracket"></i>
                </div>
                Logout
              </a>
            </div>
          </div>
       </nav>
      </div>
      <div id="layoutSidenav_content">
        <main>
          <div class="container-fluid px-4">
            <h1 class="mt-4">Data Barang</h1>
            <ol class="breadcrumb mb-4">
            </ol>
           <div class="card mb-4">
            <div class="card-header">
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                Tambah Barang
              </button>
            </div>
            <div class="card-body">
              <table id="datatablesSimple" class="display">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Barang</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                 <tbody>
                    <?php
                      $result = mysqli_query($conn, "SELECT * FROM data_barang ORDER BY kode_barang ASC");
                      $no = 1;
                      while ($row = mysqli_fetch_assoc($result)) {
                        $kodebarang = htmlspecialchars($row['kode_barang']);
                        $namabarang = htmlspecialchars($row['nama_barang']);
                        $jumlahbarang = intval($row['jumlah_barang']);
                      ?>
                      <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $kodebarang; ?></td>
                        <td><?= $namabarang; ?></td>
                        <td><?= $jumlahbarang; ?></td>
                        <td>
                          <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $kodebarang; ?>">
                            Edit
                          </button>
                          <input type="hidden" name="deletekodebarang" value="<?= $kodebarang; ?>">
                          <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete<?= $kodebarang; ?>">
                             Delete
                          </button>
                          </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="edit<?= $kodebarang; ?>">
                          <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Edit Barang</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                      <form method="post">
                                        <div class="modal-body">
                                          <input type="hidden" name="kode_barang" value="<?= $kodebarang; ?>">
                                          <input type="text" name="nama_barang" value="<?= $namabarang; ?>" class="form-control" required>
                                          <br>
                                          <button type="submit" class="btn btn-primary" name="updatebarang">Submit</button>
                                        </div>
                                      </form>
                            </div>
                          </div>
                         </div>

                          <!-- Modal Delete -->
                        <div class="modal fade" id="delete<?= $kodebarang; ?>">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">Hapus Barang</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                              </div>
                              <form method="post">
                              <div class="modal-body">
                                Apakah anda yakin ingin menghapus <strong><?= $namabarang; ?></strong>?
                                <input type="hidden" name="kode_barang" value="<?= $kodebarang; ?>">
                                <br><br>
                                <button type="submit" class="btn btn-danger" name="deletebarang">Hapus</button>
                              </div>
                              </form>
                                </div>
                          </div>
                       </div>
                     <?php } ?>
                 </tbody>
              </table>
            </div>
          </div>
        </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
          <div class="container-fluid px-4">
            <div
              class="d-flex align-items-center justify-content-between small"
            >
              <div class="text-muted">Copyright &copy; Kelompok 4 2025</div>
            </div>
          </div>
        </footer>
      </div>
    </div>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
      crossorigin="anonymous"
    ></script>
    <script src="js/scripts.js"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
      crossorigin="anonymous"
    ></script>
    <script src="js/datatables-simple-demo.js"></script>
  </body>

<!-- The Modal -->
<div class="modal fade" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Tambah Barang</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal body -->
      <form method="post">
      <div class="modal-body">
        <input type="text" name="nama_barang" placeholder="Nama Barang" class="form-control" required>
        <br>
        <input type="number" name="jumlah_barang"  placeholder="Jumlah Barang" class="form-control" required>
        <br>
        <button type="submit" class="btn btn-primary" name="addDataBarang">Submit</button>
      </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin logout?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <a href="logout.php" class="btn btn-danger">Yes</a>
      </div>
    </div>
  </div>
</div>

</html>
