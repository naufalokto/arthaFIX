<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            padding: 36px 32px 28px 32px;
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(59,130,246,0.10);
            width: 100%;
            max-width: 420px;
        }
        .login-container h2 {
            text-align: center;
            color: #2563eb;
            margin-bottom: 24px;
            font-weight: 800;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #dbeafe;
            border-radius: 6px;
            font-size: 1rem;
            background: #f1f5f9;
            margin-bottom: 4px;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 100%);
        }
        #error-message {
            text-align: center;
            margin-bottom: 12px;
            font-size: 1rem;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="/" style="display:inline-block;margin-bottom:18px;color:#2563eb;text-decoration:none;font-weight:600;font-size:1rem;">&larr; Kembali</a>
        <h2>Login</h2>
        <div id="error-message" style="display: none;"></div>
        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary" id="loginButton">Login</button>
        </form>
    </div>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const email = $('#email').val();
            const password = $('#password').val();
            
            // Disable button and show loading state
            const loginButton = $('#loginButton');
            loginButton.prop('disabled', true);
            loginButton.text('Loading...');
            
            // Hide any previous error message
            $('#error-message').hide();

            $.ajax({
                url: '/login',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    email: email,
                    password: password
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        // Redirect ke halaman sesuai role
                        window.location.href = response.redirect;
                    } else {
                        $('#error-message').text(response.message).show();
                        loginButton.prop('disabled', false);
                        loginButton.text('Login');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'An error occurred during login';
                    $('#error-message').text(message).show();
                    loginButton.prop('disabled', false);
                    loginButton.text('Login');
                }
            });
        });
    </script>
</body>
</html>