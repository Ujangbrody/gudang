<?php


if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
  
  header('location: 404.html');
}

else { ?>
  <div class="panel-header bg-primary-gradient">
    <div class="page-inner py-4">
      <div class="page-header text-white">
        
        <h4 class="page-title text-white"><i class="fas fa-file-signature mr-2"></i> Laporan Stok</h4>
        
        <ul class="breadcrumbs">
          <li class="nav-home"><a href="?module=dashboard"><i class="flaticon-home text-white"></i></a></li>
          <li class="separator"><i class="flaticon-right-arrow"></i></li>
          <li class="nav-item"><a>Laporan</a></li>
          <li class="separator"><i class="flaticon-right-arrow"></i></li>
          <li class="nav-item"><a>Stok</a></li>
        </ul>
      </div>
    </div>
  </div>

  <?php
  
  
  if (!isset($_POST['tampil'])) { ?>
    <div class="page-inner mt--5">
      <div class="card">
        <div class="card-header">
          
          <div class="card-title">Filter Data Stok</div>
        </div>
        
        <div class="card-body">
          <form action="?module=laporan_stok" method="post" class="needs-validation" novalidate>
            <div class="row">
              <div class="col-lg-5">
                <div class="form-group">
                  <label>Stok <span class="text-danger">*</span></label>
                  <select name="stok" class="form-control chosen-select" autocomplete="off" required>
                    <option selected disabled value="">-- Pilih --</option>
                    <option value="Seluruh">Seluruh</option>
                    <option value="Minimum">Minimum</option>
                  </select>
                  <div class="invalid-feedback">Stok tidak boleh kosong.</div>
                </div>
              </div>

              <div class="col-lg-3">
                <div class="form-group pt-3">
                  
                  <input type="submit" name="tampil" value="Tampilkan" class="btn btn-primary btn-round btn-block mt-4">
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php
  }
  
  else {
    
    $stok = $_POST['stok'];
  ?>
    <div class="page-inner mt--5">
      <div class="card">
        <div class="card-header">
          
          <div class="card-title">Filter Data Stok</div>
        </div>
        
        <div class="card-body">
          <form action="?module=laporan_stok" method="post" class="needs-validation" novalidate>
            <div class="row">
              <div class="col-lg-5">
                <div class="form-group">
                  <label>Stok <span class="text-danger">*</span></label>
                  <select name="stok" class="form-control chosen-select" autocomplete="off" required>
                    <option value="<?php echo $_POST['stok']; ?>"><?php echo $_POST['stok']; ?></option>
                    <option disabled value="">-- Pilih --</option>
                    <option value="Seluruh">Seluruh</option>
                    <option value="Minimum">Minimum</option>
                  </select>
                  <div class="invalid-feedback">Stok tidak boleh kosong.</div>
                </div>
              </div>

              <div class="col-lg-3">
                <div class="form-group pt-3">
                  
                  <input type="submit" name="tampil" value="Tampilkan" class="btn btn-primary btn-round btn-block mt-4">
                </div>
              </div>

              <div class="col-lg-2 pr-0">
                <div class="form-group pt-3">
                  
                  <a href="modules/laporan-stok/cetak.php?stok=<?php echo $stok; ?>" target="_blank" class="btn btn-warning btn-round btn-block mt-4">
                    <span class="btn-label"><i class="fa fa-print mr-2"></i></span> Cetak
                  </a>
                </div>
              </div>

              <div class="col-lg-2 pl-0">
                <div class="form-group pt-3">
                  
                  <a href="modules/laporan-stok/export.php?stok=<?php echo $stok; ?>" target="_blank" class="btn btn-success btn-round btn-block mt-4">
                    <span class="btn-label"><i class="fa fa-file-excel mr-2"></i></span> Export
                  </a>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <?php
        
        
        if ($stok == 'Seluruh') { ?>
          
          <div class="card-header">
            
            <div class="card-title"><i class="fas fa-file-alt mr-2"></i> Laporan Stok Seluruh Barang</div>
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
                                                  ORDER BY a.id_barang ASC")
                                                  or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
                  
                  while ($data = mysqli_fetch_assoc($query)) { ?>
                    
                    <tr>
                      <td width="50" class="text-center"><?php echo $no++; ?></td>
                      <td width="80" class="text-center"><?php echo $data['id_barang']; ?></td>
                      <td width="200"><?php echo $data['nama_barang']; ?></td>
                      <td width="150"><?php echo $data['nama_jenis']; ?></td>
                      <?php
                      
                      
                      if ($data['stok'] <= $data['stok_minimum']) { ?>
                        
                        <td width="70" class="text-right"><span class="badge badge-warning"><?php echo $data['stok']; ?></span></td>
                      <?php }
                      
                      else { ?>
                        
                        <td width="70" class="text-right"><?php echo $data['stok']; ?></td>
                      <?php } ?>
                      <td width="70"><?php echo $data['nama_satuan']; ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php
        }
        
        else { ?>
          
          <div class="card-header">
            
            <div class="card-title"><i class="fas fa-file-alt mr-2"></i> Laporan Stok Barang yang Mencapai Batas Minimum</div>
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
                      <td width="70" class="text-right"><?php echo $data['stok']; ?></td>
                      <td width="70"><?php echo $data['nama_satuan']; ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
<?php
  }
}
?>