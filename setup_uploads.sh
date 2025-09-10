#!/bin/bash

# Setup script for Doctor Appointment System uploads directory
# This script creates the necessary directory structure for file uploads

echo "Setting up uploads directory structure for Doctor Appointment System..."

# Create the main uploads directory
mkdir -p uploads

# Create subdirectories for different types of uploads
mkdir -p uploads/doctors
mkdir -p uploads/departments
mkdir -p uploads/services
mkdir -p uploads/events
mkdir -p uploads/gallery

# Set proper permissions
chmod 755 uploads
chmod 755 uploads/doctors
chmod 755 uploads/departments
chmod 755 uploads/services
chmod 755 uploads/events
chmod 755 uploads/gallery

echo "✅ Uploads directory structure created successfully!"
echo ""
echo "Directory structure:"
echo "uploads/"
echo "├── doctors/     (for doctor profile images)"
echo "├── departments/ (for department images)"
echo "├── services/    (for service images)"
echo "├── events/      (for event images)"
echo "└── gallery/     (for gallery images)"
echo ""
echo "All directories have been set with proper permissions (755)."
echo "You can now upload images through the admin panel."
