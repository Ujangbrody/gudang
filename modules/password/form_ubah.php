<?php


if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
  
  header('location: 404.html');
}

else {
  
  
  if (isset($_GET['pesan'])) {
    
    if ($_GET['pesan'] == 1) {
      
      echo '<div class="alert alert-notify alert-danger alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-times"></span> 
              <span data-notify="title" class="text-danger">Gagal!</span> 
              <span data-notify="message">Password Lama yang Anda masukan salah.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 2) {
      
      echo '<div class="alert alert-notify alert-danger alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-times"></span> 
              <span data-notify="title" class="text-danger">Gagal!</span> 
              <span data-notify="message">Password Baru dan Konfirmasi Password Baru tidak cocok.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 3) {
      
      echo '<div class="alert alert-notify alert-success alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-check"></span> 
              <span data-notify="title" class="text-success">Sukses!</span> 
              <span data-notify="message">Password berhasil diubah.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
  }
?>
  <div class="panel-header bg-primary-gradient">
    <div class="page-inner py-4">
      <div class="page-header text-white">
        
        <h4 class="page-title text-white"><i class="fas fa-user-lock mr-2"></i> Password</h4>
        
        <ul class="breadcrumbs">
          <li class="nav-home"><a href="?module=dashboard"><i class="flaticon-home text-white"></i></a></li>
          <li class="separator"><i class="flaticon-right-arrow"></i></li>
          <li class="nav-item"><a>Password</a></li>
          <li class="separator"><i class="flaticon-right-arrow"></i></li>
          <li class="nav-item"><a>Ubah</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="page-inner mt--5">
    <div class="card">
      <div class="card-header">
        
        <div class="card-title">Ubah Password</div>
      </div>
      
      <form action="modules/password/proses_ubah.php" method="post" class="needs-validation" novalidate>
        <div class="card-body">
          <div class="form-group">
            <label>Password Lama <span class="text-danger">*</span></label>
            <input type="password" name="password_lama" class="form-control col-lg-5" autocomplete="off" required>
            <div class="invalid-feedback">Password lama tidak boleh kosong.</div>
          </div>

          <div class="form-group">
            <label>Password Baru <span class="text-danger">*</span></label>
            <input type="password" name="password_baru" class="form-control col-lg-5" autocomplete="off" required>
            <div class="invalid-feedback">Password baru tidak boleh kosong.</div>
          </div>

          <div class="form-group">
            <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
            <input type="password" name="konfirmasi_password" class="form-control col-lg-5" autocomplete="off" required>
            <div class="invalid-feedback">Konfirmasi password baru tidak boleh kosong.</div>
          </div>
        </div>
        <div class="card-action">
          <input type="submit" name="simpan" value="Simpan" class="btn btn-primary btn-round pl-4 pr-4 mr-2">
        </div>
      </form>
    </div>
  </div>
<?php } ?>