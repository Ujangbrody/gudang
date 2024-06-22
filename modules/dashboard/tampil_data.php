<?php


if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
  
  header('location: 404.html');
}

else {
  
  
  if (isset($_GET['pesan'])) {
    
    if ($_GET['pesan'] == 1) {
      
      echo '<div class="alert alert-notify alert-secondary alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-user-alt"></span> 
              <span data-notify="title" class="text-primary">Hi! ' . $_SESSION['nama_user'] . '</span> 
              <span data-notify="message">Selamat Datang di Aplikasi Persediaan Barang Gudang Material.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
  }
?>
  <div class="panel-header bg-primary-gradient">
    <div class="page-inner py-5">
      <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
        <div class="page-header text-white">
          
          <h4 class="page-title text-white"><i class="fas fa-home mr-2"></i> Dashboard</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="page-inner mt--5">
    <div class="row row-card-no-pd mt--2">
      
      <div class="col-sm-12 col-md-4">
        <div class="card card-stats card-round">
          <div class="card-body ">
            <div class="row">
              <div class="col-5">
                <div class="icon-big2 text-center">
                  <i class="flaticon-box-2 text-secondary"></i>
                </div>
              </div>
              <div class="col-7 col-stats">
                <div class="numbers">
                  <p class="card-category">Data Barang</p>
                  <?php
                  
                  $query = mysqli_query($mysqli, "SELECT * FROM tbl_barang")
                                                  or die('Ada kesalahan pada query jumlah data barang : ' . mysqli_error($mysqli));
                  
                  $jumlah_barang = mysqli_num_rows($query);
                  ?>
                  
                  <h4 class="card-title"><?php echo number_format($jumlah_barang, 0, '', '.'); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-sm-12 col-md-4">
        <div class="card card-stats card-round">
          <div class="card-body ">
            <div class="row">
              <div class="col-5">
                <div class="icon-big2 text-center">
                  <i class="flaticon-inbox text-success"></i>
                </div>
              </div>
              <div class="col-7 col-stats">
                <div class="numbers">
                  <p class="card-category">Data Barang Masuk</p>
                  <?php
                  
                  $query = mysqli_query($mysqli, "SELECT * FROM tbl_barang_masuk")
                                                  or die('Ada kesalahan pada query jumlah data barang masuk : ' . mysqli_error($mysqli));
                  
                  $jumlah_barang_masuk = mysqli_num_rows($query);
                  ?>
                  
                  <h4 class="card-title"><?php echo number_format($jumlah_barang_masuk, 0, '', '.'); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-sm-12 col-md-4">
        <div class="card card-stats card-round">
          <div class="card-body">
            <div class="row">
              <div class="col-5">
                <div class="icon-big2 text-center">
                  <i class="flaticon-archive text-warning"></i>
                </div>
              </div>
              <div class="col-7 col-stats">
                <div class="numbers">
                  <p class="card-category">Data Barang Keluar</p>
                  <?php
                  
                  $query = mysqli_query($mysqli, "SELECT * FROM tbl_barang_keluar")
                                                  or die('Ada kesalahan pada query jumlah data barang keluar : ' . mysqli_error($mysqli));
                  
                  $jumlah_barang_keluar = mysqli_num_rows($query);
                  ?>
                  
                  <h4 class="card-title"><?php echo number_format($jumlah_barang_keluar, 0, '', '.'); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php
    
    
    if ($_SESSION['hak_akses'] != 'Kepala Gudang') { ?>
      
      <div class="row">
        
        <div class="col-sm-12 col-md-4">
          <div class="card card-stats card-round">
            <div class="card-body ">
              <div class="row align-items-center">
                <div class="col-icon">
                  <div class="icon-big text-center icon-warning bubble-shadow-small">
                    <i class="fas fa-clone"></i>
                  </div>
                </div>
                <div class="col col-stats ml-3 ml-sm-0">
                  <div class="numbers">
                    <p class="card-category">Data Jenis Barang</p>
                    <?php
                    
                    $query = mysqli_query($mysqli, "SELECT * FROM tbl_jenis")
                                                    or die('Ada kesalahan pada query jumlah data jenis barang : ' . mysqli_error($mysqli));
                    
                    $jumlah_jenis_barang = mysqli_num_rows($query);
                    ?>
                    
                    <h4 class="card-title"><?php echo number_format($jumlah_jenis_barang, 0, '', '.'); ?></h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-sm-12 col-md-4">
          <div class="card card-stats card-round">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-icon">
                  <div class="icon-big text-center icon-info bubble-shadow-small">
                    <i class="fas fa-folder-open"></i>
                  </div>
                </div>
                <div class="col col-stats ml-3 ml-sm-0">
                  <div class="numbers">
                    <p class="card-category">Data Satuan</p>
                    <?php
                    
                    $query = mysqli_query($mysqli, "SELECT * FROM tbl_satuan")
                                                    or die('Ada kesalahan pada query jumlah data satuan : ' . mysqli_error($mysqli));
                    
                    $jumlah_satuan = mysqli_num_rows($query);
                    ?>
                    
                    <h4 class="card-title"><?php echo number_format($jumlah_satuan, 0, '', '.'); ?></h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-sm-12 col-md-4">
          <div class="card card-stats card-round">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-icon">
                  <div class="icon-big text-center icon-success bubble-shadow-small">
                    <i class="fas fa-user-circle"></i>
                  </div>
                </div>
                <div class="col col-stats ml-3 ml-sm-0">
                  <div class="numbers">
                    <p class="card-category">Data User</p>
                    <?php
                    
                    $query = mysqli_query($mysqli, "SELECT * FROM tbl_user")
                                                    or die('Ada kesalahan pada query jumlah data user : ' . mysqli_error($mysqli));
                    
                    $jumlah_user = mysqli_num_rows($query);
                    ?>
                    
                    <h4 class="card-title"><?php echo number_format($jumlah_user, 0, '', '.'); ?></h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr class="mt-1 pb-2">
    <?php } ?>
    
    
    <div class="card">
      <div class="card-header">
        
        <div class="card-title"><i class="fas fa-info-circle mr-2"></i> Stok barang telah mencapai batas minimum</div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          
          <table id="basic-datatables" class="display table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center">No.</th>
                <th class="text-center">ID Barang</th>
                <th class="text-center">Nama Barang</th>
                <th class="text-center">Jenis Barang</th>
                <th class="text-center">Stok</th>
                <th class="text-center">Satuan</th>
              </tr>
            </thead>
            <tbody>
              <?php
              
              $no = 1;
              
              $query = mysqli_query($mysqli, "SELECT a.id_barang, a.nama_barang, a.jenis, a.stok_minimum, a.stok, a.satuan, b.nama_jenis, c.nama_satuan
                                              FROM tbl_barang as a INNER JOIN tbl_jenis as b INNER JOIN tbl_satuan as c 
                                              ON a.jenis=b.id_jenis AND a.satuan=c.id_satuan 
                                              WHERE a.stok<=a.stok_minimum ORDER BY a.id_barang ASC")
                                              or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
              
              while ($data = mysqli_fetch_assoc($query)) { ?>
                
                <tr>
                  <td width="50" class="text-center"><?php echo $no++; ?></td>
                  <td width="80" class="text-center"><?php echo $data['id_barang']; ?></td>
                  <td width="200"><?php echo $data['nama_barang']; ?></td>
                  <td width="150"><?php echo $data['nama_jenis']; ?></td>
                  <td width="70" class="text-right"><span class="badge badge-warning"><?php echo $data['stok']; ?></span></td>
                  <td width="70"><?php echo $data['nama_satuan']; ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
<?php } ?>