# E-resep Demo

Instruksi local.

-   PHP 8.2+
-   Composer
-   MySQL/MariaDB

## 1) Siapkan Database

Buat database bernama **`deltasurya`**.

```sql
CREATE DATABASE deltasurya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 2) Salin & Atur Environment

Buat file `.env` dari contoh .env.example, lalu sesuaikan koneksi database.

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=deltasurya
DB_USERNAME=<user_db>
DB_PASSWORD=<password_db>
```

**Catatan:** Gunakan `env.example` sebagai acuan konfigurasi yang diperlukan.

## 3) Install Dependensi

```bash
composer install
```

## 4) Generate App Key

```bash
php artisan key:generate
```

## 5) Migrasi & Seed Data

```bash
php artisan migrate --seed
```

## 6) (Opsional) Buat Storage Symlink

```bash
php artisan storage:link
```

## 7) Jalankan Aplikasi

```bash
php artisan serve
```

Aplikasi biasanya dapat diakses di `http://127.0.0.1:8000`.

## Akun Demo

Gunakan kredensial berikut untuk masuk dan mencoba fitur:

**Dokter:**
Email: dokter@example.com
Password: test@1234

**Apoteker:**
Email: apoteker@example.com
Password: test@1234

Di dalam dashboard masing-masing role sudah tersedia instruksi singkat untuk pengujian alur aplikasi.
