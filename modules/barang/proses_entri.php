<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_POST['simpan'])) {
    
    $id_barang          = mysqli_real_escape_string($mysqli, $_POST['id_barang']);
    $nama_barang        = mysqli_real_escape_string($mysqli, trim($_POST['nama_barang']));
    $jenis              = mysqli_real_escape_string($mysqli, $_POST['jenis']);
    $stok_minimum       = mysqli_real_escape_string($mysqli, $_POST['stok_minimum']);
    $satuan             = mysqli_real_escape_string($mysqli, $_POST['satuan']);

    
    $nama_file          = $_FILES['foto']['name'];
    $tmp_file           = $_FILES['foto']['tmp_name'];
    $extension          = array_pop(explode(".", $nama_file));
    
    $nama_file_enkripsi = sha1(md5(time() . $nama_file)) . '.' . $extension;
    
    $path               = "../../images/" . $nama_file_enkripsi;

    
    
    if (empty($nama_file)) {
      
      $insert = mysqli_query($mysqli, "INSERT INTO tbl_barang(id_barang, nama_barang, jenis, stok_minimum, satuan) 
                                       VALUES('$id_barang', '$nama_barang', '$jenis', '$stok_minimum', '$satuan')")
                                       or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));
      
      
      if ($insert) {
        
        header('location: ../../main.php?module=barang&pesan=1');
      }
    }
    
    else {
      
      
      if (move_uploaded_file($tmp_file, $path)) {
        
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_barang(id_barang, nama_barang, jenis, stok_minimum, satuan, foto) 
                                         VALUES('$id_barang', '$nama_barang', '$jenis', '$stok_minimum', '$satuan', '$nama_file_enkripsi')")
                                         or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));
        
        
        if ($insert) {
          
          header('location: ../../main.php?module=barang&pesan=1');
        }
      }
    }
  }
}
