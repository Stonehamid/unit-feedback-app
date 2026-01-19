<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unit Feedback - Login</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <div class="position-relative overflow-hidden text-bg-light min-vh-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-md-8 col-lg-6 col-xxl-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <a href="/" class="text-nowrap logo-img text-center d-block py-3 w-100">
                                    <img src="../assets/images/logos/logo.svg" alt="">
                                </a>
                                <p class="text-center">Unit Feedback System</p>
                                
                                <form id="loginForm" method="POST" action="{{ route('login') }}">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                                        <span class="text-danger" id="emailError"></span>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <span class="text-danger" id="passwordError"></span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input primary" type="checkbox" name="remember" id="remember">
                                            <label class="form-check-label text-dark" for="remember">Remember me</label>
                                        </div>
                                        <a class="text-primary fw-bold" href="">Forgot Password?</a>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4">Sign In</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Logging in...';
        });
    </script>
</body>
</html>