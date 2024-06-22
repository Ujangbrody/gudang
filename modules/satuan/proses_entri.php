<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_POST['simpan'])) {
    
    $nama_satuan = mysqli_real_escape_string($mysqli, trim($_POST['nama_satuan']));

    
    
    $query = mysqli_query($mysqli, "SELECT nama_satuan FROM tbl_satuan WHERE nama_satuan='$nama_satuan'")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    $rows = mysqli_num_rows($query);

    
    
    if ($rows <> 0) {
      
      header("location: ../../main.php?module=satuan&pesan=4&satuan=$nama_satuan");
    }
    
    else {
      
      $insert = mysqli_query($mysqli, "INSERT INTO tbl_satuan(nama_satuan) 
                                       VALUES('$nama_satuan')")
                                       or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));
      
      
      if ($insert) {
        
        header('location: ../../main.php?module=satuan&pesan=1');
      }
    }
  }
}
