# Login and Registration System

A simple PHP project with login, registration, form validation, and logout.

## Run with XAMPP

1. Put this folder inside C:/xampp/htdocs/php.
2. Start Apache in XAMPP.
3. Open http://localhost/php/ in your browser.

The SQLite database is created automatically. No MySQL setup is needed.

## Files

- index.php: Login page.
- register.php: Registration page.
- welcome.php: Page shown after login.
- logout.php: Signs the user out.
- app.php: Shared validation, sessions, and database functions.
- style.css: Page design.
- script.js: Show and hide passwords.
- storage/.htaccess: Protects the account database.
- .htaccess: Protects internal files and disables folder browsing.
- .gitignore: Keeps the local database out of GitHub.

Passwords need 8–72 characters, an uppercase letter, a lowercase letter, and a number. Confirmation must match. Unicode characters count by character, not by their storage size.

Accounts are saved in storage/users.sqlite. Passwords are stored as hashes. The database is ignored by Git, so your local accounts are not uploaded.

[Figma design](https://www.figma.com/design/j78toOzwliHPX5Ouvxkl98?node-id=4-179)
