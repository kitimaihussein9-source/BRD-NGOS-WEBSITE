# BRD portal on XAMPP

## 1. Copy the project

Copy this folder to:

```text
C:\xampp\htdocs\BRD-NGOS-WEBSITE-main
```

## 2. Start services

Open XAMPP Control Panel and start:

- Apache
- MySQL

## 3. Open the site

```text
http://localhost/BRD-NGOS-WEBSITE-main/index.php
```

The first PHP request creates the `brd_ngos` database and its tables automatically. Existing `accounts.json`, `portal_content.json`, and `client_requests.json` data is imported once if those files are present.

## 4. Default database settings

The application uses the standard XAMPP settings:

```text
Host: 127.0.0.1
Port: 3306
Database: brd_ngos
Username: root
Password: empty
```

If your MySQL password is not empty, set these environment variables before starting Apache:

```text
BRD_DB_HOST=127.0.0.1
BRD_DB_PORT=3306
BRD_DB_NAME=brd_ngos
BRD_DB_USER=root
BRD_DB_PASSWORD=your-password
```

You can also import [database.sql](database.sql) through phpMyAdmin, but it is optional because `db.php` creates the schema automatically.

## 5. Test the roles

Admin login:

```text
Username: admin
Password: admin123
```

Clients and assistants register through the portal. Successful login routes clients to `client.php` and assistants/admins to `dashboard.php`.

## Required PHP extensions

Enable these in `C:\xampp\php\php.ini` if needed, then restart Apache:

```text
extension=pdo_mysql
extension=mysqli
```

For uploads up to 50 MB, update these values in `C:\xampp\php\php.ini`, then restart Apache:

```ini
upload_max_filesize = 50M
post_max_size = 55M
```
