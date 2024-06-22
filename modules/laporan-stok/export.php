<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";
  
  require_once "../../helper/fungsi_tanggal_indo.php";

  
  $stok = $_GET['stok'];

  
  $no = 1;

  
  
  if ($stok == 'Seluruh') {
    
    header("Content-type: application/vnd-ms-excel");
    
    header("Content-Disposition: attachment; filename=Laporan Stok Seluruh Barang.xls");
?>
    
    
    <center>
      <h4>LAPORAN STOK SELURUH BARANG</h4>
    </center>
    
    <table border="1">
      <thead>
        <tr style="background-color:#6861ce;color:#fff">
          <th height="30" align="center" vertical="center">No.</th>
          <th height="30" align="center" vertical="center">ID Barang</th>
          <th height="30" align="center" vertical="center">Nama Barang</th>
          <th height="30" align="center" vertical="center">Jenis Barang</th>
          <th height="30" align="center" vertical="center">Stok</th>
          <th height="30" align="center" vertical="center">Satuan</th>
        </tr>
      </thead>
      <tbody>
        <?php
        
        $query = mysqli_query($mysqli, "SELECT a.id_barang, a.nama_barang, a.jenis, a.stok_minimum, a.stok, a.satuan, b.nama_jenis, c.nama_satuan
                                        FROM tbl_barang as a INNER JOIN tbl_jenis as b INNER JOIN tbl_satuan as c 
                                        ON a.jenis=b.id_jenis AND a.satuan=c.id_satuan 
                                        ORDER BY a.id_barang ASC")
                                        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
        
        while ($data = mysqli_fetch_assoc($query)) { ?>
          
          <tr>
            <td width="70" align="center"><?php echo $no++; ?></td>
            <td width="120" align="center"><?php echo $data['id_barang']; ?></td>
            <td width="300"><?php echo $data['nama_barang']; ?></td>
            <td width="200"><?php echo $data['nama_jenis']; ?></td>
            <?php
            
            
            if ($data['stok'] <= $data['stok_minimum']) { ?>
              
              <td style="background-color:#ffad46;color:#fff" width="100" align="right"><?php echo $data['stok']; ?></td>
            <?php }
            
            else { ?>
              
              <td width="100" align="right"><?php echo $data['stok']; ?></td>
            <?php } ?>
            <td width="150"><?php echo $data['nama_satuan']; ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php
  }
  
  else {
    
    header("Content-type: application/vnd-ms-excel");
    
    header("Content-Disposition: attachment; filename=Laporan Stok Barang Minimum.xls");
  ?>
    
    
    <center>
      <h4>LAPORAN STOK BARANG YANG MENCAPAI BATAS MINIMUM</h4>
    </center>
    
    <table border="1">
      <thead>
        <tr style="background-color:#6861ce;color:#fff">
          <th height="30" align="center" vertical="center">No.</th>
          <th height="30" align="center" vertical="center">ID Barang</th>
          <th height="30" align="center" vertical="center">Nama Barang</th>
          <th height="30" align="center" vertical="center">Jenis Barang</th>
          <th height="30" align="center" vertical="center">Stok</th>
          <th height="30" align="center" vertical="center">Satuan</th>
        </tr>
      </thead>
      <tbody>
        <?php
        
        $query = mysqli_query($mysqli, "SELECT a.id_barang, a.nama_barang, a.jenis, a.stok_minimum, a.stok, a.satuan, b.nama_jenis, c.nama_satuan
                                        FROM tbl_barang as a INNER JOIN tbl_jenis as b INNER JOIN tbl_satuan as c 
                                        ON a.jenis=b.id_jenis AND a.satuan=c.id_satuan 
                                        WHERE a.stok<=a.stok_minimum ORDER BY a.id_barang ASC")
                                        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
        
        while ($data = mysqli_fetch_assoc($query)) { ?>
          
          <tr>
            <td width="70" align="center"><?php echo $no++; ?></td>
            <td width="120" align="center"><?php echo $data['id_barang']; ?></td>
            <td width="300"><?php echo $data['nama_barang']; ?></td>
            <td width="200"><?php echo $data['nama_jenis']; ?></td>
            <td width="100" align="right"><?php echo $data['stok']; ?></td>
            <td width="150"><?php echo $data['nama_satuan']; ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php } ?>
  <br>
  <div style="text-align:right">............, <?php echo tanggal_indo(date('Y-m-d')); ?></div>
<?php } ?>