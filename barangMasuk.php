<?php
require 'config.php';
if (!isset($_SESSION['log'])) {
    header('location:login.php'); 
}

if (isset($_POST['barangMasuk'])) {
  $pilihBarang = $_POST['pilihBarang'];
  $penerima = $_POST['penerima'];
  $jumlahBarang = $_POST['jumlah_barang'];

  $cekBarang =  mysqli_query($conn, "SELECT * FROM data_barang WHERE kode_barang='$pilihBarang'");
  $ambilBarang = mysqli_fetch_array($cekBarang);

  $stokSekarang = $ambilBarang["jumlah_barang"];
  $namaBarang = $ambilBarang["nama_barang"];
  $tambahBarang = $stokSekarang + $jumlahBarang;

  $getLastKode = mysqli_query($conn, "SELECT MAX(kode_barang_masuk) AS kodeTerbesar FROM barang_masuk");
  $dataKode = mysqli_fetch_assoc($getLastKode);
  $kodeTerbesar = $dataKode['kodeTerbesar'];

  $getLastKode = mysqli_query($conn, "SELECT MAX(kode_barang_masuk) AS kodeTerbesar FROM barang_masuk");
  $dataKode = mysqli_fetch_assoc($getLastKode);
  $kodeTerbesar = $dataKode['kodeTerbesar'];

  if ($kodeTerbesar) {
      $urutan = (int) substr($kodeTerbesar, 2, 4);
      $urutan++;
  } else {
      $urutan = 1;
  }
  $kodeBarangMasukBaru = 'BM' . str_pad($urutan, 4, '0', STR_PAD_LEFT);

  $tambahBarangMasuk = mysqli_query($conn,
    "INSERT INTO barang_masuk (kode_barang_masuk, kode_barang, nama_barang, tanggal, penerima, jumlah_barang_masuk)
     VALUES ('$kodeBarangMasukBaru', '$pilihBarang', '$namaBarang', NOW(), '$penerima', '$jumlahBarang')"
  );

  $updateStok = mysqli_query($conn,
    "UPDATE data_barang SET jumlah_barang = '$tambahBarang' WHERE kode_barang = '$pilihBarang'"
  );

  if ($tambahBarangMasuk && $updateStok) {
    echo "<script>
                alert('Data berhasil ditambahkan!');
                window.location.href = 'barangMasuk.php';
                </script>";
                exit();
  } else {
    echo "gagal";
    header('location:barangMasuk.php');
  }
}

if (isset($_POST['updatebarangmasuk'])) {
    $kode_barang_masuk = $_POST['kode_barang_masuk'];
    $jumlah_baru = intval($_POST['jumlah_barang']);

    $cek = mysqli_query($conn, "SELECT * FROM barang_masuk WHERE kode_barang_masuk = '$kode_barang_masuk' LIMIT 1");
    $data_lama = mysqli_fetch_array($cek);
    $jumlah_lama = intval($data_lama['jumlah_barang_masuk']);
    $kode_barang = $data_lama['kode_barang'];

    $selisih = $jumlah_baru - $jumlah_lama;

    $update_stok = mysqli_query($conn, 
        "UPDATE data_barang SET jumlah_barang = jumlah_barang + $selisih WHERE kode_barang = '$kode_barang'"
    );

    $update_masuk = mysqli_query($conn,
        "UPDATE barang_masuk SET jumlah_barang_masuk = '$jumlah_baru' WHERE kode_barang_masuk = '$kode_barang_masuk' LIMIT 1"
    );

    if ($update_stok && $update_masuk) {
        echo "<script>
            alert('Data berhasil diupdate!');
            window.location.href = 'barangMasuk.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Gagal update data!'); window.location.href = 'barangMasuk.php';</script>";
    }
}

