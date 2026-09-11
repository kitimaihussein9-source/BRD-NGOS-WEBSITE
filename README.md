# BRD Research Consulting Centre

BRD Research Consulting Centre is a PHP/MySQL portal for research consulting, training programmes, client requests, announcements, and internal operations.

## Features

- Public BRD landing page served from `index.php`
- Training, consultancy, and announcement catalogue pages
- Admin, assistant, and client portal roles
- Client applications, consultancy requests, feedback, and request tracking
- Admin publishing tools for text, images, and videos
- MySQL database setup and legacy JSON data migration

## Requirements

- XAMPP with Apache and MySQL
- PHP with `pdo_mysql` enabled
- A modern web browser

## Local Setup

1. Copy this folder into `C:\xampp\htdocs\brd`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open `http://localhost/brd/index.php`.

The application creates the `brd_ngos` database and required tables automatically on the first PHP request. Detailed XAMPP settings and optional database import instructions are in [XAMPP_SETUP.md](XAMPP_SETUP.md).

## Portal Access

The built-in administrator account is:

```text
Username: admin
Password: admin123
```

Clients and assistants can register through the portal. Change the administrator authentication before deploying this application publicly.

## Database Configuration

The default XAMPP connection uses host `127.0.0.1`, port `3306`, database `brd_ngos`, username `root`, and an empty password. Override these values with the following environment variables when needed:

```text
BRD_DB_HOST
BRD_DB_PORT
BRD_DB_NAME
BRD_DB_USER
BRD_DB_PASSWORD
```

## Project Structure

- `index.php` - canonical public entry point
- `auth.php` - login and registration API
- `catalog.php` - public content catalogue
- `client.php` - client workspace
- `dashboard.php` - role-based dashboard
- `manage.php` - admin and assistant publishing workspace
- `db.php` - database connection, schema creation, and migration logic
- `public_content.php` - public content feed API
- `styles.css` and `script.js` - shared frontend styles and behavior
- `uploads/` - uploaded media storage