EcoScore adalah platform berbasis web yang dirancang untuk memfasilitasi dan memantau program keberlanjutan dan *green campaign* di dalam sebuah organisasi. Sistem ini memungkinkan karyawan untuk berpartisipasi dalam berbagai misi lingkungan, melacak kemajuan mereka, mendapatkan poin, dan menerima pengakuan atas kontribusi mereka, sementara admin memiliki kontrol penuh untuk mengelola event, misi, dan verifikasi data.

-----

### Fitur Utama

Sistem ini menawarkan serangkaian fitur yang berfokus pada pengalaman pengguna (karyawan) dan fungsionalitas admin:

  * **Dashboard Interaktif:** Karyawan dapat melihat ringkasan progres misi yang sedang berjalan dalam bentuk bar progres. Dashboard juga menampilkan **leaderboard** yang menunjukkan peringkat karyawan berdasarkan total poin, memberikan motivasi kompetitif untuk berpartisipasi.
  * **Misi & Aktivitas:** Menu ini berisi daftar misi yang spesifik untuk sebuah event yang sedang diikuti. Setiap misi memiliki target yang jelas, seperti "Menanam 5 Pohon," dengan bar progres visual yang terakumulasi saat misi dijalankan.
  * **Verifikasi Berbasis Bukti:** Untuk menyelesaikan misi, karyawan harus mengunggah bukti foto. Unggahan ini kemudian dikirim ke admin untuk diverifikasi. Admin dapat menerima atau menolak bukti, memastikan integritas dan keaslian partisipasi.
  * **Akses Sertifikat Digital:** Karyawan dapat mengunduh sertifikat digital yang mereka peroleh setelah berhasil menyelesaikan semua misi dalam satu event. Sertifikat ini berfungsi sebagai pengakuan resmi atas partisipasi dan kontribusi mereka.
  * **Manajemen Akun:** Fitur dasar yang memungkinkan pengguna untuk melihat sertifikat yang telah didapat dan *logout* dari sistem.
  * **Manajemen Admin:** Admin memiliki kemampuan untuk membuat akun karyawan, mengelola event dan misi, serta memverifikasi bukti unggahan.

-----

### Alur Sistem (Peran Karyawan)

Berikut adalah alur kerja yang akan dialami oleh seorang karyawan saat menggunakan sistem EcoScore:

1.  **Login:** Karyawan masuk ke sistem menggunakan *username* dan *password* yang telah disediakan oleh admin.
2.  **Dashboard:** Setelah berhasil masuk, karyawan akan diarahkan ke dashboard. Di sini, mereka dapat melihat ringkasan progres misi mereka dan memeriksa posisi mereka di leaderboard.
3.  **Aktivitas/Misi:** Karyawan memilih menu `Aktivitas` untuk melihat daftar misi dari event yang sedang mereka ikuti. Setiap misi menampilkan judul, target, dan progres mereka dalam sebuah bar progres.
4.  **Unggah Bukti:** Setelah memilih misi, karyawan akan diminta untuk mengunggah foto sebagai bukti penyelesaian. Foto ini kemudian akan menunggu verifikasi dari admin.
5.  **Verifikasi Admin:** Admin akan meninjau unggahan tersebut. Jika bukti diterima, progres misi akan diperbarui dan poin akan ditambahkan ke akun karyawan. Jika ditolak, progres tidak akan berubah.
6.  **Sertifikat:** Ketika semua misi dalam satu event berhasil diselesaikan, sertifikat digital akan tersedia di menu `Akun`. Karyawan dapat mengunduh sertifikat tersebut sebagai bukti partisipasi.
7.  **Logout:** Karyawan dapat keluar dari sistem melalui menu `Akun`.

-----

### Skema Database

Berikut adalah skema database yang digunakan oleh sistem EcoScore, disajikan dalam pernyataan SQL `CREATE TABLE`.

```sql
CREATE TABLE `certificate` (  
  `id_certificate` int NOT NULL,  
  `id_user` int DEFAULT NULL,  
  `id_event` int DEFAULT NULL,  
  `certificate_name` varchar(100) NOT NULL,  
  `file_path` varchar(255) NOT NULL,  
  `issued_date` date NOT NULL  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;  

CREATE TABLE `event` (  
  `id_event` int NOT NULL,  
  `event_name` varchar(100) NOT NULL,  
  `start_date` date DEFAULT NULL,  
  `end_date` date DEFAULT NULL,  
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;  

CREATE TABLE `mission` (  
  `id_mission` int NOT NULL,  
  `id_event` int DEFAULT NULL,  
  `title` varchar(100) NOT NULL,  
  `target` int NOT NULL,  
  `point` int NOT NULL,  
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;  

CREATE TABLE `upload` (  
  `id_upload` int NOT NULL,  
  `id_user` int DEFAULT NULL,  
  `id_mission` int DEFAULT NULL,  
  `file_path` varchar(255) NOT NULL,  
  `status` enum('Menunggu Verifikasi','Terverifikasi','Ditolak') DEFAULT 'Menunggu Verifikasi',  
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,  
  `verified_at` timestamp NULL DEFAULT NULL  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;  

CREATE TABLE `user` (  
  `id_user` int NOT NULL,  
  `name` varchar(100) NOT NULL,  
  `username` varchar(50) NOT NULL,  
  `password` varchar(255) NOT NULL,  
  `role` enum('karyawan','admin') DEFAULT 'karyawan',  
  `total_point` int DEFAULT '0',  
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;  

CREATE TABLE `user_mission` (  
  `id_user_mission` int NOT NULL,  
  `id_user` int DEFAULT NULL,  
  `id_mission` int DEFAULT NULL,  
  `submitted_at` timestamp NULL DEFAULT NULL,  
  `verified_at` timestamp NULL DEFAULT NULL,  
  `progress` int DEFAULT '0',  
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```