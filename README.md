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
git clone <your-repository-url>  
cd deped-zamboanga-lms

**2. Install PHP Dependencies**
composer install

**3. Install Frontend Dependencies**
npm install



