# BRD production deployment

## Important platform note

This project is a PHP application with PHP sessions, file uploads, and MySQL. Vercel does not provide a native PHP runtime for these `.php` pages, so deploying this repository directly to Vercel will not run the portal correctly. A Vercel deployment could serve the static landing page only; authentication, admissions, uploads, reports, and the database would not work.

Use a PHP-compatible host such as shared cPanel hosting, Render with a PHP web service, Railway, or a VPS. The database can be hosted by the same provider or by a managed MySQL provider.

## Production checklist

1. Create a MySQL database and application user.
2. Import `database.sql` into the database.
3. Upload the project files to the PHP host.
4. Set these environment variables in the hosting dashboard:

   - `BRD_DB_HOST`
   - `BRD_DB_PORT`
   - `BRD_DB_NAME`
   - `BRD_DB_USER`
   - `BRD_DB_PASSWORD`

5. Ensure PHP has PDO MySQL, Fileinfo, and session support enabled.
6. Make `uploads/` writable by the web process. Keep executable PHP files disabled inside uploads.
7. Point the domain document root at the project directory.
8. Enable HTTPS before using authentication or admissions.
9. Replace the default admin password immediately after the first login.
10. Test login, registration, profile photo upload, admissions, request confirmation, reports, and calendar export on the hosted URL.

## Database setup example

Create a restricted database user instead of using the local XAMPP `root` account:

```sql
CREATE DATABASE brd_ngos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'brd_app'@'%' IDENTIFIED BY 'use-a-long-random-password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX ON brd_ngos.* TO 'brd_app'@'%';
FLUSH PRIVILEGES;
```

Then import the schema and configure the matching environment variables. Never commit real passwords or production database credentials.

## Vercel architecture option

If Vercel is required for the public frontend, split the system into two deployments:

- Vercel: a separately built static or JavaScript frontend
- PHP host: this repository's authentication, admin, admissions, uploads, and API/database layer

The frontend would need to call an HTTPS API on the PHP host. Do not place database credentials in Vercel frontend code.
