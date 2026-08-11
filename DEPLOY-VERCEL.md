# Persiapan Deploy Vercel

Folder ini adalah salinan publik yang terpisah dari versi lokal. Tidak ada `.env`, API key, password, database lokal, log, atau folder `vendor` yang disalin.

## 1. Siapkan database PostgreSQL

Gunakan PostgreSQL persisten dari penyedia pilihan Anda. Salin connection string yang diberikan ke variabel `DB_URL` di Vercel.

Migration otomatis dijalankan saat build melalui script Composer `vercel`. Migration tersebut membuat tabel data portal, pengguna/session, cache, dan queue bawaan Laravel.

## 2. Buat APP_KEY

Jalankan pada komputer lokal dari folder Laravel yang memiliki `vendor`:

```bat
php artisan key:generate --show
```

Salin hasil `base64:...` sebagai `APP_KEY` di Vercel.

## 3. Buat password admin yang aman

Jalankan:

```bat
php -r "echo password_hash('GANTI_DENGAN_PASSWORD_KUAT', PASSWORD_BCRYPT);"
```

Masukkan hasilnya sebagai `ADMIN_PASSWORD_HASH`. Jangan memasukkan password biasa. Atur juga `ADMIN_USERNAME`.

## 4. Variabel environment wajib

Tambahkan melalui Project Settings > Environment Variables di Vercel:

- `APP_NAME`
- `APP_ENV=production`
- `APP_KEY`
- `APP_DEBUG=false`
- `APP_URL` sesuai domain Vercel
- `DB_CONNECTION=pgsql`
- `DB_URL`
- `SESSION_DRIVER=database`
- `SESSION_ENCRYPT=true`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=sync`
- `ADMIN_USERNAME`
- `ADMIN_PASSWORD_HASH`
- `GOOGLE_DRIVE_FOLDER_ID`
- `GOOGLE_DRIVE_API_KEY`

Jangan commit `.env` atau menempelkan nilai rahasia ke `vercel.json`.

### Struktur album Google Drive

Buat subfolder di dalam folder Drive utama untuk setiap tanggal atau kegiatan,
misalnya `17 Agustus 2026` atau `Kerja Bakti Gang Melati`. Nama subfolder akan
menjadi judul album pada halaman Momen dan hanya foto di dalam subfolder itu
yang tampil di bawah judul tersebut. Foto yang diletakkan langsung pada folder
utama masuk ke album `Dokumentasi Umum`. Subfolder bertingkat juga didukung.

### Import sekaligus

Gunakan isi file `.env.vercel.example`, lalu ganti terlebih dahulu semua nilai
yang diawali `GANTI_DENGAN_`. Pada formulir pembuatan project Vercel, buka
**Environment Variables**, klik **Import .env**, lalu tempel seluruh isinya.

Integrasi Neon menambahkan `DATABASE_URL` dan `DATABASE_URL_UNPOOLED` secara
otomatis. Aplikasi memprioritaskan koneksi langsung `DATABASE_URL_UNPOOLED`
karena lebih aman untuk migration Laravel, lalu memakai `DATABASE_URL` sebagai
fallback. Connection string Neon tidak perlu disalin ke template. Pastikan kedua
variabel tersedia untuk Production dan Preview.

Jika resource pernah dibuat dengan **Custom Prefix** `STORAGE`, aplikasi juga
mendukung `STORAGE_URL` dan `STORAGE_URL_UNPOOLED`. Nama lama Vercel
`POSTGRES_URL` dan `POSTGRES_URL_NON_POOLING` juga didukung.

Untuk integrasi dengan **Custom Prefix** `DB`, aplikasi memakai
`DB_POSTGRES_URL_NON_POOLING` dan fallback `DB_DATABASE_URL` yang dibuat Neon.

## 5. Deploy

Jadikan folder `ready to publik` sebagai root repository Git tersendiri, lalu import repository tersebut ke Vercel. Vercel akan membaca `vercel.json`, memasang dependency Composer, menjalankan migration, dan menggunakan `api/index.php` sebagai entrypoint Laravel.

PHP pada Vercel menggunakan runtime komunitas `vercel-php@0.8.0` (PHP 8.4), bukan runtime resmi Vercel. Uji semua fungsi setelah deployment pertama.

Routing deployment mengarahkan aset CSS/gambar secara eksplisit dan seluruh
halaman aplikasi ke fungsi `api/index.php`. Jangan menambahkan `handle:
filesystem` sebelum route Laravel karena `public/index.php` dapat terkirim
sebagai unduhan source PHP.

Entrypoint menetapkan `LARAVEL_STORAGE_PATH` ke `/tmp/laravel-storage` sebelum
bootstrap aplikasi agar log, session, cache, dan Blade dapat ditulis pada
filesystem sementara Vercel.

## Checklist setelah deploy

- Beranda, Kas Warga, Agenda, dan Momen dapat dibuka.
- CSS dan thumbnail sosial tampil.
- Login admin berhasil.
- Tambah/hapus Kas, Agenda, Pengurus, serta edit Pengumuman tetap tersimpan setelah redeploy.
- Galeri Google Drive tampil.
- `APP_DEBUG` tetap `false`.
- API key dibatasi hanya untuk Google Drive API.
