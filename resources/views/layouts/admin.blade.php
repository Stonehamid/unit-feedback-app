<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Panel</title>
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css')}}" />
  <style>
    /* Custom Styles untuk Background Putih */
    .page-wrapper { 
      padding-top: 0 !important; 
      background-color: #fafafa !important; /* Ganti background utama */
    }
    
    .left-sidebar { 
      margin-top: 0 !important; 
      top: 0 !important; 
      height: 100vh !important; 
    }
    
    .body-wrapper { 
      margin-top: 0 !important; 
      background-color: #ffffff !important; /* Pastikan body-wrapper putih */
    }
    
    .body-wrapper-inner {
      background-color: #ffffff !important;
      min-height: 100vh;
    }
    
    .container-fluid {
      background-color: #ffffff !important;
      padding-top: 0;
    }
    
    /* Hilangkan background gradient yang mungkin ada */
    .body-wrapper, 
    .body-wrapper-inner,
    .container-fluid,
    #spa-content,
    .card {
      background-image: none !important;
    }
    
    /* Pastikan card dan content area juga putih */
    .card {
      background-color: #ffffff !important;
      border: 1px solid #e5e7eb; /* Border ringan untuk kontras */
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    
    /* Header styling */
    .app-header {
      background-color: #ffffff !important;
      border-bottom: 1px solid #e5e7eb;
      padding: 1rem 0;
      margin-bottom: 1.5rem;
    }
    
    /* Content area styling */
    #spa-content {
      background-color: #ffffff !important;
      padding: 0;
      min-height: calc(100vh - 120px);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .body-wrapper {
        margin-left: 0 !important;
      }
    }
  </style>
</head>

<body style="background-color: #ffffff !important;">
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed" style="background-color: #ffffff !important;">

    @include('layouts.sidebar')

    <div class="body-wrapper" style="background-color: #ffffff !important;">
      <div class="body-wrapper-inner" style="background-color: #ffffff !important;">
        <div class="container-fluid" style="background-color: #ffffff !important; padding: 20px;">

          <header class="app-header">
            @include('layouts.navbar')
          </header>

          <div id="spa-content" style="background-color: #ffffff !important;">
            @yield('content')
          </div>
          
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/sidebarmenu.js') }}"></script>
  <script src="{{ asset('assets/js/app.min.js') }}"></script>
  <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
  <script src="{{ asset('assets/js/dashboard.js') }}"></script>
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
        <div class="text-center py-5" style="background-color: #ffffff;">
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
          <div class="card" style="background-color: #ffffff;">
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
          <div class="alert alert-danger" style="background-color: #ffffff;">
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
      
      // Force white background
      document.body.style.backgroundColor = '#ffffff';
      document.querySelector('.page-wrapper').style.backgroundColor = '#ffffff';
      document.querySelector('.body-wrapper').style.backgroundColor = '#ffffff';
      document.querySelector('.body-wrapper-inner').style.backgroundColor = '#ffffff';
      document.querySelector('.container-fluid').style.backgroundColor = '#ffffff';
      document.getElementById('spa-content').style.backgroundColor = '#ffffff';
    });
  </script>
</body>
</html>