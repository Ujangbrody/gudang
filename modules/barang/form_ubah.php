<?php


if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
  
  header('location: 404.html');
}

else {
  
  if (isset($_GET['id'])) {
    
    $id_barang = $_GET['id'];

    
    $query = mysqli_query($mysqli, "SELECT a.id_barang, a.nama_barang, a.jenis, a.stok_minimum, a.stok, a.satuan, a.foto, b.nama_jenis, c.nama_satuan
                                    FROM tbl_barang as a INNER JOIN tbl_jenis as b INNER JOIN tbl_satuan as c 
                                    ON a.jenis=b.id_jenis AND a.satuan=c.id_satuan 
                                    WHERE a.id_barang='$id_barang'")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    $data = mysqli_fetch_assoc($query);
  }
?>
  
  <div id="pesan"></div>

  <div class="panel-header bg-primary-gradient">
    <div class="page-inner py-4">
      <div class="page-header text-white">
        
        <h4 class="page-title text-white"><i class="fas fa-clone mr-2"></i> Barang</h4>
        
        <ul class="breadcrumbs">
          <li class="nav-home"><a href="?module=dashboard"><i class="flaticon-home text-white"></i></a></li>
          <li class="separator"><i class="flaticon-right-arrow"></i></li>
          <li class="nav-item"><a href="?module=barang" class="text-white">Barang</a></li>
          <li class="separator"><i class="flaticon-right-arrow"></i></li>
          <li class="nav-item"><a>Ubah</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="page-inner mt--5">
    <div class="card">
      <div class="card-header">
        
        <div class="card-title">Ubah Data Barang</div>
      </div>
      
      <form action="modules/barang/proses_ubah.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="card-body">
          <div class="row">
            <div class="col-md-7">
              <div class="form-group">
                <label>ID Barang <span class="text-danger">*</span></label>
                <input type="text" name="id_barang" class="form-control" value="<?php echo $data['id_barang']; ?>" readonly>
              </div>

              <div class="form-group">
                <label>Nama Barang <span class="text-danger">*</span></label>
                <input type="text" name="nama_barang" class="form-control" autocomplete="off" value="<?php echo $data['nama_barang']; ?>" required>
                <div class="invalid-feedback">Nama barang tidak boleh kosong.</div>
              </div>

              <div class="form-group">
                <label>Jenis Barang <span class="text-danger">*</span></label>
                <select name="jenis" class="form-control chosen-select" autocomplete="off" required>
                  <option value="<?php echo $data['jenis']; ?>"><?php echo $data['nama_jenis']; ?></option>
                  <option disabled value="">-- Pilih --</option>
                  <?php
                  
                  $query_jenis = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC")
                                                        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
                  
                  while ($data_jenis = mysqli_fetch_assoc($query_jenis)) {
                    
                    echo "<option value='$data_jenis[id_jenis]'>$data_jenis[nama_jenis]</option>";
                  }
                  ?>
                </select>
                <div class="invalid-feedback">Jenis Barang tidak boleh kosong.</div>
              </div>

              <div class="form-group">
                <label>Stok Minimum <span class="text-danger">*</span></label>
                <input type="text" name="stok_minimum" class="form-control" autocomplete="off" onKeyPress="return goodchars(event,'0123456789',this)" value="<?php echo $data['stok_minimum']; ?>" required>
                <div class="invalid-feedback">Stok minimum tidak boleh kosong.</div>
              </div>

              <div class="form-group">
                <label>Satuan <span class="text-danger">*</span></label>
                <select name="satuan" class="form-control chosen-select" autocomplete="off" required>
                  <option value="<?php echo $data['satuan']; ?>"><?php echo $data['nama_satuan']; ?></option>
                  <option disabled value="">-- Pilih --</option>
                  <?php
                  
                  $query_satuan = mysqli_query($mysqli, "SELECT * FROM tbl_satuan ORDER BY nama_satuan ASC")
                                                         or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
                  
                  while ($data_satuan = mysqli_fetch_assoc($query_satuan)) {
                    
                    echo "<option value='$data_satuan[id_satuan]'>$data_satuan[nama_satuan]</option>";
                  }
                  ?>
                </select>
                <div class="invalid-feedback">Satuan tidak boleh kosong.</div>
              </div>
            </div>
            <div class="col-md-5 ml-auto">
              <div class="form-group">
                <label>Foto Barang</label>
                <input type="file" id="foto" name="foto" class="form-control" autocomplete="off">
                <div class="card mt-3 mb-3">
                  <div class="card-body text-center">
                    <?php
                    
                    
                    if (is_null($data['foto'])) { ?>
                      
                      <img style="max-height:200px" src="images/no_image.png" class="img-fluid foto-preview" alt="Foto Barang">
                    <?php
                    }
                    
                    else { ?>
                      
                      <img style="max-height:200px" src="images/<?php echo $data['foto']; ?>" class="img-fluid foto-preview" alt="Foto Barang">
                    <?php } ?>
                  </div>
                </div>
                <small class="form-text text-primary">
                  Keterangan : <br>
                  - Tipe file yang bisa diunggah adalah *.jpg atau *.png. <br>
                  - Ukuran file yang bisa diunggah maksimal 1 Mb.
                </small>
              </div>
            </div>
          </div>
        </div>
        <div class="card-action">
          
          <input type="submit" name="simpan" value="Simpan" class="btn btn-primary btn-round pl-4 pr-4 mr-2">
          
          <a href="?module=barang" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
        </div>
      </form>
    </div>
  </div>

  <script type="text/javascript">
    $(document).ready(function() {
      
      $('#foto').change(function() {
        
        var filePath = $('#foto').val();
        var fileSize = $('#foto')[0].files[0].size;
        
        var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

        
        if (!allowedExtensions.exec(filePath)) {
          
          $('#pesan').html('<div class="alert alert-notify alert-danger alert-dismissible fade show" role="alert"><span data-notify="icon" class="fas fa-times"></span><span data-notify="title" class="text-danger">Gagal!</span> <span data-notify="message">Tipe file tidak sesuai. Harap unggah file yang memiliki tipe *.jpg atau *.png.</span><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
          
          $('#foto').val('');
          
          $('.foto-preview').attr('src', 'images/no_image.png');

          return false;
        }
        
        else if (fileSize > 1000000) {
          
          $('#pesan').html('<div class="alert alert-notify alert-danger alert-dismissible fade show" role="alert"><span data-notify="icon" class="fas fa-times"></span><span data-notify="title" class="text-danger">Gagal!</span> <span data-notify="message">Ukuran file lebih dari 1 Mb. Harap unggah file yang memiliki ukuran maksimal 1 Mb.</span><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
          
          $('#foto').val('');
          
          $('.foto-preview').attr('src', 'images/no_image.png');

          return false;
        }
        
        else {
          var fileInput = document.getElementById('foto');

          if (fileInput.files && fileInput.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
              
              $('.foto-preview').attr('src', e.target.result);
            };
          };
          reader.readAsDataURL(fileInput.files[0]);
        }
      });
    });
  </script>
<?php } ?>