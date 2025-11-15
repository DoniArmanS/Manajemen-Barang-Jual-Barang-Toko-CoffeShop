<h1 align="center">☕ Online CoffeShop Management System</h1>
<h3 align="center">Kelompok 2 — Sistem Manajemen Barang & Penjualan CoffeShop</h3>

<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/build-passing-brightgreen" alt="Build Status"></a>
  <a href="#"><img src="https://img.shields.io/badge/Laravel-10.x-red" alt="Laravel Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/license-MIT-blue" alt="License"></a>
  <a href="#"><img src="https://img.shields.io/badge/status-active-success" alt="Status"></a>
</p>

---

## 📋 Deskripsi Proyek

**Online CoffeShop Management System** adalah aplikasi berbasis web yang dikembangkan oleh **Kelompok 2** untuk membantu pengelolaan toko *CoffeShop* secara digital.  
Sistem ini memudahkan pemilik dan kasir untuk mengelola stok barang, melakukan transaksi penjualan, serta melihat ringkasan laporan.

### 🎯 Tujuan Utama
- ✅ Mengurangi kesalahan pencatatan manual  
- ⚡ Mempercepat proses transaksi di kasir  
- 📊 Menyediakan laporan stok & penjualan yang rapi  
- 💻 Mendukung digitalisasi toko kecil–menengah  

---

## 🧰 Teknologi yang Digunakan

| Komponen | Teknologi |
|---------|-----------|
| 🧩 **Framework** | Laravel 10 (PHP 8.x) |
| 💾 **Database** | MySQL / MariaDB |
| 🎨 **Frontend** | Blade Template, HTML, CSS, JavaScript |
| 📦 **Package Manager** | Composer |
| 🔧 **Version Control** | Git & GitHub |
| 🖥️ **Server** | PHP built-in server (`php artisan serve`) |

---

## 📦 Fitur Utama

- 🔐 **Login / Logout** (Admin & Cashier)
- 👥 **Manajemen User & Role** (admin, cashier)
- 📦 **Manajemen Inventory** (CRUD barang + stok)
- 🛒 **Halaman Kasir** (penjualan, keranjang, nota)
- 📊 **Dashboard** (ringkasan keuangan & stok)
- 🧾 **Riwayat Aktivitas / Transaksi**
---

## 🚀 Instalasi

Semua perintah di bawah dijalankan di **terminal** pada folder project.

1.  **Clone Repository**
    ```bash
    git clone [https://github.com/username/online-coffeeshop.git](https://github.com/username/online-coffeeshop.git)
    cd online-coffeeshop
    ```
    > **Catatan:** Ganti `username/online-coffeeshop` dengan URL repository kamu sendiri.

2.  **Install Dependencies**
    Pastikan kamu sudah menginstal Composer.
    ```bash
    composer install
    ```

3.  **Setup Environment**
    Salin file `.env.example` menjadi `.env` dan buat *app key*.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Konfigurasi Database**
    Buka file `.env` dan atur koneksi database kamu. Pastikan kamu sudah membuat database baru di MySQL/MariaDB.

    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nama_database_kamu
    DB_USERNAME=root
    DB_PASSWORD=password_kamu
    ```

5.  **Jalankan Migrasi & Seeding**
    Perintah ini akan membuat semua tabel database dan mengisi data *default* (termasuk akun admin).
    ```bash
    php artisan migrate --seed
    ```

---

## 🏃 Menjalankan Aplikasi

1.  Jalankan server pengembangan Laravel:
    ```bash
    php artisan serve
    ```
2.  Buka aplikasi di browser:
    <http://127.0.0.1:8000>

---

## 🔑 Akun Default

Setelah menjalankan `php artisan migrate --seed`, akun berikut akan tersedia:

| Role | Email | Password |
| :--- | :--- | :--- |
| Admin | `super@coffeeshop.local` | `secret123` |

* Login sebagai **Admin** menggunakan akun di atas.
* Dari halaman Admin, kamu bisa menambahkan user dengan role **cashier**.
* User dengan role **cashier** hanya akan melihat menu **Kasir** dan diarahkan langsung ke halaman kasir sesuai *middleware* role.

---

## ⚙️ Perintah Penting Saat Pengembangan

Terkadang kamu perlu membersihkan *cache* atau me-reset database.

* **Bersihkan cache** (jika ada error aneh)
    ```bash
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    ```

* **Reset database dari awal** (hapus semua data!)
    ```bash
    php artisan migrate:fresh --seed
    ```

---

## 📜 License


---

<p align="center">Made with ❤️ by Kelompok 2</p>