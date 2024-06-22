<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_POST['simpan'])) {
    
    $id_user   = mysqli_real_escape_string($mysqli, $_POST['id_user']);
    $nama_user = mysqli_real_escape_string($mysqli, trim($_POST['nama_user']));
    $username  = mysqli_real_escape_string($mysqli, trim($_POST['username']));
    $password  = mysqli_real_escape_string($mysqli, $_POST['password']);
    $hak_akses = mysqli_real_escape_string($mysqli, trim($_POST['hak_akses']));

    
    
    $query = mysqli_query($mysqli, "SELECT username FROM tbl_user WHERE username='$username' AND id_user!='$id_user'")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    $rows = mysqli_num_rows($query);

    
    
    if ($rows <> 0) {
      
      header("location: ../../main.php?module=user&pesan=4&username=$username");
    }
    
    else {
      
      
      if (empty($password)) {
        
        $update = mysqli_query($mysqli, "UPDATE tbl_user
                                         SET nama_user='$nama_user', username='$username', hak_akses='$hak_akses'
                                         WHERE id_user='$id_user'")
                                         or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
      }
      
      else {
        
        
        $option = [
          'cost' => 12,
        ];
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT, $option);

        
        $update = mysqli_query($mysqli, "UPDATE tbl_user
                                         SET nama_user='$nama_user', username='$username', password='$password_hash', hak_akses='$hak_akses'
                                         WHERE id_user='$id_user'")
                                         or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
      }

      
      
      if ($update) {
        
        header('location: ../../main.php?module=user&pesan=2');
      }
    }
  }
}
