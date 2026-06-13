# Analisis Tugas 3: Integrasi Sistem Enterprise

## 1. Analisis Transaksi
Berdasarkan kebutuhan integrasi sistem, berikut adalah pembagian transaksi yang diimplementasikan:

### A. Transaksi Penting (SOAP - Synchronous)
- **Aktivitas:** *Update Inventory* (Pembaruan Stok Barang).
- **Alasan:** Transaksi ini dikategorikan sebagai transaksi penting karena perubahan data stok berdampak langsung pada neraca perusahaan dan akurasi sistem. Memerlukan kepastian bahwa data telah diterima dan diproses oleh server pusat.
- **Metode:** SOAP (*Simple Object Access Protocol*).
- **Tujuan:** Mengirimkan log audit ke server pusat (IAE-SSO) agar data tercatat secara permanen (*immutable log*) dan mendapatkan *Receipt Number* sebagai bukti sah transaksi.

### B. Transaksi Broadcast (RabbitMQ - Asynchronous)
- **Aktivitas:** Notifikasi Perubahan Stok.
- **Alasan:** Perubahan stok perlu diketahui oleh departemen lain (misal: *Sales* atau *Procurement*) tanpa mengganggu kinerja transaksi utama.
- **Metode:** AMQP (*RabbitMQ*).
- **Tujuan:** Melakukan *broadcast* event secara asinkron agar sistem lain dapat melakukan pembaruan tampilan atau data mereka tanpa menunggu proses SOAP audit selesai.

## 2. Sequence Diagram Internal
Diagram berikut menggambarkan aliran interaksi aplikasi dengan layanan pusat:

```mermaid
sequenceDiagram
    participant User
    participant App as Aplikasi Laravel
    participant SSO as IAE SSO Server
    participant SOAP as IAE SOAP Audit

    User->>App: Update Inventory
    App->>SSO: POST /api/v1/auth/token (M2M Auth)
    SSO-->>App: Return JWT Token
    App->>SOAP: POST /soap/v1/audit (Kirim XML)
    SOAP-->>App: Return Receipt Number
    App-->>User: Berhasil (Tampilkan Receipt Number)