<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_GET['id'])) {
    
    $id_jenis = mysqli_real_escape_string($mysqli, $_GET['id']);

    
    
    $query = mysqli_query($mysqli, "SELECT jenis FROM tbl_barang WHERE jenis='$id_jenis'")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    $rows = mysqli_num_rows($query);

    
    
    if ($rows <> 0) {
      
      header('location: ../../main.php?module=jenis&pesan=5');
    }
    
    else {
      
      $delete = mysqli_query($mysqli, "DELETE FROM tbl_jenis WHERE id_jenis='$id_jenis'")
                                       or die('Ada kesalahan pada query delete : ' . mysqli_error($mysqli));
      
      
      if ($delete) {
        
        header('location: ../../main.php?module=jenis&pesan=3');
      }
    }
  }
}
