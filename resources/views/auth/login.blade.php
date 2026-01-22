<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UISOCIAL - Login</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png">
    <link rel="stylesheet" href="../assets/css/styles.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Custom CSS for UISOCIAL Design */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }

        .uisocial-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .uisocial-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        .uisocial-header {
            padding: 40px 40px 20px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .uisocial-logo {
            font-size: 28px;
            font-weight: 800;
            color: #3b82f6;
            letter-spacing: -0.5px;
            margin-bottom: 10px;
        }

        .uisocial-tagline {
            font-size: 12px;
            color: #64748b;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 30px;
        }

        .uisocial-welcome {
            margin-bottom: 30px;
        }

        .uisocial-welcome h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 5px 0;
        }

        .uisocial-welcome p {
            color: #64748b;
            margin: 0;
            font-size: 14px;
        }

        .uisocial-form {
            padding: 0 40px 40px;
        }

        .form-group-uisocial {
            margin-bottom: 20px;
        }

        .form-group-uisocial label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #475569;
            font-size: 14px;
        }

        .form-control-uisocial {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .form-control-uisocial:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control-uisocial::placeholder {
            color: #94a3b8;
        }

        .forgot-password-uisocial {
            display: block;
            text-align: right;
            margin-top: 8px;
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-password-uisocial:hover {
            text-decoration: underline;
        }

        .btn-uisocial-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin: 10px 0 20px;
        }

        .btn-uisocial-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-uisocial-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .divider-uisocial {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #64748b;
            font-size: 14px;
        }

        .divider-uisocial::before,
        .divider-uisocial::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider-uisocial span {
            padding: 0 15px;
        }

        .btn-uisocial-google {
            width: 100%;
            padding: 14px;
            background: white;
            color: #475569;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-uisocial-google:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .google-icon {
            width: 18px;
            height: 18px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="%234285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="%2334A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="%23FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="%23EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>') no-repeat center;
            background-size: contain;
        }

        .signup-link-uisocial {
            text-align: center;
            margin-top: 25px;
            color: #64748b;
            font-size: 15px;
        }

        .signup-link-uisocial a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link-uisocial a:hover {
            text-decoration: underline;
        }

        .copyright-uisocial {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            color: #94a3b8;
            font-size: 13px;
        }

        .copyright-uisocial strong {
            color: #475569;
            font-weight: 600;
        }

        /* Message styles */
        .alert-uisocial {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-info {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .spinner-border {
            vertical-align: -0.125em;
            border: 0.15em solid currentColor;
            border-right-color: transparent;
        }

        @media (max-width: 480px) {
            .uisocial-container {
                padding: 10px;
                background: white;
            }
            
            .uisocial-card {
                box-shadow: none;
                border-radius: 0;
            }
            
            .uisocial-header,
            .uisocial-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="uisocial-container">
        <div class="uisocial-card">
            <div class="uisocial-header">
                <div class="uisocial-logo">UISOCIAL</div>
                <div class="uisocial-tagline">SEEN</div>
                <div class="uisocial-welcome">
                    <h2>Hi Designer</h2>
                    <p>Welcome to UISOCIAL</p>
                </div>
            </div>

            <div class="uisocial-form">
                <div id="messageContainer" class="alert-uisocial" style="display: none;">
                    <span id="messageText"></span>
                </div>

                <form id="loginForm">
                    @csrf
                    <div class="form-group-uisocial">
                        <label for="email">Email</label>
                        <input type="email" class="form-control-uisocial" id="email" name="email" required
                            autofocus placeholder="Enter your email">
                        <div class="invalid-feedback" id="emailError" style="color: #ef4444; font-size: 13px; margin-top: 5px;"></div>
                    </div>

                    <div class="form-group-uisocial">
                        <label for="password">Password</label>
                        <input type="password" class="form-control-uisocial" id="password" name="password"
                            required placeholder="Enter your password">
                        <div class="invalid-feedback" id="passwordError" style="color: #ef4444; font-size: 13px; margin-top: 5px;"></div>
                        <a href="#" class="forgot-password-uisocial" id="forgotPassword">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-uisocial-primary" id="submitBtn">
                        <span id="btnText">Login</span>
                        <span id="btnSpinner" style="display: none;">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            Logging in...
                        </span>
                    </button>

                    <div class="divider-uisocial">
                        <span>or</span>
                    </div>

                    <button type="button" class="btn-uisocial-google">
                        <span class="google-icon"></span>
                        Login with Google
                    </button>

                    <div class="signup-link-uisocial">
                        Don't have an account? <a href="/signup">Sign up</a>
                    </div>
                </form>

                <div class="copyright-uisocial">
                    <strong>Andrew.ui</strong><br>
                    UI & Illustration
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>

    <script>
        $(document).ready(function () {
            const savedEmail = localStorage.getItem('saved_email');
            if (savedEmail) {
                $('#email').val(savedEmail);
            }

            $('#loginForm').on('submit', function (e) {
                e.preventDefault();
                resetFormErrors();
                hideMessage();

                const email = $('#email').val().trim();
                const password = $('#password').val();

                if (!email || !password) {
                    showMessage('Please fill in all fields', 'danger');
                    return;
                }

                setButtonLoading(true);

                // Simulate login process (replace with actual AJAX call)
                setTimeout(() => {
                    if (email === 'demo@uisocial.com' && password === 'demo123') {
                        handleLoginSuccess({
                            token: 'sample_token',
                            user: { name: 'Designer', email: email }
                        });
                    } else {
                        showMessage('Invalid email or password', 'danger');
                        setButtonLoading(false);
                    }
                }, 1500);
            });

            $('#forgotPassword').click(function (e) {
                e.preventDefault();
                showMessage('Password reset link has been sent to your email.', 'info');
            });

            $('.btn-uisocial-google').click(function () {
                showMessage('Google login is not implemented in this demo.', 'info');
            });

            function handleLoginSuccess(data) {
                localStorage.setItem('auth_token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));

                showMessage('Login successful! Redirecting...', 'success');

                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 1000);
            }

            function setButtonLoading(loading) {
                const btn = $('#submitBtn');
                const btnText = $('#btnText');
                const btnSpinner = $('#btnSpinner');

                if (loading) {
                    btn.prop('disabled', true);
                    btnText.hide();
                    btnSpinner.show();
                } else {
                    btn.prop('disabled', false);
                    btnText.show();
                    btnSpinner.hide();
                }
            }

            function showMessage(message, type) {
                const container = $('#messageContainer');
                const text = $('#messageText');

                container.removeClass('alert-success alert-danger alert-info');
                container.addClass('alert-' + type);
                container.css('display', 'block');

                text.text(message);

                if (type === 'success') {
                    setTimeout(hideMessage, 3000);
                }
            }

            function hideMessage() {
                $('#messageContainer').hide();
            }

            function resetFormErrors() {
                $('#email, #password').removeClass('is-invalid');
                $('#emailError, #passwordError').text('');
            }
        });
    </script>
</body>
</html>