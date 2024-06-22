<?php


if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
  
  header('location: 404.html');
}

else {
  
  if (isset($_GET['id'])) {
    
    $id_jenis = $_GET['id'];

    
    $query = mysqli_query($mysqli, "SELECT * FROM tbl_jenis WHERE id_jenis='$id_jenis'")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    $data = mysqli_fetch_assoc($query);
  }
?>
  <div class="panel-header bg-primary-gradient">
    <div class="page-inner py-4">
      <div class="page-header text-white">
        
        <h4 class="page-title text-white"><i class="fas fa-clone mr-2"></i> Jenis Barang</h4>
        
        <ul class="breadcrumbs">
          <li class="nav-home"><a href="?module=dashboard"><i class="flaticon-home text-white"></i></a></li>
          <li class="separator"><i class="flaticon-right-arrow"></i></li>
          <li class="nav-item"><a href="?module=jenis" class="text-white">Jenis Barang</a></li>
          <li class="separator"><i class="flaticon-right-arrow"></i></li>
          <li class="nav-item"><a>Ubah</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="page-inner mt--5">
    <div class="card">
      <div class="card-header">
        
        <div class="card-title">Ubah Data Jenis Barang</div>
      </div>
      
      <form action="modules/jenis/proses_ubah.php" method="post" class="needs-validation" novalidate>
        <div class="card-body">
          <input type="hidden" name="id_jenis" value="<?php echo $data['id_jenis']; ?>">

          <div class="form-group">
            <label>Jenis Barang <span class="text-danger">*</span></label>
            <input type="text" name="nama_jenis" class="form-control col-lg-5" autocomplete="off" value="<?php echo $data['nama_jenis']; ?>" required>
            <div class="invalid-feedback">Jenis barang tidak boleh kosong.</div>
          </div>
        </div>
        <div class="card-action">
          
          <input type="submit" name="simpan" value="Simpan" class="btn btn-primary btn-round pl-4 pr-4 mr-2">
          
          <a href="?module=jenis" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
        </div>
      </form>
    </div>
  </div>
<?php } ?>