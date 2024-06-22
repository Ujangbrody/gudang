<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_GET['id'])) {
    
    $id_barang = mysqli_real_escape_string($mysqli, $_GET['id']);

    
    
    $query = mysqli_query($mysqli, "SELECT barang FROM tbl_barang_masuk WHERE barang='$id_barang'")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    $rows = mysqli_num_rows($query);

    
    
    if ($rows <> 0) {
      
      header('location: ../../main.php?module=barang&pesan=4');
    }
    
    else {
      
      
      $query = mysqli_query($mysqli, "SELECT foto FROM tbl_barang WHERE id_barang='$id_barang'")
                                      or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
      
      $data = mysqli_fetch_assoc($query);
      
      $foto = $data['foto'];

      
      if (!empty($foto)) {
        
        $hapus_file = unlink("../../images/$foto");
      }

      
      $delete = mysqli_query($mysqli, "DELETE FROM tbl_barang WHERE id_barang='$id_barang'")
                                       or die('Ada kesalahan pada query delete : ' . mysqli_error($mysqli));
      
      
      if ($delete) {
        
        header('location: ../../main.php?module=barang&pesan=3');
      }
    }
  }
}
