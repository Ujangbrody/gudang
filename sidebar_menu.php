9<?php


if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
  
  header('location: 404.html');
}

else {
  
  
  if ($_SESSION['hak_akses'] == 'Administrator') {
    
    
    if ($_GET['module'] == 'dashboard') { ?>
      <li class="nav-item active">
        <a href="?module=dashboard">
          <i class="fas fa-home"></i>
          <p>Dashboard</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=dashboard">
          <i class="fas fa-home"></i>
          <p>Dashboard</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'barang' || $_GET['module'] == 'tampil_detail_barang' || $_GET['module'] == 'form_entri_barang' || $_GET['module'] == 'form_ubah_barang') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Master</h4>
      </li>

      <li class="nav-item active submenu">
        <a data-toggle="collapse" href="#barang">
          <i class="fas fa-clone"></i>
          <p>Barang</p>
          <span class="caret"></span>
        </a>

        <div class="collapse show" id="barang">
          <ul class="nav nav-collapse">
            <li class="active">
              <a href="?module=barang">
                <span class="sub-item">Data Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=jenis">
                <span class="sub-item">Jenis Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=satuan">
                <span class="sub-item">Satuan</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    <?php
    }
    
    elseif ($_GET['module'] == 'jenis' || $_GET['module'] == 'form_entri_jenis' || $_GET['module'] == 'form_ubah_jenis') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Master</h4>
      </li>

      <li class="nav-item active submenu">
        <a data-toggle="collapse" href="#barang">
          <i class="fas fa-clone"></i>
          <p>Barang</p>
          <span class="caret"></span>
        </a>

        <div class="collapse show" id="barang">
          <ul class="nav nav-collapse">
            <li>
              <a href="?module=barang">
                <span class="sub-item">Data Barang</span>
              </a>
            </li>
            <li class="active">
              <a href="?module=jenis">
                <span class="sub-item">Jenis Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=satuan">
                <span class="sub-item">Satuan</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    <?php
    }
    
    elseif ($_GET['module'] == 'satuan' || $_GET['module'] == 'form_entri_satuan' || $_GET['module'] == 'form_ubah_satuan') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Master</h4>
      </li>

      <li class="nav-item active submenu">
        <a data-toggle="collapse" href="#barang">
          <i class="fas fa-clone"></i>
          <p>Barang</p>
          <span class="caret"></span>
        </a>

        <div class="collapse show" id="barang">
          <ul class="nav nav-collapse">
            <li>
              <a href="?module=barang">
                <span class="sub-item">Data Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=jenis">
                <span class="sub-item">Jenis Barang</span>
              </a>
            </li>
            <li class="active">
              <a href="?module=satuan">
                <span class="sub-item">Satuan</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Master</h4>
      </li>

      <li class="nav-item">
        <a data-toggle="collapse" href="#barang">
          <i class="fas fa-clone"></i>
          <p>Barang</p>
          <span class="caret"></span>
        </a>

        <div class="collapse" id="barang">
          <ul class="nav nav-collapse">
            <li>
              <a href="?module=barang">
                <span class="sub-item">Data Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=jenis">
                <span class="sub-item">Jenis Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=satuan">
                <span class="sub-item">Satuan</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'barang_masuk' || $_GET['module'] == 'form_entri_barang_masuk') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Transaksi</h4>
      </li>

      <li class="nav-item active">
        <a href="?module=barang_masuk">
          <i class="fas fa-sign-in-alt"></i>
          <p>Barang Masuk</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Transaksi</h4>
      </li>

      <li class="nav-item">
        <a href="?module=barang_masuk">
          <i class="fas fa-sign-in-alt"></i>
          <p>Barang Masuk</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'barang_keluar' || $_GET['module'] == 'form_entri_barang_keluar') { ?>
      <li class="nav-item active">
        <a href="?module=barang_keluar">
          <i class="fas fa-sign-out-alt"></i>
          <p>Barang Keluar</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=barang_keluar">
          <i class="fas fa-sign-out-alt"></i>
          <p>Barang Keluar</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'laporan_stok') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Laporan</h4>
      </li>

      <li class="nav-item active">
        <a href="?module=laporan_stok">
          <i class="fas fa-file-signature"></i>
          <p>Laporan Stok</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Laporan</h4>
      </li>

      <li class="nav-item">
        <a href="?module=laporan_stok">
          <i class="fas fa-file-signature"></i>
          <p>Laporan Stok</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'laporan_barang_masuk') { ?>
      <li class="nav-item active">
        <a href="?module=laporan_barang_masuk">
          <i class="fas fa-file-import"></i>
          <p>Laporan Barang Masuk</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=laporan_barang_masuk">
          <i class="fas fa-file-import"></i>
          <p>Laporan Barang Masuk</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'laporan_barang_keluar') { ?>
      <li class="nav-item active">
        <a href="?module=laporan_barang_keluar">
          <i class="fas fa-file-export"></i>
          <p>Laporan Barang Keluar</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=laporan_barang_keluar">
          <i class="fas fa-file-export"></i>
          <p>Laporan Barang Keluar</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'user' || $_GET['module'] == 'form_entri_user' || $_GET['module'] == 'form_ubah_user') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Pengaturan</h4>
      </li>

      <li class="nav-item active">
        <a href="?module=user">
          <i class="fas fa-user"></i>
          <p>Manajemen User</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Pengaturan</h4>
      </li>

      <li class="nav-item">
        <a href="?module=user">
          <i class="fas fa-user"></i>
          <p>Manajemen User</p>
        </a>
      </li>
    <?php
    }
  }
  
  elseif ($_SESSION['hak_akses'] == 'Admin Gudang') {
    
    
    if ($_GET['module'] == 'dashboard') { ?>
      <li class="nav-item active">
        <a href="?module=dashboard">
          <i class="fas fa-home"></i>
          <p>Dashboard</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=dashboard">
          <i class="fas fa-home"></i>
          <p>Dashboard</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'barang' || $_GET['module'] == 'tampil_detail_barang' || $_GET['module'] == 'form_entri_barang' || $_GET['module'] == 'form_ubah_barang') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Master</h4>
      </li>

      <li class="nav-item active submenu">
        <a data-toggle="collapse" href="#barang">
          <i class="fas fa-clone"></i>
          <p>Barang</p>
          <span class="caret"></span>
        </a>

        <div class="collapse show" id="barang">
          <ul class="nav nav-collapse">
            <li class="active">
              <a href="?module=barang">
                <span class="sub-item">Data Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=jenis">
                <span class="sub-item">Jenis Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=satuan">
                <span class="sub-item">Satuan</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    <?php
    }
    
    elseif ($_GET['module'] == 'jenis' || $_GET['module'] == 'form_entri_jenis' || $_GET['module'] == 'form_ubah_jenis') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Master</h4>
      </li>

      <li class="nav-item active submenu">
        <a data-toggle="collapse" href="#barang">
          <i class="fas fa-clone"></i>
          <p>Barang</p>
          <span class="caret"></span>
        </a>

        <div class="collapse show" id="barang">
          <ul class="nav nav-collapse">
            <li>
              <a href="?module=barang">
                <span class="sub-item">Data Barang</span>
              </a>
            </li>
            <li class="active">
              <a href="?module=jenis">
                <span class="sub-item">Jenis Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=satuan">
                <span class="sub-item">Satuan</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    <?php
    }
    
    elseif ($_GET['module'] == 'satuan' || $_GET['module'] == 'form_entri_satuan' || $_GET['module'] == 'form_ubah_satuan') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Master</h4>
      </li>

      <li class="nav-item active submenu">
        <a data-toggle="collapse" href="#barang">
          <i class="fas fa-clone"></i>
          <p>Barang</p>
          <span class="caret"></span>
        </a>

        <div class="collapse show" id="barang">
          <ul class="nav nav-collapse">
            <li>
              <a href="?module=barang">
                <span class="sub-item">Data Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=jenis">
                <span class="sub-item">Jenis Barang</span>
              </a>
            </li>
            <li class="active">
              <a href="?module=satuan">
                <span class="sub-item">Satuan</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Master</h4>
      </li>

      <li class="nav-item">
        <a data-toggle="collapse" href="#barang">
          <i class="fas fa-clone"></i>
          <p>Barang</p>
          <span class="caret"></span>
        </a>

        <div class="collapse" id="barang">
          <ul class="nav nav-collapse">
            <li>
              <a href="?module=barang">
                <span class="sub-item">Data Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=jenis">
                <span class="sub-item">Jenis Barang</span>
              </a>
            </li>
            <li>
              <a href="?module=satuan">
                <span class="sub-item">Satuan</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'barang_masuk' || $_GET['module'] == 'form_entri_barang_masuk') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Transaksi</h4>
      </li>

      <li class="nav-item active">
        <a href="?module=barang_masuk">
          <i class="fas fa-sign-in-alt"></i>
          <p>Barang Masuk</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Transaksi</h4>
      </li>

      <li class="nav-item">
        <a href="?module=barang_masuk">
          <i class="fas fa-sign-in-alt"></i>
          <p>Barang Masuk</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'barang_keluar' || $_GET['module'] == 'form_entri_barang_keluar') { ?>
      <li class="nav-item active">
        <a href="?module=barang_keluar">
          <i class="fas fa-sign-out-alt"></i>
          <p>Barang Keluar</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=barang_keluar">
          <i class="fas fa-sign-out-alt"></i>
          <p>Barang Keluar</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'laporan_stok') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Laporan</h4>
      </li>

      <li class="nav-item active">
        <a href="?module=laporan_stok">
          <i class="fas fa-file-signature"></i>
          <p>Laporan Stok</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Laporan</h4>
      </li>

      <li class="nav-item">
        <a href="?module=laporan_stok">
          <i class="fas fa-file-signature"></i>
          <p>Laporan Stok</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'laporan_barang_masuk') { ?>
      <li class="nav-item active">
        <a href="?module=laporan_barang_masuk">
          <i class="fas fa-file-import"></i>
          <p>Laporan Barang Masuk</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=laporan_barang_masuk">
          <i class="fas fa-file-import"></i>
          <p>Laporan Barang Masuk</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'laporan_barang_keluar') { ?>
      <li class="nav-item active">
        <a href="?module=laporan_barang_keluar">
          <i class="fas fa-file-export"></i>
          <p>Laporan Barang Keluar</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=laporan_barang_keluar">
          <i class="fas fa-file-export"></i>
          <p>Laporan Barang Keluar</p>
        </a>
      </li>
    <?php
    }
  }
  
  elseif ($_SESSION['hak_akses'] == 'Kepala Gudang') {
    
    
    if ($_GET['module'] == 'dashboard') { ?>
      <li class="nav-item active">
        <a href="?module=dashboard">
          <i class="fas fa-home"></i>
          <p>Dashboard</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=dashboard">
          <i class="fas fa-home"></i>
          <p>Dashboard</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'laporan_stok') { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Laporan</h4>
      </li>

      <li class="nav-item active">
        <a href="?module=laporan_stok">
          <i class="fas fa-file-signature"></i>
          <p>Laporan Stok</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-section">
        <span class="sidebar-mini-icon">
          <i class="fa fa-ellipsis-h"></i>
        </span>
        <h4 class="text-section">Laporan</h4>
      </li>

      <li class="nav-item">
        <a href="?module=laporan_stok">
          <i class="fas fa-file-signature"></i>
          <p>Laporan Stok</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'laporan_barang_masuk') { ?>
      <li class="nav-item active">
        <a href="?module=laporan_barang_masuk">
          <i class="fas fa-file-import"></i>
          <p>Laporan Barang Masuk</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=laporan_barang_masuk">
          <i class="fas fa-file-import"></i>
          <p>Laporan Barang Masuk</p>
        </a>
      </li>
    <?php
    }

    
    if ($_GET['module'] == 'laporan_barang_keluar') { ?>
      <li class="nav-item active">
        <a href="?module=laporan_barang_keluar">
          <i class="fas fa-file-export"></i>
          <p>Laporan Barang Keluar</p>
        </a>
      </li>
    <?php
    }
    
    else { ?>
      <li class="nav-item">
        <a href="?module=laporan_barang_keluar">
          <i class="fas fa-file-export"></i>
          <p>Laporan Barang Keluar</p>
        </a>
      </li>
<?php
    }
  }
}
?>