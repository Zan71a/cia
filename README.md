# BHINEKA.SPACE + Admin Panel

Fitur yang sudah dibuat:

- Login admin
- Dashboard edit konten website
- Edit hero/beranda
- Edit korelasi/filosofi
- Edit portal VR
- Edit kabar digital/berita
- Edit anggota tim
- Upload gambar portal dan foto anggota
- Data tersimpan di `data/content.json`
- Halaman depan `index.php` otomatis berubah setelah disimpan
- Animasi scroll storytelling tetap aktif

## Login default

- URL: `admin/login.php`
- Username: `admin`
- Password: `admin123`

Setelah website online, ganti password di file:

`admin/includes/auth.php`

Cara membuat hash password baru:

```php
<?php echo password_hash('password-baru-kamu', PASSWORD_DEFAULT); ?>
```

Lalu replace isi `ADMIN_PASSWORD_HASH`.

## Cara menjalankan di XAMPP

1. Extract folder ini ke `htdocs/bhineka_admin`
2. Jalankan Apache di XAMPP
3. Buka `http://localhost/bhineka_admin/`
4. Buka admin di `http://localhost/bhineka_admin/admin/login.php`

## Catatan gambar

File gambar lama seperti `borobudur.png`, `pujamandala.png`, `amin.jpeg`, `jihan.jpeg`, dan lainnya harus ditaruh di root folder project ini, sejajar dengan `index.php`.

Kalau upload gambar dari dashboard, file otomatis masuk ke folder `uploads/`.

## Penting

Folder `data/` harus bisa ditulis server. Kalau simpan gagal di hosting, ubah permission folder `data` dan file `data/content.json` agar writable.
