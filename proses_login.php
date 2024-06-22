<?php

require_once "config/database.php";


$username = mysqli_real_escape_string($mysqli, $_POST['username']);
$password = mysqli_real_escape_string($mysqli, $_POST['password']);


$query = mysqli_query($mysqli, "SELECT * FROM tbl_user WHERE username='$username'")
                                or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

$rows = mysqli_num_rows($query);



if ($rows <> 0) {
  
  $data = mysqli_fetch_assoc($query);
  
  $password_hash = $data['password'];

  
  
  if (password_verify($password, $password_hash)) {
    
    session_start();
    
    $_SESSION['id_user']   = $data['id_user'];
    $_SESSION['nama_user'] = $data['nama_user'];
    $_SESSION['username']  = $data['username'];
    $_SESSION['password']  = $data['password'];
    $_SESSION['hak_akses'] = $data['hak_akses'];

    
    header('location: main.php?module=dashboard&pesan=1');
  }
  
  else {
    
    header('location: login.php?pesan=1');
  }
}

else {
  
  header('location: login.php?pesan=1');
}
