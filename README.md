# Doctor Appointment System

A modern web application for booking doctor appointments built with PHP, MySQL, and Tailwind CSS.

## 🚀 Features

- **User Authentication**: Sign up, sign in, and sign out functionality
- **Doctor Management**: View available doctors and their specializations
- **Appointment Booking**: Book appointments with doctors
- **Appointment History**: View your appointment history
- **Responsive Design**: Mobile-friendly interface
- **Secure API**: RESTful API with proper authentication

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Tailwind CSS
- **Server**: Apache (XAMPP)

## 📋 Prerequisites

- XAMPP (Apache + MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

## 🚀 Installation

### 1. Clone or Download the Project

Place the project in your XAMPP htdocs directory:
```
/Applications/XAMPP/xamppfiles/htdocs/doctor-appoinment/
```

### 2. Start XAMPP Services

1. Open XAMPP Control Panel
2. Start Apache server
3. Start MySQL server

### 3. Set Up Database

#### Option A: Using phpMyAdmin (Recommended)
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Create a new database named `doctor`
3. Import the `database_setup.sql` file

#### Option B: Using MySQL Command Line
```bash
mysql -u root -p < database_setup.sql
```

### 4. Configure Database Connection

The database connection is already configured in `Backend/includes/db.php` with default XAMPP settings:
- Host: localhost
- Username: root
- Password: (empty)
- Database: doctor

If you need to change these settings, edit the file accordingly.

## 🌐 Access the Application

### Main Application
```
http://localhost/doctor-appoinment/Frontend/pages/index.html
```

### Individual Pages
- **Home Page**: `http://localhost/doctor-appoinment/Frontend/pages/index.html`
- **Sign In**: `http://localhost/doctor-appoinment/Frontend/pages/signin.html`
- **Sign Up**: `http://localhost/doctor-appoinment/Frontend/pages/signup.html`

## 📁 Project Structure

```
doctor-appoinment/
├── Backend/
│   ├── api/
│   │   ├── auth.php          # Authentication API
│   │   ├── products.php      # Doctors & Appointments API
│   │   └── users.php         # Users API
│   ├── includes/
│   │   ├── db.php            # Database connection
│   │   └── functions.php     # Helper functions
│   └── index.php             # Backend entry point
├── Frontend/
│   ├── pages/
│   │   ├── index.html        # Home page
│   │   ├── signin.html       # Sign in page
│   │   └── signup.html       # Sign up page
│   ├── public/
│   │   ├── css/
│   │   │   └── styles.css    # Custom styles
│   │   └── js/
│   │       └── script.js     # Frontend JavaScript
│   └── tailwind/
│       └── input.css         # Tailwind configuration
├── database_setup.sql        # Database schema
└── README.md                 # This file
```

## 🔧 API Endpoints

### Authentication
- `POST /Backend/api/auth.php?action=signup` - User registration
- `POST /Backend/api/auth.php?action=signin` - User login
- `POST /Backend/api/auth.php?action=signout` - User logout
- `GET /Backend/api/auth.php?action=me` - Get current user

### Doctors & Appointments
- `GET /Backend/api/products.php?action=doctors` - Get all doctors
- `GET /Backend/api/products.php?action=appointments` - Get user appointments
- `POST /Backend/api/products.php?action=book` - Book appointment

### Users
- `GET /Backend/api/users.php` - Get all users (authenticated only)

## 🎯 Usage

1. **Sign Up**: Create a new account
2. **Sign In**: Log in to your account
3. **Book Appointment**: Select a doctor and book an appointment
4. **View History**: Check your appointment history

## 🔒 Security Features

- Password hashing using bcrypt
- Session-based authentication
- SQL injection prevention with prepared statements
- Input validation and sanitization
- CSRF protection (basic)

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `Backend/includes/db.php`
   - Verify database `doctor` exists

2. **Page Not Found**
   - Ensure Apache is running in XAMPP
   - Check file permissions
   - Verify correct URL path

3. **Authentication Issues**
   - Clear browser cookies
   - Check session configuration
   - Verify database tables exist

### Error Logs

Check XAMPP error logs:
- Apache: `/Applications/XAMPP/xamppfiles/logs/error_log`
- MySQL: `/Applications/XAMPP/xamppfiles/var/mysql/`

## 🤝 Contributing

1. Fork the project
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 👨‍💻 Author

Doctor Appointment System - Built with ❤️ for healthcare management

## 📞 Support

For support and questions, please create an issue in the project repository.
