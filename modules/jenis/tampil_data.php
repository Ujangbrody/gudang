<?php


if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
  
  header('location: 404.html');
}

else {
  
  
  if (isset($_GET['pesan'])) {
    
    if ($_GET['pesan'] == 1) {
      
      echo '<div class="alert alert-notify alert-success alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-check"></span> 
              <span data-notify="title" class="text-success">Sukses!</span> 
              <span data-notify="message">Data jenis barang berhasil disimpan.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 2) {
      
      echo '<div class="alert alert-notify alert-success alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-check"></span> 
              <span data-notify="title" class="text-success">Sukses!</span> 
              <span data-notify="message">Data jenis barang berhasil diubah.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 3) {
      
      echo '<div class="alert alert-notify alert-success alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-check"></span> 
              <span data-notify="title" class="text-success">Sukses!</span> 
              <span data-notify="message">Data jenis barang berhasil dihapus.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 4) {
      
      $jenis = $_GET['jenis'];
      
      echo '<div class="alert alert-notify alert-danger alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-times"></span> 
              <span data-notify="title" class="text-danger">Gagal!</span> 
              <span data-notify="message">Jenis barang <strong>' . $jenis . '</strong> sudah ada. Silahkan ganti nama jenis barang yang Anda masukan.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 5) {
      
      echo '<div class="alert alert-notify alert-danger alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-times"></span> 
              <span data-notify="title" class="text-danger">Gagal!</span> 
              <span data-notify="message">Data jenis barang tidak bisa dihapus karena sudah tercatat pada Data Barang.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
  }
?>
  <div class="panel-header bg-primary-gradient">
    <div class="page-inner py-45">
      <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
        <div class="page-header text-white">
          
          <h4 class="page-title text-white"><i class="fas fa-clone mr-2"></i> Jenis Barang</h4>
          
          <ul class="breadcrumbs">
            <li class="nav-home"><a href="?module=dashboard"><i class="flaticon-home text-white"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="?module=jenis" class="text-white">Jenis Barang</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a>Data</a></li>
          </ul>
        </div>
        <div class="ml-md-auto py-2 py-md-0">
          
          <a href="?module=form_entri_jenis" class="btn btn-primary btn-round mr-2">
            <span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Data
          </a>
          
          <a href="modules/jenis/export.php" class="btn btn-success btn-round">
            <span class="btn-label"><i class="fa fa-file-excel mr-2"></i></span> Export
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-inner mt--5">
    <div class="card">
      <div class="card-header">
        
        <div class="card-title">Data Jenis Barang</div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          
          <table id="basic-datatables" class="display table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center">No.</th>
                <th class="text-center">Jenis Barang</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              
              $no = 1;
              
              $query = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY id_jenis DESC")
                                              or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
              
              while ($data = mysqli_fetch_assoc($query)) { ?>
                
                <tr>
                  <td width="30" class="text-center"><?php echo $no++; ?></td>
                  <td width="300"><?php echo $data['nama_jenis']; ?></td>
                  <td width="70" class="text-center">
                    <div>
                      
                      <a href="?module=form_ubah_jenis&id=<?php echo $data['id_jenis']; ?>" class="btn btn-icon btn-round btn-primary btn-sm mr-md-1" data-toggle="tooltip" data-placement="top" title="Ubah">
                        <i class="fas fa-pencil-alt fa-sm"></i>
                      </a>
                      
                      <a href="modules/jenis/proses_hapus.php?id=<?php echo $data['id_jenis']; ?>" onclick="return confirm('Anda yakin ingin menghapus data jenis barang <?php echo $data['nama_jenis']; ?>?')" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus">
                        <i class="fas fa-trash fa-sm"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
<?php } ?>