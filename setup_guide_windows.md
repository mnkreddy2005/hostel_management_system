# 🚀 Windows Setup Guide: Hostel Management System (Remote DB)

This guide will walk you through setting up the **Hostel Management System** on your Windows machine while connecting to your **remote Alwaysdata MySQL server**.

---

## 1. Prerequisites
Before you begin, ensure you have the following installed:
- **XAMPP**: [Download here](https://www.apachefriends.org/index.html) (We only need the **Apache** module).
- **Internet Connection**: Required to connect to the Alwaysdata remote database.

---

## 2. Step-by-Step Installation

### Step A: Move Project to XAMPP
1. Copy your project folder (`Hostel Management System`).
2. Navigate to your XAMPP installation directory (usually `C:\xampp\htdocs`).
3. Paste the folder there. 
   > [!TIP]
   > Rename the folder to something simple like `hms` (e.g., `C:\xampp\htdocs\hms`).

### Step B: Start Apache
1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. **Note:** You do **NOT** need to start MySQL locally since we are using the remote server.

### Step C: Database Setup (Already Done! ✅)
Since we are using the Alwaysdata remote server, the database is already hosted online. You don't need to import any SQL files locally unless you want a backup.

---

## 3. Configuration

Your **`db_connect.php`** is already configured to connect to Alwaysdata. Ensure it looks like this:

```php
<?php
// db_connect.php

/* Alwaysdata Production Settings */
$host = 'mysql-nithinmeruva.alwaysdata.net'; 
$user = 'nithinmeruva';               
$pass = 'meruva2005';               
$dbname = 'nithinmeruva_hms';               

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

---

## 4. Running the Project
1. Open your browser.
2. Navigate to: `http://localhost/hms/index.php` (replace `hms` if your folder name is different).
3. **Login Credentials:**
   - **Username:** `admin`
   - **Password:** `admin123`

---

## 🔧 Pro Tips for Remote Connection

### 1. Connection Speed
Because the database is remote, you might notice a slight delay when loading pages. This is normal for remote connections.

### 2. Firewall Issues
If you get a "Connection Timed Out" error, ensure your Windows Firewall is not blocking outgoing connections on port **3306** (the MySQL port).

### 3. Terminal Option
If you prefer running without XAMPP:
1. Open CMD in your project folder.
2. Run: `php -S localhost:8000`
3. Visit: `http://localhost:8000`

---

> [!IMPORTANT]
> Since the database is remote, any changes you make (adding students, rooms, etc.) will be saved directly to the Alwaysdata server.
