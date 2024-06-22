<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";

  
  if (isset($_POST['simpan'])) {
    
    if (isset($_SESSION['id_user'])) {
      
      $password_lama       = mysqli_real_escape_string($mysqli, $_POST['password_lama']);
      $password_baru       = mysqli_real_escape_string($mysqli, $_POST['password_baru']);
      $konfirmasi_password = mysqli_real_escape_string($mysqli, $_POST['konfirmasi_password']);
      
      $id_user             = $_SESSION['id_user'];

      
      $query = mysqli_query($mysqli, "SELECT password FROM tbl_user WHERE id_user=$id_user")
                                      or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
      
      $data = mysqli_fetch_assoc($query);
      
      $password_lama_hash = $data['password'];

      
      
      if (password_verify($password_lama, $password_lama_hash)) {
        
        if ($password_baru != $konfirmasi_password) {
          
          header('location: ../../main.php?module=form_ubah_password&pesan=2');
        }
        
        else {
          
          
          $options = [
            'cost' => 12,
          ];
          
          $password_baru_hash = password_hash($password_baru, PASSWORD_DEFAULT, $options);

          
          $update = mysqli_query($mysqli, "UPDATE tbl_user 
                                           SET password='$password_baru_hash' 
                                           WHERE id_user='$id_user'")
                                           or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
          
          
          if ($update) {
            
            header('location: ../../main.php?module=form_ubah_password&pesan=3');
          }
        }
      }
      
      else {
        
        header('location: ../../main.php?module=form_ubah_password&pesan=1');
      }
    }
  }
}
