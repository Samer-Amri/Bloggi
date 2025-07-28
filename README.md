 ---

# Bloggi - Modern Blogging Platform

## Overview
Bloggi is a feature-rich blogging platform built with Laravel. It provides a robust admin interface for managing posts, categories, tags, users, comments, and site settings. Bloggi is designed for flexibility, scalability, and ease of use, making it ideal for personal blogs, multi-author sites, or content-driven communities.

## Features

### Post Management
- Create, edit, and delete blog posts
- Rich text editing and media attachments
- Slug generation and SEO-friendly URLs
- Soft deletes and post status management (draft, published)
- Full-text search on posts

### Category & Tag Management
- Organize posts into categories (supports hierarchy)
- Tagging system for flexible content organization

### Comment System
- Nested comments on posts
- Comment approval and moderation
- Notifications for new comments

### User & Role Management
- User registration and authentication
- Email verification
- Role-based access control (Admin, Author, User)
- User profile management

### Announcements & Pages
- Site-wide announcements
- Custom static pages (About, Contact, etc.)

### Contact & Notifications
- Contact form for user inquiries
- System notifications for key events

### Settings & Customization
- Manage site-wide settings from the admin panel
- Multilingual support (if enabled)

## Technical Stack
- PHP 8.2+
- Laravel 10/11
- MySQL
- Redis (for cache and queues)
- Node.js & npm (for asset compilation)

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Noxanoxa/Laravel-Projects.git
   cd Laravel-Projects/mindcms-blog
   ```
2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```
3. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. **Configure database in `.env` file:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bloggi
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. **Run migrations and seeders:**
   ```bash
   php artisan migrate --seed
   ```
6. **Start Redis server:**
   ```bash
   redis-server
   ```
7. **Run the development server:**
   ```bash
   php artisan serve
   ```
8. **Compile assets:**
   ```bash
   npm run dev
   ```

## Database Structure

### Key Tables
- `users`: Stores user accounts
- `roles`, `permissions`, `user_permissions`: Role-based access control
- `posts`: Blog posts
- `categories`: Post categories (hierarchical)
- `tags`, `posts_tags`: Tagging system
- `comments`: Comments on posts
- `announcements`: Site-wide announcements
- `pages`: Static/custom pages
- `settings`: Site settings
- `contacts`: Contact form submissions
- `post_media`: Media attachments for posts
- `notifications`: System/user notifications

## Contributing
Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## Security
If you discover any security-related issues, please email [maintainer-email] instead of using the issue tracker.

## License
This project is licensed under the SOL License. 