# ThreatMetrics — Cyber Threat Intelligence & SOC Dashboard

Aplikasi web berbasis **Laravel** untuk memantau, menganalisis, dan mengelola data indikator ancaman siber (*Indicator of Compromise* / IOC) serta catatan diskusi anonim 
tim analis secara terpusat. Proyek ini dikembangkan sebagai tugas **Mata Kuliah Pemrograman Web — Kelompok 10**.

## Fitur Utama

* **Threat Intelligence Overview (`/threatmetrics`)**: Dashboard ringkasan metrik ancaman siber, rentang tanggal (`start_date` & `end_date`), dan navigasi ke *Analytics*, *Alerts*, serta *IOC Data*.
* **Sinkronisasi TweetFeed (`/tweetfeed/sync`)**: Integrasi sinkronisasi data ancaman dari TweetFeed ke dalam sistem.
* **Tabel IOC Interaktif (`/threats/data`)**: Endpoint *server-side* untuk DataTables beserta alur CRUD data ancaman (`create`, `edit`, `show`, `destroy`) dan import file (`.xlsx`, `.csv`, `.txt`) termasuk unduhan template import.
* **Ekspor Laporan Terfilter (`/threats/export/{format}` & `/threats/export/pdf`)**: Mendukung ekspor data terfilter ke format `.xlsx`, `.csv`, paket SPSS, dan `.pdf`.
* **Analyst Notes & Quick Discussion (`/analyst-notes`)**: Kolom catatan dan diskusi tim anonim dengan generator nama panggilan acak, editor teks kaya (*Quill Editor*), serta penyimpanan persisten ke database (`analyst_notes`) dengan respons JSON.

## Stack Teknologi

* **Backend**: PHP 8.x & Laravel Framework
* **Database**: MySQL (`db_threatmetrics`) via Laragon (serta dukungan konfigurasi koneksi database/deployment pada repository)
* **Frontend**: Blade Templates, Bootstrap, DataTables, Quill Editor, dan JavaScript
* **Library Dokumen/Spreadsheet**: PhpOffice PhpSpreadsheet (`.xlsx` & `.csv`) serta controller ekspor PDF

## Struktur Direktori Utama

```text
ThreatMetrics/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AnalystNoteController.php
│   │       ├── DashboardController.php
│   │       ├── Integration/
│   │       │   └── TweetFeedSyncController.php
│   │       └── Threat/
│   │           ├── ThreatController.php
│   │           ├── ThreatDataTableController.php
│   │           ├── ThreatExportController.php
│   │           ├── ThreatImportController.php
│   │           └── ThreatPdfController.php
│   └── Models/
│       ├── AnalystNote.php
│       ├── ApiSyncState.php
│       └── Threat.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── dashboard/
│       ├── layouts/
│       └── threats/
│           └── partials/
│               └── analyst-notes.blade.php
└── routes/
    └── web.php