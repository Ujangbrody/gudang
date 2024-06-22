<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_POST['simpan'])) {
    
    $id_transaksi  = mysqli_real_escape_string($mysqli, $_POST['id_transaksi']);
    $tanggal       = mysqli_real_escape_string($mysqli, trim($_POST['tanggal']));
    $barang        = mysqli_real_escape_string($mysqli, $_POST['barang']);
    $jumlah        = mysqli_real_escape_string($mysqli, $_POST['jumlah']);

    
    $tanggal_keluar = date('Y-m-d', strtotime($tanggal));

    
    $insert = mysqli_query($mysqli, "INSERT INTO tbl_barang_keluar(id_transaksi, tanggal, barang, jumlah) 
                                     VALUES('$id_transaksi', '$tanggal_keluar', '$barang', '$jumlah')")
                                     or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));
    
    
    if ($insert) {
      
      header('location: ../../main.php?module=barang_keluar&pesan=1');
    }
  }
}
