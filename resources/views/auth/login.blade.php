<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HSN Service Perfection</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        :root {
            --green-dark: #172D27;
            --green-main: #28483A;
            --gold: #D6A84B;
            --background: #F4F5F1;
        }

        body {
            min-height: 100vh;
            background: var(--background);
            font-family: Arial, sans-serif;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-card {
            width: 100%;
            max-width: 950px;
            min-height: 560px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(23, 45, 39, 0.10);
        }

        .login-brand {
            background: var(--green-dark);
            color: white;
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-logo {
            width: 76px;
            height: 76px;
            border-radius: 18px;
            background: var(--gold);
            color: var(--green-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 27px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 28px;
        }

        .brand-title {
            font-size: 30px;
            font-weight: 700;
            line-height: 1.3;
        }

        .brand-description {
            color: #D0D9D3;
            line-height: 1.8;
            margin-top: 16px;
        }

        .brand-footer {
            color: #AEBDB4;
            font-size: 12px;
        }

        .login-form-area {
            padding: 65px 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-heading {
            color: var(--green-dark);
            font-size: 27px;
            font-weight: 700;
        }

        .login-subtitle {
            color: #78817B;
            font-size: 14px;
            margin-top: 8px;
            margin-bottom: 35px;
        }

        .form-label {
            color: #34443B;
            font-size: 13px;
            font-weight: 600;
        }

        .form-control {
            padding: 12px 14px;
            border: 1px solid #DCE2DD;
            border-radius: 9px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: var(--green-main);
            box-shadow: 0 0 0 3px rgba(40, 72, 58, 0.10);
        }

        .btn-login {
            background: var(--green-dark);
            color: white;
            border: none;
            padding: 13px;
            border-radius: 9px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-login:hover {
            background: var(--green-main);
            color: white;
        }

        .login-note {
            color: #8A938D;
            font-size: 12px;
            text-align: center;
            margin-top: 28px;
        }

        @media (max-width: 768px) {
            .login-card {
                grid-template-columns: 1fr;
                max-width: 450px;
            }

            .login-brand {
                padding: 30px;
            }

            .brand-footer {
                margin-top: 30px;
            }

            .login-form-area {
                padding: 35px 30px;
            }
        }
    </style>
</head>

<body>

<div class="login-wrapper">
    <div class="login-card">

        <!-- Bagian Identitas Toko -->
        <div class="login-brand">
            <div>
                <div class="brand-logo">HSN</div>

                <div class="brand-title">
                    HSN Service<br>
                    Perfection
                </div>

                <p class="brand-description">
                    Sistem informasi untuk membantu pengelolaan
                    persediaan barang, sparepart, penjualan,
                    servis, dan hadiah secara terintegrasi.
                </p>
            </div>

            <div class="brand-footer">
                © {{ date('Y') }} HSN Service Perfection Palembang
            </div>
        </div>

        <!-- Bagian Form Login -->
        <div class="login-form-area">

            <h1 class="login-heading">Selamat Datang</h1>

            <p class="login-subtitle">
                Silakan masuk menggunakan akun Anda.
            </p>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.process') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        placeholder="Masukkan email Anda"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Masukkan password Anda"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="form-check mb-4">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="remember"
                        value="1"
                        id="remember"
                    >

                    <label class="form-check-label small" for="remember">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn btn-login w-100">
                    Masuk ke Sistem
                </button>
            </form>

            <p class="login-note">
                Halaman ini hanya dapat diakses oleh pengguna
                yang memiliki akun terdaftar.
            </p>

        </div>
    </div>
</div>

</body>
</html>
