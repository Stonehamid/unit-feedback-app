<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Panel</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <style>
    /* CSS OVERRIDE UNTUK ATASI KOSONG SETELAH HAPUS TOPSTRIP */
    .page-wrapper {
      padding-top: 0 !important;
    }
    
    .left-sidebar {
      margin-top: 0 !important;
      padding-top: 0 !important;
      top: 0 !important;
    }
    
    .body-wrapper {
      margin-top: 0 !important;
      padding-top: 0 !important;
    }
    
    .body-wrapper-inner {
      padding-top: 0 !important;
    }
    
    /* Atur ulang tinggi sidebar */
    .left-sidebar {
      height: 100vh !important;
    }
    
    /* Atur ulang konten utama */
    .container-fluid {
      padding-top: 20px !important;
    }
  </style>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" 
       data-layout="vertical" 
       data-navbarbg="skin6" 
       data-sidebartype="full"
       data-sidebar-position="fixed" 
       data-header-position="fixed">

    <!-- SIDEBAR SEBELUM MAIN CONTENT -->
    @include('layouts.sidebar')
    
    <!-- Main wrapper -->
    <div class="body-wrapper">
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- Header Start -->
          <header class="app-header">
            @include('layouts.navbar')
          </header>
          <!-- Header End -->

          @yield('content')

          <!-- Footer -->
          <div class="py-6 px-6 text-center">
            <p class="mb-0 fs-4">
              Design and Developed by 
              <a href="https://www.wrappixel.com/" target="_blank" class="pe-1 text-primary text-decoration-underline">
                wrappixel.com
              </a> 
              Distributed by 
              <a href="https://themewagon.com/">ThemeWagon</a>
            </p>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>