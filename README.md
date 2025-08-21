# HR Management Web Application  

A simple HR Management system built with **HTML, CSS, PHP, and MySQL**.  
This project provides separate dashboards for **Admins** and **Employees**, enabling attendance tracking, leave applications, requests, and notices in one platform.  

---

## 🚀 Features  

### 🔑 Authentication  
- Admin and Employee login  
- Password recovery  

### 👨‍💼 Admin Dashboard  
- View employee attendance (time, location, status, warnings)  
- See present, absent, and on-leave employees  
- View employees who missed attendance on the previous day  
- Manage employees (edit, delete, warnings)  
- Approve/Reject leave and requests  
- Post notices  

### 👩‍💻 Employee Dashboard  
- Submit attendance (in/out time + location)  
- View personal details (ID, designation, department, join date)  
- Stats: leave taken, missed attendance, pending approvals  
- Submit leave and requests  
- Access company directory & notices  

---

## 🛠 Tech Stack  
- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP  
- **Database:** MySQL  

---

## 📂 Project Structure  

/hr-management
├── index.html # Role selection page
├── login_admin.php # Admin login
├── login_employee.php # Employee login
├── dashboard_admin.php # Admin dashboard
├── dashboard_employee.php # Employee dashboard
├── admin/ # Admin feature pages
├── employee/ # Employee feature pages
├── php/ # Backend scripts (db.php, CRUD ops)
├── sql/hr_management.sql # Database schema
├── css/ # Stylesheets
├── js/ # JavaScript files
└── README.md # Documentation

---

## ⚙️ Installation  

1. Clone the repository:  
   ```bash
   git clone https://github.com/your-username/hr-management.git
   cd hr-management


Import the database:

Open MySQL or phpMyAdmin

Create a database:

CREATE DATABASE hr_management;


Import sql/hr_management.sql

Configure database connection:

Edit php/db.php with your MySQL credentials

Run the project:

Place the folder inside htdocs (for XAMPP) or www (for WAMP)

Start Apache & MySQL

Open in browser:

http://localhost/hr-management/index.html

🔑 Default Login (Sample)

Admin → Email: admin@example.com | Password: admin123

Employee → Email: employee@example.com | Password: emp123

📌 Future Improvements

Role-based permissions

Notifications & email alerts

Modern UI with frameworks (React/Vue)

API integration for mobile app

🤝 Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you’d like to change.

📜 License

This project is licensed under the MIT License.


---

👉 Would you like me to also add **badges** (like PHP version, MySQL, MIT License) and a **preview screenshot section** at the top of the README to make it look more professional for GitHub?
