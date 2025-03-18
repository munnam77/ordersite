<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | 発注サイト</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Hiragino Kaku Gothic Pro', 'Meiryo', sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .login-title {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
            font-weight: 700;
        }
        .form-label {
            font-weight: 600;
        }
        .btn-primary {
            background-color: #4285F4;
            border-color: #4285F4;
            font-weight: 600;
            padding: 0.5rem 1rem;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #3367D6;
            border-color: #3367D6;
        }
        .alert {
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h1 class="login-title">発注サイト</h1>
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif
            
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="login_id" class="form-label">ログインID</label>
                    <input type="text" class="form-control" id="login_id" name="login_id" value="{{ old('login_id') }}" required autofocus>
                </div>
                
                <div class="mb-4">
                    <label for="login_password" class="form-label">パスワード</label>
                    <input type="password" class="form-control" id="login_password" name="login_password" required>
                </div>
                
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">ログイン</button>
                </div>
            </form>
            
            <div class="footer">
                <p>ログインに関するお問い合わせは管理者にご連絡ください。</p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 