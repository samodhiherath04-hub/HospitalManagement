# Hospital Management System (HMS)

A complete web-based Hospital Management System built with **PHP, MySQL, HTML, CSS, and JavaScript**. Implements all 10 core modules described in the software specification, with multi-role authentication and a clean minimal-mono interface.

---

## Features

| Module            | Description                                                  |
|-------------------|--------------------------------------------------------------|
| **Dashboard**     | KPI overview: patients, appointments, revenue, lab alerts    |
| **Patients**      | Registration, search, view history, edit, delete             |
| **Doctors**       | Add/edit doctors, specializations, departments, fees         |
| **Appointments**  | Book, reschedule, complete, cancel, filter by status         |
| **Medical Records (EMR)** | Diagnosis, prescriptions, treatment history        |
| **Laboratory**    | Test catalog + test requests + result entry                  |
| **Pharmacy**      | Inventory, low-stock alerts, expiry tracking                 |
| **Billing**       | Invoice generation, payments, printable invoices             |
| **Admissions**    | Inpatient management, rooms, beds, daily rates               |
| **Staff**         | Employees, attendance, leave approval workflow               |
| **Users**         | User management (admin only)                                 |
| **Departments**   | Department management                                         |
| **Reports**       | Analytics: revenue, top doctors, appointments by status      |
| **Audit Logs**    | Action tracking for security and compliance                  |

---

## Tech Stack

- **Backend**: PHP 7.4+ (PDO for database access)
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, vanilla JavaScript
- **Auth**: PHP native sessions, bcrypt password hashing, role-based access control (RBAC)
- **No external dependencies / frameworks** — pure stack

---

## Setup Instructions

### 1. Requirements
- PHP 7.4 or newer with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.3+
- A web server: Apache (XAMPP/WAMP/MAMP), Nginx, or PHP built-in server

### 2. Database Setup
Import the schema file `database/hms.sql`:

**Via MySQL CLI:**
```bash
mysql -u root -p < database/hms.sql
```

**Via phpMyAdmin:**
1. Open phpMyAdmin
2. Click **Import**
3. Choose `database/hms.sql`
4. Click **Go**

This creates database `hms_db` with all tables and seed data.

### 3. Configure Connection
Edit `config/database.php` with your MySQL credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'hms_db');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### 4. Deploy
- **XAMPP/WAMP**: Copy the `hms/` folder into `htdocs/` (or `www/`) and visit `http://localhost/hms/`
- **PHP built-in server** (for quick testing):
  ```bash
  cd hms
  php -S localhost:8000
  ```
  Then open `http://localhost:8000/`

### 5. Login
Use any of the pre-seeded accounts. **The password for all demo accounts is `admin123`.**

| Username     | Role          |
|--------------|---------------|
| `admin`      | Administrator |
| `dr.smith`   | Doctor        |
| `dr.lee`     | Doctor        |
| `nurse.jane` | Nurse         |
| `reception`  | Receptionist  |
| `lab.tech`   | Lab Staff     |
| `pharmacy`   | Pharmacist    |
| `accounts`   | Accountant    |

---

## Role-Based Access

Different roles see different sidebar items:

| Module            | Admin | Doctor | Nurse | Receptionist | Lab | Pharmacy | Accountant |
|-------------------|:-----:|:------:|:-----:|:------------:|:---:|:--------:|:----------:|
| Patients          |  Y    |  Y     |  Y    |  Y           |  Y  |  Y       |  Y         |
| Doctors           |  Y    |  Y     |  Y    |  Y           |  Y  |  Y       |  Y         |
| Appointments      |  Y    |  Y     |  Y    |  Y           |  Y  |  Y       |  Y         |
| Medical Records   |  Y    |  Y     |  Y    |  -           |  -  |  -       |  -         |
| Laboratory        |  Y    |  Y     |  Y    |  -           |  Y  |  -       |  -         |
| Pharmacy          |  Y    |  Y     |  Y    |  -           |  -  |  Y       |  -         |
| Billing           |  Y    |  -     |  -    |  Y           |  -  |  -       |  Y         |
| Admissions        |  Y    |  Y     |  Y    |  Y           |  -  |  -       |  -         |
| Staff             |  Y    |  -     |  -    |  -           |  -  |  -       |  -         |
| Users             |  Y    |  -     |  -    |  -           |  -  |  -       |  -         |
| Departments       |  Y    |  -     |  -    |  -           |  -  |  -       |  -         |
| Reports           |  Y    |  -     |  -    |  -           |  -  |  -       |  Y         |
| Audit Logs        |  Y    |  -     |  -    |  -           |  -  |  -       |  -         |

