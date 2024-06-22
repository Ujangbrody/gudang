<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_GET['id'])) {
    
    $id_satuan = mysqli_real_escape_string($mysqli, $_GET['id']);

    
    
    $query = mysqli_query($mysqli, "SELECT satuan FROM tbl_barang WHERE satuan='$id_satuan'")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    $rows = mysqli_num_rows($query);

    
    
    if ($rows <> 0) {
      
      header('location: ../../main.php?module=satuan&pesan=5');
    }
    
    else {
      
      $delete = mysqli_query($mysqli, "DELETE FROM tbl_satuan WHERE id_satuan='$id_satuan'")
                                       or die('Ada kesalahan pada query delete : ' . mysqli_error($mysqli));
      
      
      if ($delete) {
        
        header('location: ../../main.php?module=satuan&pesan=3');
      }
    }
  }
}
