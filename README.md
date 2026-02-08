# 🛒 Algerian Cash-on-Delivery E-Commerce Platform

A full-featured **e-commerce platform** tailored for the Algerian
market, based on the **Cash on Delivery (COD)** payment model.

The platform provides a modern shopping experience for customers and a
secure, professional **admin dashboard** for managing products, delivery
costs, and discounts.

![Home Preview](assets/UI.png)\
![product Preview](assets/product.png)\

![Admin Panel](assets/admin.png)\

------------------------------------------------------------------------

## 📌 Project Overview

This project is a **Full Stack Web Application** designed to deliver:

-   A smooth shopping experience for customers.
-   A powerful admin management system.
-   Automatic delivery cost calculation by wilaya (region).
-   Time-based product discounts.
-   Secure authentication and session management.

------------------------------------------------------------------------

## 🚀 Main Features

### 🛍️ Client Side (Storefront)

-   Product listing with categories.
-   Dynamic shopping cart.
-   Automatic total price calculation.
-   Cash on Delivery checkout.
-   Smart notifications.
-   Full Arabic support (RTL layout).

### 🧑‍💼 Admin Dashboard

-   Product management (Add / Edit / Delete).
-   Time-based discounts management.
-   Image upload system.
-   Delivery cost management per wilaya.
-   Sales statistics.
-   Auto logout after inactivity.

### 🔐 Security

-   Session hardening.
-   CSRF protection.
-   Auto logout.
-   Session ID regeneration.
-   Prepared statements (PDO).
-   Cache prevention.
-   Security headers (XSS & Clickjacking protection).

------------------------------------------------------------------------

## 🧰 Technologies Used

### Frontend

-   HTML5
-   CSS3 (Modern UI)
-   JavaScript (ES6+)
-   Bootstrap 5
-   Font Awesome
-   Flatpickr

### Backend

-   PHP 8+
-   MySQL
-   PDO

### Security

-   OWASP Best Practices
-   CSRF Tokens
-   Secure Sessions

------------------------------------------------------------------------

Algerian Cash-on-Delivery E-Commerce Platform/
│
├── CSS/
│ ├── Index.css # Styles for the store interface
│ ├── Login.css # Styles for login page
│ └── Admin.css # Styles for admin panel
│
├── db/
│ ├── config.php # Database configuration
│ └── assets/ # Images for documentation and UI
│ ├── UI.jpg
│ ├── admin.jpg
│ └── product.jpg
│
├── img/
│ ├── boutique.ico
│ ├── gestion.ico
│ └── login.ico
│
├── JS/
│ ├── admin.js # Admin panel logic
│ ├── Login.js # Login page logic
│ └── index.js # Store interface logic
│
├── delivery.php # Delivery management page
├── espace_admin.php # Admin dashboard page
├── index.php # Main store interface
├── get.php # API endpoint for data fetching
├── Login.php # Login page
├── Lougout.php # Logout script
├── database/
│ └── store.sql # Database dump
│
├── License # Project license
└── README.md # Project documentation


------------------------------------------------------------------------

## ⚙️ Local Installation

### Requirements

-   XAMPP / WAMP / Laragon
-   PHP 8+
-   MySQL
-   Git

### Clone Project

``` bash

git clone https://github.com/HoussemBouagal/Algerian-Cash-on-Delivery-E-Commerce-Platform.git
cd Algerian-Cash-on-Delivery-E-Commerce-Platform
```

Move project to:

    htdocs/ or www/

### Database Configuration

Edit file:

``` php
db/config.php
```

``` php
define('DB_HOST', 'localhost');
define('DB_NAME', 'store');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Create database:

``` sql
CREATE DATABASE ecommerce;
```

------------------------------------------------------------------------

## 🔑 Default Admin Credentials

Username: admin\
Password: admin

> Change credentials in production.

------------------------------------------------------------------------

## 🏆 Project Level

Skill Level: **Mid-Level to Advanced Full Stack Developer**\

## 📄 License

This project is licensed under the **MIT License**.

Read the full license in the [LICENSE](LICENSE) file.

---

## 👤 Author

**Houssem Bouagal**  
📧 Email: [mouhamedhoussem813@gmail.com](mailto:mouhamedhoussem813@gmail.com)  
🔗 LinkedIn: [Houssem Bouagal](https://www.linkedin.com/in/houssem-eddine-bouagal-98025a297)  
