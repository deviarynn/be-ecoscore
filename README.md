## Life Sync
"Life Sync" adalah sebuah aplikasi internal yang dirancang untuk membantu menanamkan budaya dan kebiasaan positif di lingkungan kerja. Aplikasi ini memungkinkan karyawan untuk berpartisipasi dalam berbagai misi atau tantangan yang bertujuan untuk meningkatkan produktivitas, kesehatan, dan kebersamaan tim.

### Fitur Utama
#### Tampilan Admin
1. Akses penuh untuk mengelola dan memantau seluruh sistem.

2. Manajemen Misi: Admin dapat membuat, membaca, memperbarui (update), dan menghapus (delete) misi yang ada. Setiap misi memiliki poin tertentu yang akan didapatkan oleh peserta setelah misi diselesaikan dan disetujui. Setiap misi juga harus ditentukan Penanggung Jawabnya yang akan bertugas untuk menyetujui (approve) bukti penyelesaian misi.

3. Manajemen Peserta: Admin bisa melihat tabel seluruh peserta, lengkap dengan total poin yang telah mereka kumpulkan. Admin juga dapat melihat progress spesifik setiap peserta, termasuk riwayat unggahan bukti untuk setiap misi yang mereka ikuti.

4. Tampilan Penanggung Jawab
Dashboard khusus untuk mengelola dan memvalidasi penyelesaian misi.

5. Dashboard Misi: Penanggung Jawab akan melihat ringkasan bukti atau berkas yang belum disetujui hari ini.

6. Validasi Bukti: Penanggung Jawab dapat melihat daftar lengkap peserta, nama misi, bukti berupa foto atau berkas yang diunggah, serta opsi untuk menyetujui (approve) atau menolak (decline) bukti tersebut.

### Alur Sistem
1. Administrasi (Admin)
Admin masuk ke sistem dan menuju menu Misi.

Di sini, Admin membuat misi baru, menentukan poin, dan menunjuk Penanggung Jawab untuk misi tersebut.

Di menu Peserta, Admin dapat memantau progres dan total poin seluruh peserta.

Ketika mengklik salah satu peserta, Admin bisa melihat riwayat detail unggahan bukti yang telah dikerjakan oleh peserta.

2. Validasi (Penanggung Jawab)
Penanggung Jawab masuk ke sistem. Dashboard utamanya menampilkan bukti-bukti yang belum di-approve hari ini.

Penanggung Jawab dapat mengakses menu Peserta untuk melihat detail bukti yang diunggah oleh setiap partisipan.

Penanggung Jawab akan mengecek bukti (foto/berkas) dan melakukan persetujuan atau penolakan.

### Struktur Database
Berikut adalah skema tabel yang digunakan dalam sistem Life Sync:

1. user
Tabel ini menyimpan data pengguna sistem, baik karyawan maupun admin.

id_user: Primary Key, auto-increment

name: Nama lengkap pengguna

username: Username, harus unik

password: Password pengguna

role: Peran pengguna (karyawan atau admin)

total_point: Total poin yang dikumpulkan pengguna

created_at: Timestamp waktu pembuatan akun

2. mission
Tabel ini menyimpan detail dari setiap misi yang dibuat oleh admin.

id_mission: Primary Key, auto-increment

title: Judul misi

deskripsi: Deskripsi lengkap tentang misi

point: Poin yang didapat jika misi selesai

penanggungjawab: Nama atau ID penanggung jawab misi

start: Waktu mulai misi

end: Waktu berakhir misi

created_at: Timestamp waktu pembuatan misi

3. user_mission
Tabel pivot ini mencatat misi yang telah diambil oleh setiap pengguna.

id_user_mission: Primary Key, auto-increment

id_user: Foreign Key ke tabel user

id_mission: Foreign Key ke tabel mission

submitted_at: Waktu pengguna menyerahkan bukti

verified_at: Waktu bukti diverifikasi

updated_at: Timestamp waktu terakhir diperbarui

created_at: Timestamp waktu dibuat

4. upload
Tabel ini digunakan untuk menyimpan riwayat unggahan bukti penyelesaian misi.

id_upload: Primary Key, auto-increment

id_user: Foreign Key ke tabel user

id_mission: Foreign Key ke tabel mission

file_path: Path lokasi file bukti (foto/berkas)

status: Status verifikasi bukti (Menunggu Verifikasi, Terverifikasi, Ditolak)

uploaded_at: Waktu unggahan file

verified_at: Waktu verifikasi file

5. certificate
Tabel ini mencatat sertifikat atau penghargaan yang diterima oleh pengguna.

id_certificate: Primary Key, auto-increment

id_user: Foreign Key ke tabel user

certificate_name: Nama sertifikat

file_path: Path lokasi file sertifikat

issued_date: Tanggal sertifikat diterbitkan

6. log
Tabel untuk mencatat aktivitas terbaru dalam sistem.

id_log: Primary Key, auto-increment

id_user_mission: Foreign Key ke tabel user_mission

aktivitas_terbaru: Deskripsi singkat tentang aktivitas

timestamps: Timestamp waktu aktivitas


### Petunjuk Instalansi
1. Masukan query SQL dibawah ini untuk export database

CREATE DATABASE ecoscore;

-- Tabel 'user'
-- Menyimpan data pengguna, baik karyawan maupun admin
--
CREATE TABLE user (
    id_user INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('karyawan', 'admin') NOT NULL DEFAULT 'karyawan',
    total_point INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--
-- Tabel 'mission'
-- Menyimpan detail dari setiap misi yang dibuat oleh admin
--
CREATE TABLE mission (
    id_mission INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    deskripsi TEXT NOT NULL,
    point INT(11) NOT NULL,
    penanggungjawab VARCHAR(100),
    start TIME,
    end TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--
-- Tabel 'user_mission'
-- Mencatat hubungan antara pengguna dan misi yang mereka ikuti
--
CREATE TABLE user_mission (
    id_user_mission INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user INT(11) UNSIGNED,
    id_mission INT(11) UNSIGNED,
    submitted_at TIMESTAMP NULL,
    verified_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE SET NULL,
    FOREIGN KEY (id_mission) REFERENCES mission(id_mission) ON DELETE SET NULL
);

--
-- Tabel 'upload'
-- Menyimpan riwayat bukti yang diunggah oleh pengguna
--
CREATE TABLE upload (
    id_upload INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user INT(11) UNSIGNED,
    id_mission INT(11) UNSIGNED,
    file_path VARCHAR(255) NOT NULL,
    status ENUM('Menunggu Verifikasi', 'Terverifikasi', 'Ditolak') NOT NULL DEFAULT 'Menunggu Verifikasi',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP NULL,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE SET NULL,
    FOREIGN KEY (id_mission) REFERENCES mission(id_mission) ON DELETE SET NULL
);

--
-- Tabel 'certificate'
-- Mencatat sertifikat atau penghargaan yang diterima pengguna
--
CREATE TABLE certificate (
    id_certificate INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user INT(11) UNSIGNED,
    certificate_name VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    issued_date DATE NOT NULL,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE SET NULL
);

--
-- Tabel 'log'
-- Mencatat aktivitas terbaru dalam sistem
--
CREATE TABLE log (
    id_log INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user_mission INT(11) UNSIGNED,
    aktivitas_terbaru VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user_mission) REFERENCES user_mission(id_user_mission) ON DELETE SET NULL
);
