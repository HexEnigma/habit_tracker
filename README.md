# HabitTracker - Build Better Habits, One Day at a Time

![HabitTracker Logo](screenshots/logo.png)

A comprehensive, feature-rich habit tracking web application designed to help users build consistency, track progress, and achieve their personal goals through gamification and social accountability.

## 🚀 Live Demo

_[Live Demo Link](#) ← Live URL coming soon...

## 📸 Screenshots

| Dashboard                               | Habits Management                 | Analytics                               |
| --------------------------------------- | --------------------------------- | --------------------------------------- |
| ![Dashboard](screenshots/dashboard.png) | ![Habits](screenshots/habits.png) | ![Analytics](screenshots/analytics.png) |

| Groups & Social                   | Achievements                                  | Mobile View                            |
| --------------------------------- | --------------------------------------------- | -------------------------------------- |
| ![Groups](screenshots/groups.png) | ![Achievements](screenshots/achievements.png) | ![Mobile](screenshots/mobile-view.png) |



## ✨ Features

### 🎯 Core Functionality

- **Habit Creation & Management** - Create custom habits with detailed parameters
- **Daily Progress Tracking** - Mark habits as complete with one click
- **Visual Calendar View** - Monthly progress overview with color-coded completion status
- **Streak Tracking** - Maintain and build consecutive day streaks
- **Smart Reminders** - Customizable notification system

### 📊 Analytics & Insights

- **Progress Statistics** - Completion rates and habit performance metrics
- **Visual Analytics** - Interactive charts and graphs using Chart.js
- **Monthly Reports** - Comprehensive progress overviews
- **Category Tracking** - Organize habits by health, productivity, learning, fitness, and more
- **Export Capabilities** - Download your habit data

### 🎮 Gamification System

- **Points & Rewards** - Earn points for completed habits and streaks
- **Achievement System** - Unlock badges for milestones and accomplishments
- **Daily Login Bonuses** - Reward for consistent engagement
- **Streak Milestones** - Special rewards for 7, 14, and 30-day streaks
- **Level Progression** - Visual progress indicators

### 👥 Social Features

- **Group Creation** - Form accountability groups with friends
- **Group Chat** - Real-time messaging within groups
- **Progress Sharing** - Share selected habits with group members
- **Member Management** - Admin controls for group management
- **Public/Private Groups** - Flexible privacy settings

### 🛡️ User Experience

- **Responsive Design** - Fully mobile-friendly interface
- **Intuitive UI/UX** - Clean, modern design with Tailwind CSS
- **CSRF Protection** - Enhanced security measures
- **Data Privacy** - Control over public/private habit visibility
- **Session Management** - Secure login and authentication

### ⚙️ Additional Features

- **Custom Habit Frequencies** - Daily, weekly, or custom day schedules
- **Category System** - Organize habits into meaningful categories
- **Search & Filter** - Easy navigation through habits
- **Data Export** - Download your habit history
- **Newsletter System** - Stay updated with new features

## 🛠️ Tech Stack

### Frontend

- **HTML5** - Semantic markup structure
- **Tailwind CSS** - Utility-first CSS framework
- **JavaScript (ES6+)** - Vanilla JavaScript for interactions
- **Font Awesome** - Comprehensive icon library
- **Animate.css** - CSS animations

### Backend

- **PHP 7.4+** - Server-side scripting language
- **MySQL** - Relational database management
- **PDO** - Secure database interactions

### Libraries & Tools

- **Chart.js** - Data visualization and analytics
- **Flatpickr** - Modern date picker
- **XAMPP** - Local development environment

## 📦 Installation & Setup

### Prerequisites

- XAMPP (Apache, MySQL, PHP)
- Modern web browser
- Git (optional)

### Step-by-Step Installation

1. **Clone or Download the Repository**

   ```bash
   git clone https://github.com/your-username/habittracker.git
   ```

   Or download the ZIP file and extract to your `htdocs` folder

2. **Set Up Database**

   - Open PHPMyAdmin (usually at http://localhost/phpmyadmin)
   - Create a new database named `habit_tracker`
   - Import the SQL file from the project's `database/` folder
   - Or run the SQL schema provided in the installation docs

3. **Configure Environment**

   - The `config.php` file is pre-configured for XAMPP
   - Verify database credentials if using different environment:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'habit_tracker');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Launch Application**

   - Start Apache and MySQL in XAMPP
   - Navigate to `http://localhost/habit_tracker/`
   - Register a new account or use demo credentials

5. **Optional: Configure Email**
   - Set up SMTP settings in `config.php` for email notifications
   - Configure reminder system in user settings

## 🚀 Usage Guide

### Getting Started

1. **Create Account** - Register with email and password
2. **Add First Habit** - Use the "Add Habit" button to create your initial habit
3. **Set Frequency** - Choose daily, weekly, or custom schedule
4. **Track Daily** - Mark habits as complete each day
5. **Build Streaks** - Maintain consistency to build impressive streaks

### Advanced Features

- **Join Groups** - Find accountability partners in the Groups section
- **Earn Achievements** - Complete milestones to unlock badges
- **Analyze Progress** - Use Analytics to review your performance
- **Set Reminders** - Configure notifications in Settings
- **Customize Categories** - Organize habits by your preferred categories

## 📁 Project Structure

```
habit_tracker/
├── 📄 index.php              # Main entry point
├── 📄 home.php               # Landing page
├── 📄 dashboard.php          # User dashboard
├── 📄 habits.php             # Habit management
├── 📄 auth.php               # Authentication handler
├── 📄 config.php             # Configuration & database
├── 📄 analytics.php          # Data visualization
├── 📄 achievements.php       # Gamification system
├── 📄 group.php              # Group management
├── 📄 monthly-view.php       # Calendar functionality
├── 📄 header.php             # Site header
├── 📄 footer.php             # Site footer
├── 📄 All the remainig file in the repository.
├── 📁 css/                   # Stylesheets
│   ├── style.css
│   └── login-style.css
├── 📁 js/                    # JavaScript files
│   └── script.js
├── 📁 screenshots/           # Project screenshots ← Add your images here
└── 📁 database/              # Database schema
    └── habittracker.sql
```

## 🐛 Troubleshooting

### Common Issues

1. **Registration Not Working**

   - Check database connection in `config.php`
   - Verify `users` table exists in database
   - Check PHP error logs for specific issues

2. **Page Not Loading**

   - Ensure Apache is running in XAMPP
   - Verify files are in `htdocs/habit_tracker/` folder

3. **Database Errors**

   - Import the SQL schema again
   - Check MySQL is running in XAMPP

4. **CSS/JS Not Loading**
   - Check file permissions
   - Verify file paths in HTML headers

### Getting Help

- Check the `FAQ.php` page in the application
- Review PHP error logs in `xampp/php/logs/`
- Ensure all prerequisite services are running

## 🤝 Contributing

This is currently a personal university project. While I'm not accepting external contributions at this time, I welcome feedback and suggestions!

## 📄 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

The MIT License is a permissive free software license that allows reuse within proprietary software provided all copies of the licensed software include a copy of the MIT License terms and the copyright notice. It's a great choice for personal and academic projects.

## 🙋‍♂️ About the Project

### Background

HabitTracker was developed as a comprehensive course project for my university studies. The goal was to create a full-stack web application that demonstrates proficiency in modern web development technologies while solving a real-world problem.

### Development Notes

- Built with pure PHP (no frameworks) for educational purposes
- Focus on security best practices (CSRF protection, input sanitization)
- Responsive design for all device types
- Modular architecture for easy maintenance

### Future Enhancements

- Mobile app development
- API integration for third-party services
- Advanced analytics with machine learning
- Social media integration
- Multi-language support

## 📞 Contact & Support

For questions, support, or feedback about HabitTracker:

- **Email**: [Your Email]
- **GitHub Issues**: [Create an issue](../../issues)
- **University**: [Your University Name]

---

**⭐ If you find this project useful, please give it a star on GitHub!**

_HabitTracker - Building better habits, one day at a time._ 🚀
