# DepEd Zamboanga City Division - Learning Management System (LMS)

A centralized, full-stack Learning Management System designed to facilitate the distribution, management, and tracking of educational materials and examinations for the Department of Education (DepEd) Zamboanga City Division.

Created by **Graziella Marife S. Saavedra & James Benedict A. Rojas** (WMSU - College of Computing Studies) in collaboration with the DepEd Zamboanga Information Technology team.

---

## 🚀 Tech Stack

* **Backend:** [Laravel](https://laravel.com/) (PHP)
* **Frontend:** Blade Templating, [Tailwind CSS v4](https://tailwindcss.com/), Vanilla JavaScript
* **Database:** MySQL
* **File Processing:** Laravel Excel (Maatwebsite) for CSV/XLSX imports
* **Asset Bundling:** Vite
* **Interactive Document Viewing:** PDF.js

---

## 📋 Core Features

* **Role-Based Access Control:** Secure dashboards for Administrators, CID Personnel, Teachers, and Students.
* **Module Management:** Create and publish rich learning materials containing text, video, and integrated PDF viewing.
* **Interactive Study Mode:** Students can navigate a timeline-based curriculum that tracks their progress, saves states automatically, and issues completion metrics.
* **Dynamic Examinations:** Built-in interactive quizzes (Multiple Choice, Checkbox, True/False, Text/Essay) integrated directly into the study flow.
* **Grading & Certification:** Dynamic sliders to set examination weights and passing score percentages.
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

**3. Install Frontend Dependencies**
```bash
npm install
```

**4. Configure Environment Variables**

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

**5. Generate Application Key**
```bash
php artisan key:generate
```

**6. Create the Database**

Create a new MySQL database matching the `DB_DATABASE` value you set in `.env`:
```sql
CREATE DATABASE deped_zamboanga_lms;
```

**7. Run Database Migrations**
```bash
php artisan migrate
```

**8. Seed the Database**

Populate the database with default roles and an initial administrator account:
```bash
php artisan db:seed
```

**9. Create Storage Symlink**

This allows uploaded files (modules, PDFs, etc.) to be publicly accessible:
```bash
php artisan storage:link
```

**10. Build Frontend Assets**

For development (with hot reloading):
```bash
npm run dev
```

For production:
```bash
npm run build
```

**11. Start the Local Development Server**
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

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

## 🚢 Production Deployment

When deploying to a production server, run the following commands after pulling the latest changes:

```bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
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