
  <title>Aplikasi Warehouse Management System</title>

  
  <link rel="icon" href="assets/img/logoapk.png" type="image/x-icon" />

  
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

  
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/atlantis.min.css">
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="login">
  <?php
  
  
  if (isset($_GET['pesan'])) {
    
    if ($_GET['pesan'] == 1) {
      
      echo '<div class="alert alert-notify alert-danger alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-times"></span> 
              <span data-notify="title" class="text-danger">Gagal Login!</span> 
              <span data-notify="message">Username atau Password salah. Cek kembali Username dan Password Anda.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 2) {
      
      echo '<div class="alert alert-notify alert-warning alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-exclamation"></span> 
              <span data-notify="title" class="text-warning">Peringatan!</span> 
              <span data-notify="message">Anda harus login terlebih dahulu.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
    
    elseif ($_GET['pesan'] == 3) {
      
      echo '<div class="alert alert-notify alert-success alert-dismissible fade show" role="alert">
              <span data-notify="icon" class="fas fa-check"></span> 
              <span data-notify="title" class="text-success">Sukses!</span> 
              <span data-notify="message">Anda telah berhasil logout.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>';
    }
  }
  ?>

  <div class="wrapper wrapper-login">
    <div class="container container-login animated fadeIn">
      
      <div class="text-center mb-4"><img src="assets/img/logoapk.png" alt="Logo" width="95px"></div>
      
      <h3 class="text-center">Aplikasi <br>Warehouse Management System</h3>
      
      <form action="proses_login.php" method="post" class="needs-validation" novalidate>
        <div class="form-group form-floating-label">
          <div class="user-icon"><i class="fas fas fa-user"></i></div>
          <input type="text" id="username" name="username" class="form-control input-border-bottom" autocomplete="off" required>
          <label for="username" class="placeholder">Username</label>
          <div class="invalid-feedback">Username tidak boleh kosong.</div>
        </div>

        <div class="form-group form-floating-label">
          <div class="user-icon"><i class="fas fa-lock"></i></div>
          <div class="show-password"><i class="flaticon-interface"></i></div>
          <input type="password" id="password" name="password" class="form-control input-border-bottom" autocomplete="off" required>
          <label for="password" class="placeholder">Password</label>
          <div class="invalid-feedback">Password tidak boleh kosong.</div>
        </div>

        <div class="form-action mt-2">
          
          <input type="submit" name="login" value="LOGIN" class="btn btn-primary btn-rounded btn-login btn-block">
        </div>

        
        <div class="login-footer mt-4">
          <span class="msg">&copy; Dewi Sri Ana Riska</span>
         </div>
      </form>
    </div>
  </div>

  
  <script src="assets/js/core/jquery.3.2.1.min.js"></script>
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>

  
  <script src="assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>

  
  <script src="assets/js/ready.js"></script>

  
  <script src="assets/js/form-validation.js"></script>
</body>

</html>