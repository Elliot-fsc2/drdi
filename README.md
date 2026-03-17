# DRDI Project

A Laravel-based application built with Filament, Livewire, and Tailwind CSS for managing theses, proposals, students, instructors, and related academic processes.

## Requirements

Before you begin, ensure you have the following installed on your local machine:

- **PHP**: ^8.3 (Requires PHP 8.3.20 as per project guidelines)
- **Composer**: Dependency manager for PHP
- **Node.js & NPM**: For managing frontend assets (Vite and Tailwind CSS)
- **Database Server**: MySQL, PostgreSQL, or SQLite
- **Web Server**: Apache, Nginx, or a local development environment like Laragon (recommended for Windows), Laravel Herd, or Laravel Sail.

## Installation & Setup

Follow these steps to set up the project locally:

1. **Clone the repository** (if you haven't already):

   ```bash
   git clone <repository-url>
   cd drdi
   ```

2. **Install PHP dependencies**:

   ```bash
   composer install
   ```

3. **Install NPM dependencies**:

   ```bash
   npm install
   ```

4. **Environment Configuration**:
   Copy the example environment file and create your own `.env` file:

   ```bash
   cp .env.example .env
   ```

5. **Generate Application Key**:

   ```bash
   php artisan key:generate
   ```

6. **Database Setup**:
   Open the `.env` file and configure your database credentials. For example:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=drdi
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. **Run Database Migrations & Seeders**:
   Run the migrations to create the database tables, and optionally seed them with initial data:

   ```bash
   php artisan migrate --seed
   ```

8. **Build Frontend Assets**:
   Compile the Tailwind CSS and other frontend assets using Vite:

   ```bash
   npm run build
   # Or for active development:
   npm run dev
   ```

9. **Link Storage (Optional but recommended)**:
   If the application uploads files, you will need to link the storage directory:

   ```bash
   php artisan storage:link
   ```

10. **Serve the Application**:
    If you are not using Laragon/Herd/Valet, you can use the built-in Laravel server:
    ```bash
    php artisan serve
    ```
    The application will be accessible at `http://localhost:8000`.

## Testing

This project uses **Pest** for testing. To run the test suite, execute:

```bash
php artisan test
```

## Formatting

This project enforces code styles using **Laravel Pint**. To automatically format your code before committing, run:

```bash
vendor/bin/pint --dirty --format agent
```

## Technologies Used

- [Laravel 12](https://laravel.com)
- [Filament v3](https://filamentphp.com) (Admin Panel / Server-Driven UI)
- [Livewire v3](https://livewire.laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Pest PHP](https://pestphp.com)