if (isset($_POST['deletebarang'])) {
    $kodeBarangMasuk = $_POST['kode_barang_masuk']; 

    try {
        $getData = mysqli_query($conn, "SELECT * FROM barang_masuk WHERE kode_barang_masuk = '$kodeBarangMasuk'");
        $data = mysqli_fetch_assoc($getData);

        if (!$data) {
            throw new Exception('Data barang masuk tidak ditemukan!');
        }

        $kodeBarang = $data['kode_barang'];
        $jumlahMasuk = intval($data['jumlah_barang_masuk']);

        $updateStok = mysqli_query($conn, "UPDATE data_barang SET jumlah_barang = jumlah_barang - $jumlahMasuk WHERE kode_barang = '$kodeBarang'");
        if (!$updateStok) {
            throw new Exception('Gagal mengurangi stok barang: ' . mysqli_error($conn));
        }

        $deleteBarangMasuk = mysqli_query($conn, "DELETE FROM barang_masuk WHERE kode_barang_masuk = '$kodeBarangMasuk'");
        if (!$deleteBarangMasuk) {
            throw new Exception('Gagal menghapus data barang masuk: ' . mysqli_error($conn));
        }

        $checkBarangMasuk = mysqli_query($conn, "SELECT * FROM barang_masuk WHERE kode_barang = '$kodeBarang'");
        if (mysqli_num_rows($checkBarangMasuk) === 0) {
            $deleteBarang = mysqli_query($conn, "DELETE FROM data_barang WHERE kode_barang = '$kodeBarang'");
            if (!$deleteBarang) {
                throw new Exception('Gagal menghapus data barang: ' . mysqli_error($conn));
            }
        }
        echo "<script>
                alert('Data berhasil dihapus!');
                window.location.href = 'barangMasuk.php';
              </script>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'a foreign key constraint fails') !== false) {
            echo "<script>
                    alert('Gagal menghapus! Data masih digunakan di tabel lain.');
                    window.location.href = 'barangMasuk.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Terjadi kesalahan saat menghapus: " . addslashes($e->getMessage()) . "');
                    window.location.href = 'barangMasuk.php';
                  </script>";
        }
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
    <title>Barang Masuk</title>
    <link
      href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css"
      rel="stylesheet"
    />
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
            <h1 class="mt-4">Barang Masuk</h1>
            <ol class="breadcrumb mb-4">
            </ol>
            <div class="card mb-4">
              <div class="card-header">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                  Tambah Barang Masuk
                </button>
              </div>
              <div class="card-body">
              <table id="datatablesSimple" class="display">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Kode Barang Masuk</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Barang Masuk</th>
                    <th>Tanggal</th>
                    <th>Penerima</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
               <tbody>
                    <?php
                      $result = mysqli_query($conn, "SELECT * FROM barang_masuk ORDER BY kode_barang ASC");
                      $no = 1;
                      while ($row = mysqli_fetch_assoc($result)) {
                        $kodebarangmasuk = htmlspecialchars($row['kode_barang_masuk']);
                        $kodebarang = htmlspecialchars($row['kode_barang']);
                        $namabarang = htmlspecialchars($row['nama_barang']);
                        $jumlahbarang = intval($row['jumlah_barang_masuk']);
                        $tanggal = htmlspecialchars(date('d-m-Y', strtotime($row['tanggal'])));
                        $penerima = htmlspecialchars($row['penerima']);
                      ?>
                      <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $kodebarangmasuk; ?></td>
                        <td><?= $kodebarang; ?></td>
                        <td><?= $namabarang; ?></td>
                        <td><?= $jumlahbarang; ?></td>
                        <td><?= $tanggal; ?></td>
                        <td><?= $penerima; ?></td>
                        <td>
                          <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $kodebarangmasuk; ?>">
                          Edit
                        </button>
                        <input type="hidden" name="deletekodebarangmasuk" value="<?= $kodebarangmasuk; ?>">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete<?= $kodebarangmasuk; ?>">
                          Delete
                        </button>
                      </td>
                      </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="edit<?= $kodebarangmasuk; ?>">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">Edit Barang</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                              </div>
                              <form method="post">
                                <div class="modal-body">
                                  <input type="hidden" name="kode_barang_masuk" value="<?= $kodebarangmasuk; ?>">
                                  <br>
                                  <input type="text" name="nama_barang" value="<?= $namabarang; ?>" class="form-control" readonly>
                                  <br>
                                  <input type="text" name="jumlah_barang" value="<?= $jumlahbarang; ?>" class="form-control" required>
                                  <br>
                                  <button type="submit" class="btn btn-primary" name="updatebarangmasuk">Submit</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                          <!-- Modal Delete -->
                        <div class="modal fade" id="delete<?= $kodebarangmasuk; ?>">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">Hapus Barang</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                              </div>
                              <form method="post">
                              <div class="modal-body">
                                Apakah anda yakin ingin menghapus <strong><?= $namabarang; ?></strong>?
                                <input type="hidden" name="kode_barang_masuk" value="<?= $kodebarangmasuk; ?>">
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
      src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"
      crossorigin="anonymous"
    ></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
      crossorigin="anonymous"
    ></script>
    <script src="js/datatables-simple-demo.js"></script>
  </body>

  <div class="modal fade" id="myModal">
    <div class="modal-dialog">
      <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Tambah Barang Masuk</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal body -->
      <form method="post">
      <div class="modal-body">
        
          <select name="pilihBarang" class="form-select">
            <?php
              $ambildata = mysqli_query($conn, "SELECT * FROM data_barang");
              while ($fetcharray = mysqli_fetch_array($ambildata)) {
                $namaBarang = $fetcharray['nama_barang'];
                $kode_barang = $fetcharray['kode_barang'];
            ?>

            <option value="<?=$kode_barang;?>"><?=$namaBarang;?></option>
            <?php
              }
            ?>
          </select>
        <br>
        <input type="number" name="jumlah_barang"  placeholder="Jumlah Barang" class="form-control" required min="0">
        <br>
        <input type="text" name="penerima" placeholder="Penerima" class="form-control" required>
        <br>
        <button type="submit" class="btn btn-primary" name="barangMasuk">Submit</button>
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
