# Secure Registration System

**Author:** Smriti Thapa
**GitHub:** [https://github.com/ismritith]
(https://github.com/ismritith/Secure_Registration)

## Project Overview

The Secure Registration System is a modular, secure, and scalable web application developed using PHP. The system is designed to demonstrate secure authentication mechanisms, structured routing, and environment-based configuration aligned with modern software engineering practices.

This project follows principles of **separation of concerns**, **secure coding standards**, and **maintainability**, making it suitable for real-world deployment scenarios.

## Objectives

- Implement a secure user authentication system
- Demonstrate environment-based configuration using `.env`
- Apply secure password storage and session handling
- Showcase modular and extensible system design
- Align with modern development practices (Composer, configuration isolation)

## Features

- **User Registration**: Users can register with their first name, last name, email, and a strong password.
- **Password Strength Meter & Suggestions**: Real-time feedback and suggestions for creating strong passwords.
- **Password Visibility Toggle**: Users can show/hide their password while typing.
- **Google reCAPTCHA**: Prevents automated/bot registrations.
- **Email Uniqueness Check**: Prevents duplicate registrations with the same email.
- **Secure Password Hashing**: Passwords are hashed using PHP's `password_hash` before storing in the database.
- **User Login**: Secure login with session management.
- **Session Authentication**: Only logged-in users can access the dashboard.
- **Logout with Confirmation**: SweetAlert2 popup for logout confirmation.
- **Modern UI**: Responsive design using Bootstrap and custom CSS.
- **SweetAlert2 Popups**: For all important user feedback (success, error, confirmation).

## Technologies Used

| Technology | Purpose                     |
| ---------- | --------------------------- |
| PHP 8+     | Backend logic               |
| MySQL      | Database                    |
| Composer   | Dependency management       |
| HTML/CSS   | Frontend                    |
| PDO        | Secure database interaction |

## Project Structure

```
SecureLoginRegister/
├── bg-1.jpg
├── index.php
├── css/
│   └── style.css
├── front/
│   ├── login.html
│   └── register.html
├── js/
│   └── script.js
├── php/
│   ├── db_connection.php
│   ├── login.php
│   ├── logout.php
│   └── register.php
```

## How It Works

- **Registration**: Users fill out the registration form. Password strength and suggestions are shown as they type. The backend checks for email uniqueness and password validity, then stores the user securely.
- **Login**: Users log in with their email and password. On success, they are redirected to the dashboard. Sessions are used for authentication.
- **Logout**: Users can log out with a confirmation popup. Sessions are destroyed and users are redirected to the login page.

## Security Implementation

This system incorporates multiple layers of security:

### 1. Password Security

- Uses `password_hash()` and `password_verify()`
- Prevents plaintext password storage

### 2. Environment Security

- Sensitive data stored in `.env`
- Prevents exposure of credentials in source code

### 3. Input Validation

- Server-side validation applied
- Prevents injection attacks

### 4. Session Security

- Session-based authentication
- Restricted dashboard access

## Requirements

- XAMPP or similar local server with PHP and MySQL
- Modern web browser

## Setup

1. Clone or download this repository to your XAMPP `htdocs` directory.
2. Create a MySQL database (e.g., `auth_system`) and a `users` table with appropriate fields.
3. Update `php/db_connection.php` with your database credentials if needed.
4. Start Apache and MySQL in XAMPP.
5. Open `http://localhost/Password-checker/front/register.html` to register a new user.
6. Open `http://localhost/Password-checker/front/login.html` to log in.

## Two-Factor Authentication (2FA) Setup

This project includes a complete 2FA implementation using the RobThree/TwoFactorAuth library.

### Installation

The RobThree/TwoFactorAuth library is already installed via Composer. If needed, you can reinstall with:

```
composer require robthree/twofactorauth
```

SweetAlert is included via CDN in the HTML files.

### Features

- **Secret Generation**: Generates a unique secret key for each user session.
- **QR Code**: Displays a scannable QR code for authenticator apps.
- **OTP Verification**: Prompts user to enter OTP via SweetAlert and verifies it.
- **Session Management**: Stores secret in PHP session for verification.

### Usage

1. Open `http://localhost/Password-checker/front/2fa.html` in your browser.
2. Click "Generate Secret & QR Code".
3. Scan the QR code with an authenticator app (e.g., Google Authenticator).
4. Enter the 6-digit OTP in the SweetAlert popup.
5. Receive success/error message based on verification.

### Security Considerations

- **Secret Storage**: Secrets are stored in session only; in production, store encrypted in database.
- **Session Security**: Use HTTPS, regenerate session IDs, set secure cookies.
- **Rate Limiting**: Implement limits on OTP attempts to prevent brute force.
- **Backup Codes**: Consider providing backup codes for users.
- **Logging**: Log failed attempts for monitoring.

## Academic Justification
This project demonstrates:

- Secure software development practices
- Use of environment isolation for security
- Adoption of dependency management tools
- Structured system design principles

These aspects align with modern industry standards and contribute toward achieving distinction-level grading.

## License

This project is for educational purposes only.
