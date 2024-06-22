<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_POST['simpan'])) {
    
    $id_jenis   = mysqli_real_escape_string($mysqli, $_POST['id_jenis']);
    $nama_jenis = mysqli_real_escape_string($mysqli, trim($_POST['nama_jenis']));

    
    
    $query = mysqli_query($mysqli, "SELECT nama_jenis FROM tbl_jenis WHERE nama_jenis='$nama_jenis' AND id_jenis!='$id_jenis'")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    $rows = mysqli_num_rows($query);

    
    
    if ($rows <> 0) {
      
      header("location: ../../main.php?module=jenis&pesan=4&jenis=$nama_jenis");
    }
    
    else {
      
      $update = mysqli_query($mysqli, "UPDATE tbl_jenis
                                       SET nama_jenis='$nama_jenis'
                                       WHERE id_jenis='$id_jenis'")
                                       or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
      
      
      if ($update) {
        
        header('location: ../../main.php?module=jenis&pesan=2');
      }
    }
  }
}
