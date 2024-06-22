<?php


if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
  
  header('location: 404.html');
}

else {
  
  require_once "config/database.php";

  
  
  if ($_GET['module'] == 'dashboard') {
    
    include "modules/dashboard/tampil_data.php";
  }
  
  elseif ($_GET['module'] == 'barang' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/barang/tampil_data.php";
  }
  
  elseif ($_GET['module'] == 'form_entri_barang' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/barang/form_entri.php";
  }
  
  elseif ($_GET['module'] == 'form_ubah_barang' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/barang/form_ubah.php";
  }
  
  elseif ($_GET['module'] == 'tampil_detail_barang' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/barang/tampil_detail.php";
  }
  
  elseif ($_GET['module'] == 'jenis' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/jenis/tampil_data.php";
  }
  
  elseif ($_GET['module'] == 'form_entri_jenis' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/jenis/form_entri.php";
  }
  
  elseif ($_GET['module'] == 'form_ubah_jenis' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/jenis/form_ubah.php";
  }
  
  elseif ($_GET['module'] == 'satuan' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/satuan/tampil_data.php";
  }
  
  elseif ($_GET['module'] == 'form_entri_satuan' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/satuan/form_entri.php";
  }
  
  elseif ($_GET['module'] == 'form_ubah_satuan' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/satuan/form_ubah.php";
  }
  
  elseif ($_GET['module'] == 'barang_masuk' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/barang-masuk/tampil_data.php";
  }
  
  elseif ($_GET['module'] == 'form_entri_barang_masuk' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/barang-masuk/form_entri.php";
  }
  
  elseif ($_GET['module'] == 'barang_keluar' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/barang-keluar/tampil_data.php";
  }
  
  elseif ($_GET['module'] == 'form_entri_barang_keluar' && $_SESSION['hak_akses'] != 'Kepala Gudang') {
    
    include "modules/barang-keluar/form_entri.php";
  }
  
  elseif ($_GET['module'] == 'laporan_stok') {
    
    include "modules/laporan-stok/tampil_data.php";
  }
  
  elseif ($_GET['module'] == 'laporan_barang_masuk') {
    
    include "modules/laporan-barang-masuk/tampil_data.php";
  }
  
  elseif ($_GET['module'] == 'laporan_barang_keluar') {
    
    include "modules/laporan-barang-keluar/tampil_data.php";
  }
  
  elseif ($_GET['module'] == 'user' && $_SESSION['hak_akses'] == 'Administrator') {
    
    include "modules/user/tampil_data.php";
  }
  
  elseif ($_GET['module'] == 'form_entri_user' && $_SESSION['hak_akses'] == 'Administrator') {
    
    include "modules/user/form_entri.php";
  }
  
  elseif ($_GET['module'] == 'form_ubah_user' && $_SESSION['hak_akses'] == 'Administrator') {
    
    include "modules/user/form_ubah.php";
  }
  
  elseif ($_GET['module'] == 'form_ubah_password') {
    
    include "modules/password/form_ubah.php";
  }
}
