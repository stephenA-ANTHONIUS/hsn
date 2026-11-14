@extends('layouts.app')

@section('title', 'Profil Akun')
@section('page-heading', 'Profil Akun')

@php
    $isKepalaToko = $user->role === 'kepala_toko';

    $roleLabel = $isKepalaToko
        ? 'Kepala Toko'
        : 'Admin';

    $dashboardRoute = $isKepalaToko
        ? 'kepala-toko.dashboard'
        : 'admin.dashboard';

    $tanggalDibuat = $user->created_at
        ? $user->created_at->format('d/m/Y')
        : '-';
@endphp

@push('styles')
<style>
    .profile-page {
        max-width: 1050px;
        margin: 0 auto;
    }

    .profile-heading {
        margin-bottom: 25px;
    }

    .profile-heading h2 {
        color: #172D27;
        font-size: 23px;
        font-weight: 800;
        margin-bottom: 7px;
    }

    .profile-heading p {
        color: #78817B;
        font-size: 13px;
        margin: 0;
    }

    .profile-main-card {
        overflow: hidden;
        background: white;
        border: 1px solid #DCE2DD;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(23,45,39,.04);
    }

    .profile-banner {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 27px 30px;
        color: white;
        background: linear-gradient(120deg, #172D27, #28483A);
    }

    .profile-avatar {
        width: 76px;
        height: 76px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 3px solid rgba(255,255,255,.25);
        border-radius: 50%;
        background: #D6A84B;
        color: #172D27;
        font-size: 30px;
        font-weight: 800;
    }

    .profile-banner h3 {
        color: white;
        font-size: 21px;
        font-weight: 800;
        margin: 0 0 7px;
    }

    .profile-banner p {
        color: #D0DCD3;
        font-size: 12px;
        margin: 0 0 10px;
    }

    .profile-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 6px;
        background: rgba(214,168,75,.18);
        color: #F4DDA4;
        font-size: 10px;
        font-weight: 750;
    }

    .profile-content {
        padding: 27px 30px;
    }

    .profile-section {
        margin-bottom: 27px;
    }

    .profile-section:last-child {
        margin-bottom: 0;
    }

    .profile-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #172D27;
        font-size: 14px;
        font-weight: 800;
        margin-bottom: 19px;
    }

    .profile-section-title i {
        color: #28483A;
        font-size: 17px;
    }

    .profile-data-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
    }

    .profile-data-item {
        padding: 16px 17px;
        border: 1px solid #E4E9E5;
        border-radius: 9px;
        background: #FAFBF9;
        min-width: 0;
    }

    .profile-data-label {
        color: #78817B;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .5px;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .profile-data-value {
        color: #26332D;
        font-size: 14px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    /* SECURITY NOTICE */

    .profile-security {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        padding: 17px 19px;
        margin-top: 23px;
        border: 1px solid #E9DFC8;
        border-left: 4px solid #D6A84B;
        border-radius: 9px;
        background: #FCFAF5;
    }

    .profile-security > i {
        color: #99752E;
        font-size: 20px;
        flex-shrink: 0;
    }

    .profile-security h4 {
        color: #172D27;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .profile-security p {
        color: #69756D;
        font-size: 11px;
        line-height: 1.8;
        margin: 0;
    }

    /* FORM */

    .profile-password-card {
        padding: 23px;
        border: 1px solid #DCE2DD;
        border-radius: 11px;
        background: #fff;
    }

    .profile-password-description {
        color: #78817B;
        font-size: 11px;
        line-height: 1.75;
        margin: -10px 0 20px;
    }

    .profile-form-group {
        margin-bottom: 17px;
    }

    .profile-form-label {
        display: block;
        color: #344239;
        font-size: 12px;
        font-weight: 750;
        margin-bottom: 8px;
    }

    .profile-form-control {
        display: block;
        width: 100%;
        min-height: 43px;
        padding: 10px 13px;
        border: 1px solid #DCE2DD;
        border-radius: 8px;
        background: #FAFBF9;
        color: #26332D;
        font-size: 12px;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .profile-form-control:focus {
        border-color: #28483A;
        box-shadow: 0 0 0 3px rgba(40,72,58,.1);
        background: #fff;
    }

    .profile-form-control.is-invalid {
        border-color: #DC3545;
    }

    .profile-form-error {
        color: #DC3545;
        font-size: 11px;
        margin-top: 6px;
    }

    .profile-password-hint {
        color: #78817B;
        font-size: 10px;
        margin-top: 7px;
    }

    .btn-profile-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 17px;
        border: 0;
        border-radius: 8px;
        background: #28483A;
        color: white;
        font-size: 12px;
        font-weight: 750;
        cursor: pointer;
        transition: background .2s;
    }

    .btn-profile-save:hover {
        background: #172D27;
        color: white;
    }

    .btn-profile-save:focus-visible {
        outline: 3px solid #D6A84B;
        outline-offset: 3px;
    }

    /* NOTIFICATION */

    .profile-alert {
        padding: 13px 16px;
        border-radius: 8px;
        font-size: 12px;
        margin-bottom: 20px;
    }

    .profile-alert-success {
        background: #EAF5ED;
        color: #245B34;
        border: 1px solid #CBE5D1;
    }

    .profile-alert-danger {
        background: #FCEBEC;
        color: #A52A34;
        border: 1px solid #F3C8CC;
    }

    /* FOOTER ACTION */

    .profile-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 24px;
    }

    .btn-profile-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 1px solid #28483A;
        border-radius: 8px;
        background: #28483A;
        color: white;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: background .2s;
    }

    .btn-profile-back:hover {
        background: #172D27;
        border-color: #172D27;
        color: white;
    }

    .btn-profile-back:focus-visible {
        outline: 3px solid #D6A84B;
        outline-offset: 3px;
    }

    @media (max-width: 600px) {
        .profile-banner {
            padding: 22px 18px;
            gap: 14px;
        }

        .profile-avatar {
            width: 60px;
            height: 60px;
            font-size: 24px;
        }

        .profile-banner h3 {
            font-size: 17px;
        }

        .profile-content {
            padding: 21px 17px;
        }

        .profile-data-grid {
            grid-template-columns: 1fr;
        }

        .profile-password-card {
            padding: 18px;
        }

        .profile-actions {
            justify-content: stretch;
        }

        .btn-profile-back {
            justify-content: center;
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="profile-page">

    {{-- PAGE HEADING --}}
    <div class="profile-heading">
        <h2>Informasi Akun Saya</h2>

        <p>
            Informasi identitas dan pengaturan keamanan akun.
        </p>
    </div>

    <div class="profile-main-card">

        {{-- PROFILE HEADER --}}
        <div class="profile-banner">

            <div class="profile-avatar">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>

            <div>
                <h3>{{ $user->name }}</h3>

                <p>HSN Service Perfection Palembang</p>

                <span class="profile-role-badge">
                    <i class="bi bi-person-badge"></i>
                    {{ $roleLabel }}
                </span>
            </div>

        </div>

        <div class="profile-content">

            {{-- SUCCESS MESSAGE --}}
            @if (session('success'))
                <div class="profile-alert profile-alert-success" role="status">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            {{-- VALIDATION SUMMARY --}}
            @if ($errors->any())
                <div class="profile-alert profile-alert-danger" role="alert">
                    <strong>
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Perubahan akun belum berhasil. Periksa kembali data yang Anda masukkan.
                    </strong>

                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- PERSONAL INFORMATION --}}
            <section class="profile-section">

                <h4 class="profile-section-title">
                    <i class="bi bi-person-lines-fill"></i>
                    Informasi Pribadi
                </h4>

                <div class="profile-data-grid">

                    <div class="profile-data-item">
                        <div class="profile-data-label">ID Akun</div>
                        <div class="profile-data-value">#{{ $user->id }}</div>
                    </div>

                    <div class="profile-data-item">
                        <div class="profile-data-label">Nama Lengkap</div>
                        <div class="profile-data-value">{{ $user->name ?? '-' }}</div>
                    </div>

                    <div class="profile-data-item">
                        <div class="profile-data-label">Email</div>
                        <div class="profile-data-value">{{ $user->email ?: '-' }}</div>
                    </div>

                    <div class="profile-data-item">
                        <div class="profile-data-label">Peran Pengguna</div>
                        <div class="profile-data-value">{{ $roleLabel }}</div>
                    </div>

                    <div class="profile-data-item">
                        <div class="profile-data-label">Tanggal Pembuatan Akun</div>
                        <div class="profile-data-value">{{ $tanggalDibuat }}</div>
                    </div>

                    <div class="profile-data-item">
                        <div class="profile-data-label">Kata Sandi</div>
                        <div class="profile-data-value">Tidak ditampilkan demi keamanan</div>
                    </div>

                </div>

            </section>

            {{-- EDIT PERSONAL INFORMATION: KEPALA TOKO ONLY --}}
            @if ($isKepalaToko)

                <section class="profile-section">

                    <h4 class="profile-section-title">
                        <i class="bi bi-pencil-square"></i>
                        Ubah Nama dan Email
                    </h4>

                    <div class="profile-password-card">

                        <p class="profile-password-description">
                            Perbarui nama dan email yang digunakan pada akun Kepala Toko.
                            Email harus berbeda dari email akun pengguna lainnya.
                        </p>

                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="profile-form-group">
                                <label for="name" class="profile-form-label">
                                    Nama Lengkap
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       id="name"
                                       name="name"
                                       class="profile-form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                       value="{{ old('name', $user->name) }}"
                                       maxlength="255"
                                       autocomplete="name"
                                       required>

                                @error('name')
                                    <div class="profile-form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="profile-form-group">
                                <label for="email" class="profile-form-label">
                                    Email
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="email"
                                       id="email"
                                       name="email"
                                       class="profile-form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                       value="{{ old('email', $user->email) }}"
                                       maxlength="255"
                                       autocomplete="email"
                                       required>

                                @error('email')
                                    <div class="profile-form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn-profile-save">
                                <i class="bi bi-save"></i>
                                Simpan Perubahan Profil
                            </button>

                        </form>

                    </div>

                </section>

            @endif

            {{-- ACCOUNT SECURITY --}}
            <section class="profile-section">

                <h4 class="profile-section-title">
                    <i class="bi bi-shield-lock"></i>
                    Keamanan Akun
                </h4>

                <div class="profile-security">

                    <i class="bi bi-shield-check"></i>

                    <div>
                        <h4>Perlindungan Akun</h4>

                        @if ($isKepalaToko)
                            <p>
                                Anda dapat mengganti password akun sendiri melalui
                                formulir di bawah. Masukkan password lama untuk
                                memverifikasi identitas sebelum menyimpan password baru.
                            </p>
                        @else
                            <p>
                                Akun Admin tidak memiliki akses untuk mengganti
                                informasi profil maupun password sendiri. Apabila
                                informasi akun atau password perlu diubah, silakan
                                hubungi Kepala Toko melalui menu Kelola Pengguna.
                            </p>
                        @endif
                    </div>

                </div>

            </section>

            {{-- PASSWORD FORM: KEPALA TOKO ONLY --}}
            @if ($isKepalaToko)

                <section class="profile-section">

                    <h4 class="profile-section-title">
                        <i class="bi bi-key"></i>
                        Ganti Password Saya
                    </h4>

                    <div class="profile-password-card">

                        <p class="profile-password-description">
                            Gunakan password lama untuk melakukan verifikasi.
                            Password baru harus memiliki minimal 8 karakter.
                        </p>

                        <form action="{{ route('profile.password.update') }}"
                              method="POST">

                            @csrf
                            @method('PUT')

                            <div class="profile-form-group">
                                <label for="current_password" class="profile-form-label">
                                    Password Lama
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="password"
                                       id="current_password"
                                       name="current_password"
                                       class="profile-form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                                       autocomplete="current-password"
                                       required
                                       placeholder="Masukkan password lama">

                                @error('current_password')
                                    <div class="profile-form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="profile-form-group">
                                <label for="password" class="profile-form-label">
                                    Password Baru
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="password"
                                       id="password"
                                       name="password"
                                       class="profile-form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                       autocomplete="new-password"
                                       minlength="8"
                                       required
                                       placeholder="Masukkan password baru">

                                @error('password')
                                    <div class="profile-form-error">{{ $message }}</div>
                                @enderror

                                <div class="profile-password-hint">
                                    Minimal 8 karakter dan harus berbeda dari password lama.
                                </div>
                            </div>

                            <div class="profile-form-group">
                                <label for="password_confirmation" class="profile-form-label">
                                    Konfirmasi Password Baru
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="password"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       class="profile-form-control"
                                       autocomplete="new-password"
                                       minlength="8"
                                       required
                                       placeholder="Ulangi password baru">
                            </div>

                            <button type="submit" class="btn-profile-save">
                                <i class="bi bi-shield-check"></i>
                                Simpan Password Baru
                            </button>

                        </form>

                    </div>

                </section>

            @endif

            {{-- BACK TO DASHBOARD --}}
            <div class="profile-actions">
                <a href="{{ route($dashboardRoute) }}" class="btn-profile-back">
                    <i class="bi bi-arrow-left"></i>
                    Kembali ke Dashboard
                </a>
            </div>

        </div>

    </div>

</div>

@endsection
