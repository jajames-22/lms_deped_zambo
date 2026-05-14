# DepEd Zamboanga City Division - Learning Management System (LMS)

A centralized, full-stack Learning Management System designed to facilitate the distribution, management, and tracking of educational materials and examinations for the Department of Education (DepEd) Zamboanga City Division.

Created by **Graziella Marife S. Saavedra & James Benedict A. Rojas** (WMSU - College of Computing Studies) in collaboration with the DepEd Zamboanga Information Technology team.

---

## 🚀 Tech Stack

* **Backend:** [Laravel](https://laravel.com/) (PHP)
* **Frontend:** Blade Templating, [Tailwind CSS v4](https://tailwindcss.com/), Vanilla JavaScript
* **Database:** MySQL
* **File Processing:** [Laravel Excel (Maatwebsite)](https://docs.laravel-excel.com/) for CSV/XLSX imports
* **QR Code Generation:** [SimpleSoftwareIO Simple QrCode](https://github.com/SimpleSoftwareIO/simple-qrcode) for generating QR codes
* **ID Obfuscation:** [Vinkla Hashids](https://github.com/vinkla/hashids) for encoding/obfuscating numeric IDs in URLs
* **Asset Bundling:** Vite
* **Interactive Document Viewing:** PDF.js

---

## 📋 Core Features

* **Role-Based Access Control:** Secure dashboards for Administrators, CID Personnel, Teachers, and Students.
* **Module Management:** Create and publish rich learning materials containing text, video, and integrated PDF viewing.
* **Interactive Study Mode:** Students can navigate a timeline-based curriculum that tracks their progress, saves states automatically, and issues completion metrics.
* **Dynamic Examinations:** Built-in interactive quizzes (Multiple Choice, Checkbox, True/False, Text/Essay) integrated directly into the study flow.
* **Grading & Certification:** Dynamic sliders to set examination weights and passing score percentages.
* **Strict Retake Limits:** Students are granted a maximum of 3 retake attempts for failed modules. Reaching the limit permanently locks the material to maintain academic rigor.
* **Account Security & Integrity:** Users are restricted from changing their personal information and passwords more than once every 30 days to enforce accountability.
* **Bulk User Management:** Mass import and registration of Students and Personnel using `.csv` and `.xlsx` templates with automated conflict resolution and username generation.

---

## ⚙️ Prerequisites

Before you begin, ensure you have the following installed on your local machine or server:

* PHP >= 8.1
* Composer
* Node.js & NPM
* MySQL or MariaDB

---

## 🛠️ Local Installation & Setup

Follow these steps to get the project running in your local development environment:

**1. Clone the repository**
```bash
git clone <your-repository-url>
cd deped-zamboanga-lms
```

**2. Install PHP Dependencies**
```bash
composer install
```

**3. Install Required Composer Packages**
```bash
composer require maatwebsite/excel
composer require simplesoftwareio/simple-qrcode
composer require vinkla/hashids
```

**4. Install Frontend Dependencies**
```bash
npm install
```

**5. Configure Environment Variables**

Copy the example environment file and update it with your local settings:
```bash
cp .env.example .env
```

Open `.env` and update the following values:
```env
APP_NAME="DepEd Zamboanga LMS"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=deped_zamboanga_lms
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

**6. Generate Application Key**
```bash
php artisan key:generate
```

**7. Run Database Migrations & Seeders**

This will create the necessary tables and populate the system with default roles and admin accounts:
```bash
php artisan migrate:fresh --seed
```

**8. Create Storage Symlink**

Required for viewing uploaded thumbnails, PDFs, videos, and profile avatars:
```bash
php artisan storage:link
```

**9. Build Frontend Assets**

Compile the Tailwind CSS v4 and JavaScript files using Vite:
```bash
# For development (with hot reloading):
npm run dev

# For production (minified):
npm run build
```

**10. Start the Local Development Server**
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

---

## 👥 User Roles

The system supports four distinct roles, each with a dedicated dashboard and permissions:

| Role | Description |
|---|---|
| **Administrator** | Full system access. Manages all users, schools, modules, and system settings. |
| **CID Personnel** | Curriculum and Instruction Division staff. Creates and publishes learning modules and examinations. |
| **Teacher** | Monitors student progress and examination results within their assigned classes. |
| **Student** | Accesses assigned modules, completes interactive study sessions, and takes examinations. |

### Default Administrator Credentials

After seeding, log in with the default admin account:

```
Email:    admin@deped-zamboanga.edu.ph
Password: password
```

> ⚠️ **Change these credentials immediately** after your first login in a production environment.

---

## 📁 Bulk User Import

The system supports mass registration of users via `.csv` or `.xlsx` file uploads.

1. Download the provided import template from the Admin dashboard under **User Management > Import Users**.
2. Fill in the required fields (name, email, role, school, etc.).
3. Upload the completed file. The system will automatically:
   - Generate unique usernames.
   - Resolve duplicate conflicts.
   - Send credentials to newly created accounts (if mail is configured).

---

## ✉️ Mail Configuration (Optional)

To enable email notifications (e.g., account credentials on bulk import), configure your mail driver in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@deped-zamboanga.edu.ph
MAIL_FROM_NAME="DepEd Zamboanga LMS"
```

---

## 💻 Essential Developer Commands

During development, especially when modifying routes or Blade files, you may need to clear Laravel's caches:

```bash
# Clear all application cache
php artisan optimize:clear

# Clear route cache (after adding/changing routes in web.php)
php artisan route:clear

# Clear view cache (if Blade edits aren't showing up)
php artisan view:clear
```

---

## 🚢 Production Deployment Checklist

When deploying to a live server (e.g., Hostinger, DigitalOcean, AWS), run the following to ensure maximum performance and security:

**1. Set environment to production in `.env`:**
```env
APP_ENV=production
APP_DEBUG=false
```

**2. Install optimized dependencies:**
```bash
composer install --optimize-autoloader --no-dev
```

**3. Build production assets:**
```bash
npm run build
```

**4. Cache configurations and routes:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

**5. Ensure storage link exists on server:**
```bash
php artisan storage:link
```

Ensure your web server (Apache/Nginx) points its document root to the `/public` directory of the project.

### Sample Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/deped-zamboanga-lms/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🧪 Running Tests

```bash
php artisan test
```

---

## 🤝 Contributing

This project was developed as a capstone collaboration between WMSU - College of Computing Studies and the DepEd Zamboanga City Division IT team. For bug reports or feature suggestions, please open an issue or contact the development team directly.

---

## 📄 License

This project is proprietary software developed for the **Department of Education – Zamboanga City Division**. Unauthorized distribution or commercial use is prohibited without express written consent from the authors and DepEd Zamboanga City Division.

---

*Developed with ❤️ by Graziella Marife S. Saavedra & James Benedict A. Rojas — WMSU College of Computing Studies*