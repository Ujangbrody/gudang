<?php


if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
  
  header('location: 404.html');
}

else { ?>
  <div class="panel-header bg-primary-gradient">
    <div class="page-inner py-4">
      <div class="page-header text-white">
        
        <h4 class="page-title text-white"><i class="fas fa-user mr-2"></i> Manajemen User</h4>
        
        <ul class="breadcrumbs">
          <li class="nav-home"><a href="?module=dashboard"><i class="flaticon-home text-white"></i></a></li>
          <li class="separator"><i class="flaticon-right-arrow"></i></li>
          <li class="nav-item"><a href="?module=user" class="text-white">User</a></li>
          <li class="separator"><i class="flaticon-right-arrow"></i></li>
          <li class="nav-item"><a>Entri</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="page-inner mt--5">
    <div class="card">
      <div class="card-header">
        
        <div class="card-title">Entri Data User</div>
      </div>
      
      <form action="modules/user/proses_entri.php" method="post" class="needs-validation" novalidate>
        <div class="card-body">
          <div class="form-group col-lg-5">
            <label>Nama User <span class="text-danger">*</span></label>
            <input type="text" name="nama_user" class="form-control" autocomplete="off" required>
            <div class="invalid-feedback">Nama user tidak boleh kosong.</div>
          </div>

          <div class="form-group col-lg-5">
            <label>Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" autocomplete="off" required>
            <div class="invalid-feedback">Username tidak boleh kosong.</div>
          </div>

          <div class="form-group col-lg-5">
            <label>Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" autocomplete="off" required>
            <div class="invalid-feedback">Password tidak boleh kosong.</div>
          </div>

          <div class="form-group col-lg-5">
            <label>Hak Akses <span class="text-danger">*</span></label>
            <select name="hak_akses" class="form-control chosen-select" autocomplete="off" required>
              <option selected disabled value="">-- Pilih --</option>
              <option value="Administrator">Administrator</option>
              <option value="Admin Gudang">Admin Gudang</option>
              <option value="Kepala Gudang">Kepala Gudang</option>
            </select>
            <div class="invalid-feedback">Hak akses tidak boleh kosong.</div>
          </div>
        </div>
        <div class="card-action">
          
          <input type="submit" name="simpan" value="Simpan" class="btn btn-primary btn-round pl-4 pr-4 mr-2">
          
          <a href="?module=user" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
        </div>
      </form>
    </div>
  </div>
<?php } ?>