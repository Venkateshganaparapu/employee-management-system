# 💊 MediCore — Pharmacy Management System

A full-featured, web-based **Pharmacy Management System** built with **PHP**, **MySQL**, and **vanilla JavaScript**. Designed to streamline day-to-day pharmacy operations including inventory management, billing, customer tracking, supplier management, and sales analytics.

---

## 🖥️ Screenshots

> Login page and dashboard of MediCore Pharmacy Management System.

---

## ✨ Features

| Module | Description |
|---|---|
| 🔐 **Authentication** | Secure login with role-based access (Admin / Staff) using bcrypt password hashing |
| 📊 **Dashboard** | Real-time stats — total medicines, revenue, customers, and interactive sales chart by year |
| 💊 **Medicines** | Full CRUD — add, edit, delete, and search medicines with stock & expiry tracking |
| 🧾 **Billing / POS** | Multi-item invoicing with auto-stock deduction, discounts, and printable receipts |
| 📜 **Sales History** | View all past transactions with line-item breakdown and export to PDF/CSV |
| 👥 **Customers** | Manage customer profiles with purchase history notes |
| 🏭 **Suppliers** | Manage supplier contacts and link them to medicines |
| 🔔 **Alerts** | Automatic alerts for low-stock and near/past expiry medicines |
| 📤 **Reports** | Export sales data as PDF or CSV |

---

## 🛠️ Tech Stack

- **Backend:** PHP 8+ (procedural)
- **Database:** MySQL 5.7+ / MariaDB
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Server:** Apache (via XAMPP)
- **Icons:** Font Awesome 6
- **Charts:** Chart.js

---

## 📁 Project Structure

```
pharmacy_system/
├── index.html              # Landing/redirect page
├── login.php               # Login page
├── logout.php              # Session destroy & redirect
├── dashboard.php           # Main analytics dashboard
├── medicines.php           # Medicine inventory management
├── billing.php             # POS / Invoice creation
├── history.php             # Sales history & transaction viewer
├── customers.php           # Customer management
├── suppliers.php           # Supplier management
├── alerts.php              # Stock & expiry alerts
├── action_discount.php     # Discount processing handler
├── action_dispose.php      # Medicine disposal handler
├── seed_data.php           # Demo data seeder
│
├── api/                    # AJAX / REST-style API endpoints
│   ├── billing_api.php     # Billing operations
│   ├── customer_crud.php   # Customer CRUD
│   ├── supplier_crud.php   # Supplier CRUD
│   ├── medicine_crud.php   # Medicine CRUD
│   ├── history_api.php     # Sales history queries
│   ├── sales_by_year.php   # Chart data API
│   └── export_report.php   # PDF/CSV export
│
├── includes/               # Shared PHP components
│   ├── db.php              # PDO database connection
│   ├── header.php          # HTML head & nav
│   ├── sidebar.php         # Navigation sidebar
│   └── footer.php          # Footer scripts
│
├── css/
│   └── style.css           # Global stylesheet
│
├── js/                     # JavaScript files
│
└── sql/
    ├── schema.sql          # Full DB schema + seed data
    ├── fix_pass.sql        # Password fix utility
    └── fix_pass_v2.sql     # Password fix utility v2
```

---

## ⚙️ Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL) — v7.4 or higher
- PHP 8.0+
- A modern web browser

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/YOUR_USERNAME/pharmacy_system.git
```

**2. Move project to XAMPP's htdocs folder**
```bash
# Windows
move pharmacy_system C:\xampp\htdocs\pharmacy_system

# Linux/Mac
mv pharmacy_system /opt/lampp/htdocs/pharmacy_system
```

**3. Start XAMPP**
- Open **XAMPP Control Panel**
- Start **Apache** and **MySQL**

**4. Create the database**
- Open your browser and go to: `http://localhost/phpmyadmin`
- Click **New** → create a database named `pharmacy_db`
- Select `pharmacy_db` → click **Import**
- Choose the file: `sql/schema.sql` → click **Go**

**5. Configure the database connection** *(if needed)*

Edit `includes/db.php`:
```php
$host = 'localhost';
$dbname = 'pharmacy_db';
$username = 'root';   // your MySQL username
$password = '';       // your MySQL password (empty by default in XAMPP)
```

**6. Open the application**
```
http://localhost/pharmacy_system/login.php
```

---

## 🔑 Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `password` |
| Staff | `staff1` | `password` |

> ⚠️ **Change these credentials immediately after first login in a production environment.**

---

## 🌱 Demo Seed Data

To populate the system with realistic sample data (medicines, customers, suppliers, billing history):

1. Make sure the database is set up (Step 4 above)
2. Visit: `http://localhost/pharmacy_system/seed_data.php`
3. The script will insert demo records automatically

---

## 📊 Dashboard Overview

The dashboard provides:
- **KPI Cards** — Total medicines, today's revenue, total customers, active suppliers
- **Sales Chart** — Monthly revenue visualization by year (powered by Chart.js)
- **Quick Actions** — Jump directly to billing, stock alerts, or add new medicine

---

## 🔒 Security Features

- Passwords stored as **bcrypt hashes** (PHP `password_hash()`)
- **Session-based authentication** — all pages check for valid session
- **PDO prepared statements** — protection against SQL injection
- Role-based page access control (Admin vs Staff)

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m 'Add some feature'`
4. Push to the branch: `git push origin feature/your-feature`
5. Open a **Pull Request**

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

## 👤 Author

**Venky**
- GitHub: [@YOUR_USERNAME](https://github.com/YOUR_USERNAME)

---

> Built with ❤️ using PHP, MySQL & Vanilla JS
