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
              <span data-notify="message">Data user berhasil disimpan.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 2) {
      
      echo '<div class="alert alert-notify alert-success alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-check"></span> 
              <span data-notify="title" class="text-success">Sukses!</span> 
              <span data-notify="message">Data user berhasil diubah.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 3) {
      
      echo '<div class="alert alert-notify alert-success alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-check"></span> 
              <span data-notify="title" class="text-success">Sukses!</span> 
              <span data-notify="message">Data user berhasil dihapus.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 4) {
      
      $username = $_GET['username'];
      
      echo '<div class="alert alert-notify alert-danger alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-times"></span> 
              <span data-notify="title" class="text-danger">Gagal!</span> 
              <span data-notify="message">Username <strong>' . $username . '</strong> sudah ada. Silahkan ganti username yang Anda masukan.</span>
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
          
          <h4 class="page-title text-white"><i class="fas fa-user mr-2"></i> Manajemen User</h4>
          
          <ul class="breadcrumbs">
            <li class="nav-home"><a href="?module=dashboard"><i class="flaticon-home text-white"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="?module=user" class="text-white">User</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a>Data</a></li>
          </ul>
        </div>
        <div class="ml-md-auto py-2 py-md-0">
          
          <a href="?module=form_entri_user" class="btn btn-primary btn-round mr-2">
            <span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Data
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-inner mt--5">
    <div class="card">
      <div class="card-header">
        
        <div class="card-title">Data User</div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          
          <table id="basic-datatables" class="display table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center">No.</th>
                <th class="text-center">Nama User</th>
                <th class="text-center">Username</th>
                <th class="text-center">Hak Akses</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              
              $no = 1;
              
              $query = mysqli_query($mysqli, "SELECT id_user, nama_user, username, hak_akses FROM tbl_user ORDER BY id_user DESC")
                                              or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
              
              while ($data = mysqli_fetch_assoc($query)) { ?>
                
                <tr>
                  <td width="30" class="text-center"><?php echo $no++; ?></td>
                  <td width="180"><?php echo $data['nama_user']; ?></td>
                  <td width="180"><?php echo $data['username']; ?></td>
                  <td width="100"><?php echo $data['hak_akses']; ?></td>
                  <td width="70" class="text-center">
                    <div>
                      
                      <a href="?module=form_ubah_user&id=<?php echo $data['id_user']; ?>" class="btn btn-icon btn-round btn-primary btn-sm mr-md-1" data-toggle="tooltip" data-placement="top" title="Ubah">
                        <i class="fas fa-pencil-alt fa-sm"></i>
                      </a>
                      
                      <a href="modules/user/proses_hapus.php?id=<?php echo $data['id_user']; ?>" onclick="return confirm('Anda yakin ingin menghapus data user dengan username <?php echo $data['username']; ?>?')" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus">
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