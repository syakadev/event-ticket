🧪 LATIHAN UK
🎯 Tema: Sistem Informasi Pemesanan Tiket Event Berbasis Web
Teknologi: PHP Native + MySQL + Bootstrap
________________________________________
🔹 BAGIAN A: Persiapan Database
Tugas 1
Buat database dengan nama:
event_tiket
Tugas 2
 
Buat tabel sesuai ERD:
●	users
●	venue
●	event
●	tiket
●	orders
●	order_detail
●	voucher
●	attendee
Tugas 3
Tentukan:
●	Primary Key tiap tabel
●	Foreign Key sesuai relasi pada ERD
📌 Output:
●	Script SQL lengkap (CREATE TABLE + relasi)
________________________________________
🔹 BAGIAN B: Sistem Login
Tugas 4
Buat sistem login dengan ketentuan:
●	Input: email & password
●	Role: admin dan user
●	Redirect:
○	admin → dashboard admin
○	user → dashboard user
Tugas 5
Buat fitur logout menggunakan session
📌 Output:
●	Halaman login berfungsi
●	Session login aktif
________________________________________
🔹 BAGIAN C: CRUD Master Data (Admin)
Tugas 6
Buat CRUD Venue
●	Tambah
●	Edit
●	Hapus
●	Tampil data
Tugas 7
Buat CRUD Event
●	Relasi dengan venue
●	Input tanggal event
Tugas 8
Buat CRUD Tiket
●	Relasi ke event
●	Input harga & kuota
Tugas 9
Buat CRUD Voucher
●	Kode voucher
●	Potongan harga
●	Status aktif/nonaktif
📌 Output:
●	Halaman admin dengan fitur CRUD lengkap
________________________________________
🔹 BAGIAN D: Pemesanan Tiket (User)
Tugas 10
Buat halaman:
●	Daftar event
●	Detail tiket
Tugas 11
Buat fitur pemesanan:
●	Pilih tiket
●	Input jumlah (qty)
●	Hitung subtotal
Tugas 12
Simpan ke:
●	tabel orders
●	tabel order_detail
📌 Output:
●	Data transaksi tersimpan di database
________________________________________
🔹 BAGIAN E: Voucher & Pembayaran
Tugas 13
Tambahkan fitur:
●	Input kode voucher
●	Validasi voucher (aktif & tersedia)
●	Hitung diskon
Tugas 14
Update:
●	total pembayaran setelah diskon
●	status order menjadi:
○	pending
○	paid
📌 Output:
●	Perhitungan total dengan voucher
________________________________________
🔹 BAGIAN F: Generate Tiket (Attendee)
Tugas 15
Setelah order dibuat:
●	Generate kode tiket unik
●	Simpan ke tabel attendee
Tugas 16
Jumlah tiket sesuai qty pembelian
📌 Output:
●	Setiap pembelian menghasilkan kode tiket
________________________________________
🔹 BAGIAN G: Check-in Tiket
Tugas 17
Buat halaman check-in:
●	Input kode tiket
Tugas 18
Jika kode valid:
●	Update status_checkin = "sudah"
●	Simpan waktu_checkin
📌 Output:
●	Sistem check-in berjalan
________________________________________
🔹 BAGIAN H: Dashboard & Laporan
Tugas 19
Buat dashboard admin:
●	Total user
●	Total order
●	Total pendapatan
Tugas 20
Buat laporan:
●	Data transaksi
●	Data tiket terjual
📌 Output:
●	Halaman dashboard dengan data statistik
________________________________________
🔹 BAGIAN I: Tampilan UI
Tugas 21
Gunakan Bootstrap:
●	Responsive
●	Layout card untuk event
●	Tabel untuk data admin
📌 Output:
●	Tampilan modern & rapi
________________________________________
🔹 BAGIAN J: Soal HOTS (Analisis & Pengembangan)
Tugas 22
Jelaskan cara:
●	Mencegah pembelian melebihi kuota tiket
Tugas 23
Buat query:
●	Menampilkan total tiket terjual per event
Tugas 24
Buat fitur:
●	Riwayat pembelian user
Tugas 25
Analisis:
●	Apa yang terjadi jika voucher tidak dibatasi kuota?
________________________________________
🔹 BONUS (Nilai Tambahan)
Pilih minimal 1:
●	Export laporan ke PDF / Excel
●	Tambahkan grafik dashboard
●	Upload gambar event
●	Tambahkan pagination & search
________________________________________
🎯 Output Akhir
Mahasiswa menghasilkan:
●	Aplikasi web event tiket
●	Database sesuai ERD
●	Sistem login & transaksi berjalan

