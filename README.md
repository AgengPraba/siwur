# Panduan Setup dan Menjalankan Chatbot Microservice

Dokumen ini memberikan panduan untuk menjalankan aplikasi Laravel (TALL Stack) dan service AI (Python/FastAPI) secara bersamaan untuk fitur chatbot.

## Prasyarat

1.  **PHP & Composer**: Pastikan PHP (>= 8.2) dan Composer terinstal.
2.  **Node.js & NPM**: Diperlukan untuk Vite.
3.  **Python & Pip**: Pastikan Python (>= 3.9) dan pip terinstal.
4.  **Akun Google AI**: Diperlukan untuk mendapatkan `GEMINI_API_KEY`. Kunjungi [Google AI Studio](https://aistudio.google.com/app/apikey) untuk mendapatkannya.
5.  **Database**: Database yang kompatibel dengan Laravel (misalnya, MySQL, PostgreSQL).

---

## 1. Setup Backend Laravel (API Gateway)

Backend Laravel bertindak sebagai aplikasi utama dan sebagai gateway yang menghubungkan frontend dengan service AI.

### Langkah-langkah:

1.  **Clone Repository & Install Dependensi**
    ```bash
    # Arahkan ke direktori proyek Laravel Anda
    cd /path/to/your/siwur_livewire

    # Install dependensi PHP
    composer install

    # Install dependensi JavaScript
    npm install
    ```

2.  **Konfigurasi Environment (.env)**

    Salin file `.env.example` jika Anda belum memiliki `.env`.
    ```bash
    cp .env.example .env
    ```

    Buka file `.env` dan konfigurasikan variabel berikut:
    ```env
    # Konfigurasi Database Anda
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nama_database_anda
    DB_USERNAME=user_database_anda
    DB_PASSWORD=password_database_anda

    # URL untuk AI Service (sesuaikan port jika berbeda)
    AI_SERVICE_URL=http://127.0.0.1:8001

    # Kunci API Gemini (sama dengan yang di service AI)
    GEMINI_API_KEY="PASTE_YOUR_GEMINI_API_KEY_HERE"
    ```

3.  **Generate Kunci Aplikasi & Migrasi Database**
    ```bash
    php artisan key:generate
    php artisan migrate --seed # Jalankan migrasi dan seeder jika ada
    ```

4.  **Jalankan Laravel Development Server & Vite**

    Buka **dua terminal terpisah** di dalam direktori proyek Laravel.

    *   **Terminal 1 (Menjalankan Server PHP):**
        ```bash
        php artisan serve
        ```
        Server akan berjalan di `http://127.0.0.1:8000`.

    *   **Terminal 2 (Menjalankan Vite untuk Frontend):**
        ```bash
        npm run dev
        ```
        Vite akan memantau perubahan pada file CSS/JS.

---

## 2. Setup Backend Python (FastAPI AI Service)

Service ini menangani semua logika AI, termasuk RAG (Retrieval-Augmented Generation) dan evaluasi.

### Langkah-langkah:

1.  **Arahkan ke Direktori Service AI**
    ```bash
    cd /path/to/your/siwur_livewire/ai_service
    ```

2.  **Buat dan Aktifkan Virtual Environment**

    Sangat disarankan untuk menggunakan virtual environment agar dependensi tidak tercampur.
    ```bash
    # Buat virtual environment
    python3 -m venv venv

    # Aktifkan (macOS/Linux)
    source venv/bin/activate

    # Aktifkan (Windows)
    # .\venv\Scripts\activate
    ```

3.  **Install Dependensi Python**
    ```bash
    pip install -r requirements.txt
    ```

4.  **Konfigurasi Environment (.env)**

    Buat file `.env` di dalam direktori `ai_service`.
    ```env
    # Kunci API dari Google AI Studio
    GEMINI_API_KEY="PASTE_YOUR_GEMINI_API_KEY_HERE"

    # Set ke "true" untuk evaluasi, "false" untuk produksi/kecepatan
    ENABLE_RAG_EVALUATION=false
    ```
    **Penting**: Pastikan `GEMINI_API_KEY` di sini sama dengan yang ada di `.env` Laravel.

5.  **Jalankan FastAPI Server**

    Buka terminal baru di direktori `ai_service` (pastikan virtual environment aktif).
    ```bash
    uvicorn app:app --reload --port 8001
    ```
    *   `app:app`: Memberitahu Uvicorn untuk mencari objek `app` di dalam file `app.py`.
    *   `--reload`: Server akan otomatis restart jika ada perubahan kode.
    *   `--port 8001`: Menjalankan server di port 8001 (sesuai dengan `AI_SERVICE_URL` di Laravel).

    Anda dapat memverifikasi service berjalan dengan membuka `http://127.0.0.1:8001` di browser.

---

## Ringkasan Proses Berjalan

Setelah semua langkah di atas selesai, Anda akan memiliki:
1.  **Terminal 1**: Menjalankan `php artisan serve` (Laravel).
2.  **Terminal 2**: Menjalankan `npm run dev` (Vite).
3.  **Terminal 3**: Menjalankan `uvicorn app:app --reload --port 8001` (FastAPI).

Sekarang, buka aplikasi Laravel Anda di browser (`http://127.0.0.1:8000`), login, dan klik ikon chatbot untuk mulai berinteraksi.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# siwur
