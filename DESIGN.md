
---
version: alpha
name: "HSN Service Perfection"
description: "Design system untuk aplikasi manajemen persediaan, penjualan, servis, dan hadiah HSN."
colors:
  primary: "#172D27"
  secondary: "#28483A"
  accent: "#D6A84B"
  background: "#F4F5F1"
  surface: "#FFFFFF"
  text: "#26332D"
  muted: "#78817B"
  border: "#DCE2DD"
  success: "#287A50"
  danger: "#B84040"
typography:
  sans:
    fontFamily: "Arial, Helvetica, sans-serif"
  mono:
    fontFamily: "Consolas, monospace"
rounded:
  DEFAULT: "0.5rem"
  sm: "0.375rem"
  md: "0.5rem"
  lg: "0.75rem"
spacing:
  section-gap: "2rem"
  page-padding: "1.5rem"
  sidebar-width: "16rem"
components:
  button: {}
  card: {}
  input: {}
  table: {}
---

# HSN Service Perfection Design System

## Overview

### Creative North Star
Antarmuka administrasi toko yang terinspirasi dari identitas HSN, dengan warna hijau hutan dan aksen emas. Tampilan mengutamakan kemudahan membaca data dan melakukan transaksi sehari-hari.

### Product Context

- **Pengguna:** Kepala Toko dan Admin.
- **Tujuan:** Mengelola barang, sparepart, barang masuk, penjualan, servis, hadiah, dan pendapatan.
- **Bahasa:** Bahasa Indonesia.
- **Platform:** Aplikasi web Laravel dengan Blade dan Bootstrap.
- **Register:** Aplikasi administrasi internal.
- **Memorable Signature:** Identitas HSN dengan kombinasi hijau tua dan emas.
- **Restraint:** Menghindari dekorasi berlebihan dan tampilan seperti situs promosi.

## Colors

Hijau tua digunakan sebagai warna utama navigasi dan tombol penting. Hijau kedua digunakan untuk variasi elemen antarmuka. Emas menjadi aksen identitas HSN. Latar belakang menggunakan warna netral agar tabel dan formulir mudah dibaca.

## Typography

Menggunakan Arial sebagai font utama karena mudah dibaca dan tersedia secara luas. Judul menggunakan ketebalan lebih tinggi daripada teks isi. Angka transaksi dan nominal uang harus mudah dibedakan.

## Layout

Dashboard menggunakan sidebar di sebelah kiri dan area konten utama di sebelah kanan. Sidebar memuat navigasi sesuai hak akses pengguna. Area konten menampilkan ringkasan, tabel, formulir, dan informasi operasional.

Tampilan harus responsif untuk desktop, tablet, dan perangkat seluler.

## Elevation & Depth

Gunakan pembatas tipis dan bayangan lembut pada kartu atau panel yang membutuhkan pemisahan visual. Hindari bayangan berlebihan.

## Shapes

Gunakan sudut membulat sedang pada kartu, tombol, dan input. Pertahankan bentuk yang konsisten di seluruh halaman.

## Components

### Buttons and Actions
Tombol utama menggunakan warna hijau tua. Tombol sekunder menggunakan warna netral. Aksi berbahaya menggunakan warna merah dan harus memiliki konfirmasi.

### Navigation and Data Display
Sidebar menggunakan warna hijau tua. Tabel menggunakan latar putih, garis pemisah halus, dan judul kolom yang jelas.

### Forms
Setiap input memiliki label yang jelas. Kesalahan validasi ditampilkan dekat dengan formulir terkait.

### Iconography
Gunakan ikon yang sederhana dan konsisten. Ikon navigasi tetap disertai teks agar mudah dipahami.

### Motion
Gunakan animasi ringan hanya untuk membantu menunjukkan perubahan keadaan. Hormati pengaturan reduced motion perangkat.

## Do's and Don'ts

- Gunakan warna hijau dan emas secara konsisten.
- Utamakan keterbacaan data transaksi.
- Gunakan istilah operasional yang mudah dipahami pengguna.
- Jangan memenuhi dashboard dengan dekorasi yang tidak diperlukan.
- Jangan menampilkan fitur yang tidak sesuai dengan hak akses pengguna.
