# Deploy Ginela POS ke Hostinger (Git + SSH)

Aplikasi Laravel + Livewire. Aset front-end **sudah dikompilasi** dan ikut di repo (`public/build`),
jadi **tidak perlu Node.js** di server. Composer dijalankan di server untuk mengunduh `vendor/`.

Database MySQL Hostinger **sudah dimigrasi & di-seed** (tidak perlu `migrate` lagi saat pertama deploy).

## Ringkasan
- Dokumen root domain **harus** mengarah ke folder `public/`.
- Buat file `.env` di server (isi diberikan terpisah — berisi kata sandi DB, jangan commit).

## Langkah di server (SSH)

```bash
# 1. Masuk ke folder home / domain, lalu clone repo
cd ~
git clone https://github.com/<USER>/ginela-laravel.git ginela
cd ginela

# 2. Install dependency PHP (produksi)
composer install --no-dev --optimize-autoloader

# 3. Buat file .env (paste isi yang diberikan), lalu:
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

# (migrate TIDAK perlu — DB sudah disiapkan. Untuk update skema berikutnya:)
# php artisan migrate --force
```

## Mengarahkan domain ke folder public
Pilih salah satu sesuai panel Hostinger:

**A. Ubah Document Root (bila tersedia di hPanel)** → arahkan `ginela.web.id` ke `~/ginela/public`.

**B. Cara symlink (universal, via SSH):**
```bash
# ganti nama public_html lama, arahkan ke public aplikasi
mv ~/public_html ~/public_html_old
ln -s ~/ginela/public ~/public_html
```

## Update rutin (setiap ada perubahan)
```bash
cd ~/ginela && git pull && composer install --no-dev --optimize-autoloader \
  && php artisan migrate --force && php artisan optimize
```
