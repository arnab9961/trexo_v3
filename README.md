# Tourism Management System

A comprehensive Tourism Management System built using HTML, CSS, PHP, and MySQL for university projects. This system allows users to browse destinations, book travel packages, manage bookings, and more.

## Features

- **User Management**: Registration, login, profile management
- **Destination Management**: Browse and search for travel destinations
- **Package Management**: View and book travel packages
- **Booking System**: Book destinations or packages, view booking history
- **Review System**: Users can leave reviews for destinations and packages
- **Admin Dashboard**: Comprehensive admin panel to manage all aspects of the system
- **Responsive Design**: Works on desktop and mobile devices

## Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache/Nginx)
- XAMPP (recommended for easy setup)

## Installation

1. **Set up XAMPP**:
   - Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Start Apache and MySQL services from the XAMPP Control Panel

2. **Clone/Download the Project**:
   - Place the `tourism_management_system` folder in the `htdocs` directory of your XAMPP installation
   - Typically located at `C:\xampp\htdocs\` on Windows or `/Applications/XAMPP/htdocs/` on macOS

3. **Create the Database**:
   - Open your web browser and navigate to `http://localhost/phpmyadmin`
   - Create a new database named `tourism_db`
   - Import the `tourism_db.sql` file from the project folder

4. **Configure Database Connection**:
   - If needed, modify the database connection settings in `includes/config.php`
   - Default settings:
     - Server: localhost
     - Username: root
     - Password: (empty)
     - Database: tourism_db

5. **Access the Website**:
   - Open your web browser and navigate to `http://localhost/tourism_management_system`
   - The website should now be up and running

## Directory Structure

```
tourism_management_system/
├── admin/                  # Admin dashboard files
├── css/                    # CSS stylesheets
├── images/                 # Image files
├── includes/               # PHP include files
│   ├── config.php          # Database configuration
│   ├── header.php          # Header template
│   └── footer.php          # Footer template
├── js/                     # JavaScript files
├── about.php               # About page
├── contact.php             # Contact page
├── destination_details.php # Destination details page
├── destinations.php        # Destinations listing page
├── index.php               # Homepage
├── login.php               # Login page
├── logout.php              # Logout script
├── my_bookings.php         # User bookings page
├── package_details.php     # Package details page
├── packages.php            # Packages listing page
├── profile.php             # User profile page
├── register.php            # Registration page
├── tourism_db.sql          # Database SQL file
└── README.md               # This file
```

## Admin Access

- **URL**: `http://localhost/tourism_management_system/admin`
- **Username**: admin
- **Password**: admin123

## User Roles

1. **Admin**:
   - Manage destinations and packages
   - Manage user accounts
   - Process bookings
   - Respond to inquiries
   - View reports and statistics

2. **Customer**:
   - Browse destinations and packages
   - Make bookings
   - Write reviews
   - View booking history
   - Update profile information

## Notes for Development

- The system uses Bootstrap 5 for responsive design
- Font Awesome is used for icons
- The database includes sample data for testing
- Images should be placed in the `images` directory

## License

This project is created for educational purposes as a university project. Feel free to modify and use it for your own educational purposes.

## Credits

Developed as a university project for demonstrating web development skills using HTML, CSS, PHP, and MySQL. 