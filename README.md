# Proyek SIP

Sistem Informasi Perpustakaan adalah aplikasi berbasis komputer yang dirancang untuk membantu pengelolaan perpustakaan secara efisien. Sistem ini memungkinkan pengelolaan data buku, peminjaman, pengembalian, dan anggota perpustakaan secara otomatis. Fitur utama dalam sistem ini meliputi pencatatan buku, peminjaman dan pengembalian buku, manajemen anggota, serta laporan-laporan terkait status peminjaman dan stok buku. Dengan menggunakan sistem ini, perpustakaan dapat meningkatkan efisiensi operasional, mengurangi kesalahan manual, dan memberikan layanan yang lebih cepat serta akurat bagi pengunjung perpustakaan.

## Prasyarat Sistem

Sebelum menginstal dan menjalankan proyek ini, pastikan Anda memiliki hal-hal berikut:

- **Node.js** versi LTS (Long Term Support).
- **NPM** (Node Package Manager), yang biasanya sudah terinstal bersama Node.js.
- **XAMPP/Laragon**
- **Tested di PHP 8.3**

---

## Langkah Instalasi

### 1. **Instal Node.js**

Jika Anda belum menginstal Node.js, Anda bisa mengunduhnya dari situs resmi:

- [Unduh Node.js](https://nodejs.org/)

Pilih versi **LTS** untuk stabilitas terbaik. Setelah mengunduh, ikuti instruksi instalasi sesuai sistem operasi Anda.

### 2. **Verifikasi Instalasi**

Setelah menginstal Node.js dan NPM, buka terminal atau command prompt dan jalankan perintah berikut untuk memverifikasi instalasi:

```bash
node -v
npm -v
```

### 3. **Install Requirement**

Nginstal kebutuh proyek sesuai package.json

```bash
npm install
```

### 4. **Import SQL**

Import file SQL di folder sql/sistem_informasi_perpustakaan.sql


## Langkah Menjalankan di local

### 1. **Dir!**

Pastikan directory proyek berada di htdocs(XAMPP) / www(Laragon)

### 2. **NPM LARI**

Dikarnakan menggunakan tailwind dan auto compile, ada command tambahan yaitu:

```bash
npm run dev
```