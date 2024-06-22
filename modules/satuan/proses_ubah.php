<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_POST['simpan'])) {
    
    $id_satuan   = mysqli_real_escape_string($mysqli, $_POST['id_satuan']);
    $nama_satuan = mysqli_real_escape_string($mysqli, trim($_POST['nama_satuan']));

    
    
    $query = mysqli_query($mysqli, "SELECT nama_satuan FROM tbl_satuan WHERE nama_satuan='$nama_satuan' AND id_satuan!='$id_satuan'")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    $rows = mysqli_num_rows($query);

    
    
    if ($rows <> 0) {
      
      header("location: ../../main.php?module=satuan&pesan=4&satuan=$nama_satuan");
    }
    
    else {
      
      $update = mysqli_query($mysqli, "UPDATE tbl_satuan
                                       SET nama_satuan='$nama_satuan'
                                       WHERE id_satuan='$id_satuan'")
                                       or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
      
      
      if ($update) {
        
        header('location: ../../main.php?module=satuan&pesan=2');
      }
    }
  }
}
