<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Panel</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <style>
    .page-wrapper { padding-top: 0 !important; }
    .left-sidebar { margin-top: 0 !important; top: 0 !important; height: 100vh !important; }
    .body-wrapper { margin-top: 0 !important; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    @include('layouts.sidebar')

    <div class="body-wrapper">
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <header class="app-header">
            @include('layouts.navbar')
          </header>

          <div id="spa-content">
            @yield('content')
          </div>

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

  <script>
    function getAuthToken() {
      const token = localStorage.getItem('token');
      if (!token) {
        console.warn('No token found in localStorage');
        window.location.href = '/login';
      }
      return token;
    }

    function loadPageContent(page) {
      const contentArea = document.getElementById('spa-content');
      const token = getAuthToken();

      contentArea.innerHTML = `
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading ${page}...</p>
        </div>
      `;

      fetch(`/api/admin/${page}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })
      .then(response => {
        if (response.status === 401) {
          localStorage.removeItem('token');
          localStorage.removeItem('user');
          throw new Error('Session expired. Please login again.');
        }
        if (!response.ok) {
          throw new Error(`Server error: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        contentArea.innerHTML = `
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">${page.charAt(0).toUpperCase() + page.slice(1)} Management</h4>
              <div class="mt-4">
                <pre class="bg-light p-3 rounded">${JSON.stringify(data, null, 2)}</pre>
              </div>
            </div>
          </div>
        `;
      })
      .catch(error => {
        contentArea.innerHTML = `
          <div class="alert alert-danger">
            <h5>Error Loading ${page}</h5>
            <p>${error.message}</p>
            ${error.message.includes('Session expired') ? 
              '<a href="/login" class="btn btn-primary mt-2">Login Again</a>' : 
              '<button onclick="loadPageContent(\'' + page + '\')" class="btn btn-primary mt-2">Retry</button>'}
          </div>
        `;
        console.error('Load error:', error);
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      const currentPage = '{{ $page ?? "dashboard" }}';
      
      if (currentPage !== 'dashboard' && !document.querySelector('#spa-content').innerHTML.trim()) {
        loadPageContent(currentPage);
      }
    });
  </script>
</body>
</html>