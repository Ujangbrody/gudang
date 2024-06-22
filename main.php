<?php
session_start();      



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: login.php?pesan=2');
}

else { ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Aplikasi Warehouse Management System dengan PHP 8 dan MySQLi" />
    <meta name="author" content="dewi sri ana riska" />

    
    <title>Aplikasi Warehouse Management System dengan PHP 8 dan MySQLi</title>

    
    <link rel="icon" href="assets/img/logodash.png" type="image/x-icon" />

    
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: {
          "families": ["Lato:300,400,700,900"]
        },
        custom: {
          "families": ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"],
          urls: ['assets/css/fonts.min.css']
        },
        active: function() {
          sessionStorage.fonts = true;
        }
      });
    </script>

    
    <link rel="stylesheet" href="assets/js/plugin/datepicker/css/bootstrap-datepicker.css">
    
    <link rel="stylesheet" href="assets/js/plugin/chosen/css/chosen.css">

    
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/atlantis.min.css">

    
    <script src="assets/js/core/jquery.3.2.1.min.js"></script>
  </head>

  <body>
    <div class="wrapper">
      <div class="main-header">
        
        <div class="logo-header" data-background-color="light-blue">
          
          <a href="?module=dashboard" class="logo">
            <div class="navbar-brand">
              <span><i class="fab fa-gofore fa-lg text-warning"></i></span>
              <span class="text-white">Gudang Kaca</span>
            </div>
          </a>
          
          <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse" data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">
              <i class="icon-menu"></i>
            </span>
          </button>
          <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
          <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar">
              <i class="icon-menu"></i>
            </button>
          </div>
        </div>
        

        
        <nav class="navbar navbar-header navbar-expand-lg" data-background-color="light-blue">
          <div class="container-fluid">
            <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
              
              <li class="nav-item dropdown hidden-caret">
                <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="javascript:void(0)" aria-expanded="false">
                  <div class="avatar-sm-top mt-1">
                    <img src="assets/img/human.png" alt="image profile" class="avatar-img rounded-circle">
                    <i class="fas fa-angle-down avatar-title"></i>
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-user animated fadeIn">
                  <li>
                    <div class="user-box">
                      <div class="avatar-lg"><img src="assets/img/fotoadmin.jpeg" alt="image profile" class="avatar-img rounded"></div>
                      <div class="u-text pt-1">
                        <h4><?php echo $_SESSION['nama_user']; ?></h4>
                        <p class="text-muted"><?php echo $_SESSION['hak_akses']; ?></p>
                      </div>
                    </div>
                  </li>
                  
                  <li>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="?module=form_ubah_password">
                      <i class="fas fa-user-lock mr-1"></i> Ubah Password
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalLogout">
                      <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
        
      </div>

      
      <div class="sidebar sidebar-style-2">
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            
            <div class="user">
              <div class="avatar-sm float-left mr-2">
                <img src="assets/img/human.png" alt="image profile" class="avatar-img rounded-circle">
              </div>
              <div class="info">
                <a>
                  <span>
                    <?php echo $_SESSION['nama_user']; ?>
                    <span class="user-level"><?php echo $_SESSION['hak_akses']; ?></span>
                  </span>
                </a>
              </div>
            </div>
            
            <ul class="nav nav-primary">

              
              <?php include "sidebar_menu.php"; ?>

            </ul>
          </div>
        </div>
      </div>
      

      <div class="main-panel">
        
        <div class="content">

          
          <?php include "content.php"; ?>

        </div>
        

        
        <footer class="footer">
          <div class="container-fluid">
            <div class="copyright ml-auto">
              Copyright &copy; 2024 - <a href="#" class="text-primary">Dewi Sri Ana Riska</a>.
            </div>
          </div>
        </footer>
        
      </div>
    </div>

    
    <div class="modal fade" id="modalLogout" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-sign-out-alt mr-2"></i>Logout</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">Apakah Anda yakin ingin logout?</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default btn-round" data-dismiss="modal">Batal</button>
            <a href="logout.php" class="btn btn-danger btn-round">Ya, Logout</a>
          </div>
        </div>
      </div>
    </div>

    
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>

    
    <script src="assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <script src="assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>

    
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>
    
    <script src="assets/js/plugin/datepicker/js/bootstrap-datepicker.min.js"></script>
    
    <script src="assets/js/plugin/chosen/js/chosen.jquery.js"></script>

    
    <script src="assets/js/atlantis.min.js"></script>

    
    <script src="assets/js/plugin.js"></script>
    <script src="assets/js/form-validation.js"></script>
  </body>

  </html>
<?php } ?>