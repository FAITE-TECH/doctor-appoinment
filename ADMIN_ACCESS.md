# 🔐 Admin Access Guide

## 🏥 **MedCare Doctor Appointment System**

### **Admin Login Credentials**
- **Email:** `admin@hospital.com`
- **Password:** `admin123`

### **How to Access Admin Panel**

#### **Option 1: Direct Admin Login**
1. Go to: `http://localhost/doctor-appoinment/Frontend/pages/admin/login.html`
2. Enter the credentials above
3. You'll be redirected to the admin dashboard

#### **Option 2: Through Main Site**
1. Go to: `http://localhost/doctor-appoinment/Frontend/pages/index.html`
2. Click "Access Admin Panel" button at the bottom
3. Sign in with admin credentials
4. You'll be redirected to the admin dashboard

#### **Option 3: After Regular Login**
1. Sign in to the main site with admin credentials
2. An "Admin Panel" button will appear in the navigation
3. Click it to access the admin dashboard

### **Admin Features Available**
- ✅ **Dashboard** - Overview statistics and recent activity
- ✅ **Doctors Management** - Add, edit, delete doctors
- ✅ **Departments Management** - Manage medical departments
- ✅ **Gallery Management** - Upload and manage images
- ✅ **Services Management** - Manage medical services
- ✅ **Events Management** - Create and manage health events
- ✅ **Appointments Overview** - View all appointments
- ✅ **User Management** - View system users

### **Security Notes**
- Admin access is restricted to users with `admin` role
- Regular users cannot access admin functions
- All admin actions require authentication
- Session-based security with automatic logout

### **Troubleshooting**
- **Can't access admin panel?** Make sure you're using the correct credentials
- **Role not recognized?** Check if the database was set up correctly
- **Session issues?** Try logging out and logging back in

### **Database Setup**
If you haven't set up the database yet:
1. Run: `http://localhost/doctor-appoinment/setup_database.php`
2. This will create the admin user automatically
3. Then use the credentials above to log in

---
**⚠️ Important:** Change the default admin password in production!
