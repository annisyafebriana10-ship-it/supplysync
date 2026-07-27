# 🛡️ SupplySync: Secure Cloud-Based Management System

Repositori ini berisi kode sumber dan konfigurasi infrastruktur awan untuk sistem informasi **SupplySync**, sebuah proyek yang berfokus pada implementasi arsitektur keamanan web menggunakan pendekatan *Platform as a Service* (PaaS).

## 🚀 Teknologi dan Infrastruktur
* **Backend:** Laravel 11 (PHP 8.2)
* **Database:** PostgreSQL 18
* **Authentication:** Firebase Auth (Identity Management)
* **Cloud Deployment:** Railway.app
* **Build Engine:** Nixpacks

## 🔒 Implementasi Keamanan (CIA Triad)
Proyek ini mengimplementasikan prinsip Keamanan Informasi dengan spesifikasi berikut:
1. **Confidentiality:** Manajemen kredensial didelegasikan penuh ke infrastruktur Google melalui **Firebase Authentication**. Tidak ada sandi mentah atau *hash* yang disimpan di *database* lokal.
2. **Integrity & Firewall:** Melindungi peladen dari eksploitasi dan serangan DDoS ringan menggunakan **Rate Limiting (Throttle)** bawaan Laravel, dengan toleransi pembatasan akses anomali.
3. **Availability:** Sistem ekstraksi cadangan (*Automated Backup*) PostgreSQL yang dilindungi oleh otorisasi *middleware* khusus Administrator, dirancang untuk mencegah hilangnya data operasional.

## ⚙️ Konfigurasi Cloud (Config-as-Code)
Proyek ini menggunakan fail `nixpacks.toml` pada *root directory* untuk memaksakan orkestrasi *environment* secara spesifik (mengikat versi Node.js dan klien PostgreSQL) sebelum proses kompilasi (*build*) di Railway, guna mencegah bentrok dependensi.

---
*Dokumentasi ini disusun untuk memenuhi penilaian mata kuliah Keamanan Informasi.*
