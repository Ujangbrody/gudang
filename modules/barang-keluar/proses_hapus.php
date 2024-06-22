<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_GET['id'])) {
    
    $id_transaksi = mysqli_real_escape_string($mysqli, $_GET['id']);

    
    $delete = mysqli_query($mysqli, "DELETE FROM tbl_barang_keluar WHERE id_transaksi='$id_transaksi'")
                                     or die('Ada kesalahan pada query delete : ' . mysqli_error($mysqli));
    
    
    if ($delete) {
      
      header('location: ../../main.php?module=barang_keluar&pesan=2');
    }
  }
}