---

## Folder Structure

```
hms/
├── config/
│   └── database.php          # PDO connection config
├── includes/
│   ├── auth.php              # Login, RBAC, audit logging
│   ├── functions.php         # Helpers (nav, KPIs, icons, formatting)
│   ├── header.php            # Common HTML header + sidebar
│   └── footer.php            # Common footer + JS include
├── assets/
│   ├── css/
│   │   └── style.css         # Minimal-mono theme
│   └── js/
│       └── script.js         # Sidebar toggle, confirm dialogs, live search
├── database/
│   └── hms.sql               # Full schema + seed data
├── index.php                 # Redirects to login or dashboard
├── login.php                 # Login page
├── logout.php                # Destroys session
├── dashboard.php             # KPI dashboard
├── patients.php              # Patient list
├── patient_edit.php          # Add/edit patient
├── patient_view.php          # Patient details + history
├── patient_delete.php        # Delete patient (admin)
├── doctors.php               # Doctor list
├── doctor_edit.php           # Add/edit doctor
├── doctor_delete.php
├── appointments.php          # Appointment list
├── appointment_edit.php      # Book/edit appointment
├── appointment_action.php    # Mark complete / cancelled
├── medical_records.php       # EMR list
├── medical_record_edit.php
├── medical_record_delete.php
├── laboratory.php            # Lab requests + catalog (tabs)
├── lab_edit.php              # New lab request
├── lab_result.php            # Enter / view result
├── lab_test_edit.php         # Add/edit test catalog
├── lab_test_delete.php
├── pharmacy.php              # Inventory / low stock / expiry (tabs)
├── medicine_edit.php
├── medicine_delete.php
├── billing.php               # Bill list
├── bill_edit.php             # Generate / edit bill
├── bill_view.php             # Printable invoice
├── payment.php               # Record payment
├── admissions.php
├── admission_edit.php
├── admission_discharge.php
├── staff.php                 # Employees / attendance / leaves (tabs)
├── staff_edit.php
├── staff_delete.php
├── leave_action.php          # Approve / reject leave
├── users.php                 # User list (admin)
├── user_edit.php
├── user_delete.php
├── departments.php
├── department_edit.php
├── department_delete.php
├── reports.php               # Analytics
├── audit.php                 # Audit logs (admin)
└── README.md
```

---

## Security Notes

- Passwords are hashed with **bcrypt** via `password_hash()`.
- All database queries use **PDO prepared statements** (SQL-injection safe).
- HTML output is escaped via `htmlspecialchars()` (XSS safe).
- Role-based access control enforced on every page via `require_role()`.
- All significant actions (login, create, update, delete) are written to the **audit log**.

For production deployment:
- Serve over HTTPS
- Set `session.cookie_secure = 1` and `session.cookie_httponly = 1` in `php.ini`
- Move `config/database.php` outside the web root and update the include path
- Set up daily MySQL backups as described in the spec

---

## Future Enhancements (from spec)

- Mobile application
- Patient portal
- SMS & email notifications
- Telemedicine integration
- Insurance module
- AI-based decision support
- Biometric authentication
- Cloud deployment

---

Built as a reference implementation of the HMS Software Specification Document.
