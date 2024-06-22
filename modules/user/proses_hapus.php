<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_GET['id'])) {
    
    $id_user = mysqli_real_escape_string($mysqli, $_GET['id']);

    
    $delete = mysqli_query($mysqli, "DELETE FROM tbl_user WHERE id_user='$id_user'")
                                     or die('Ada kesalahan pada query delete : ' . mysqli_error($mysqli));
    
    
    if ($delete) {
      
      header('location: ../../main.php?module=user&pesan=3');
    }
  }
}
