# Reborn App Backend

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Cloudinary](https://img.shields.io/badge/Cloudinary-3448C5?style=for-the-badge&logo=cloudinary&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Auth0](https://img.shields.io/badge/Auth0-EB5424?style=for-the-badge&logo=auth0&logoColor=white)

## Overview

Reborn App Backend is the server-side application for a marketplace that gives motorist gear like jackets, helmets, and gloves a second chance. This platform allows users to buy, sell, and exchange motorist equipment, promoting sustainability and safety.

## Technologies Used

- **Laravel 11**: A robust PHP framework for building scalable web applications.
- **Cloudinary**: A cloud-based image and video management service.
- **MySQL**: A reliable and powerful relational database management system.
- **Auth0**: A flexible, drop-in solution to add authentication and authorization services to your applications.

## Features

- User Authentication and Authorization
  - Implemented with Auth0 login
  - Backend decodes JWT bearer tokens sent by the frontend where Auth0 login occurs
- Product Listings
- Product searches by name and/or filters
- Image Upload and Management
- See seller contact data

## Requirements

- PHP 8.2+
- Composer
- MySQL
- Cloudinary Account
- Auth0 Account

## Installation

### Clone the Repository

```bash
git clone https://github.com/moramaan/reborn-app-backend.git
cd reborn-app-backend
```

### Install Dependencies

```bash
composer install
```

### MySQL Installation (if not already installed)

#### On Ubuntu

```bash
sudo apt update
sudo apt install mysql-server
sudo mysql_secure_installation
```

#### On macOS

You can use Homebrew to install MySQL:

```bash
brew update
brew install mysql
brew services start mysql
```

#### On Windows

You can download the MySQL installer from the [official MySQL website](https://dev.mysql.com/downloads/installer/) and follow the installation instructions.

### Environment Setup

Copy the `.env.example` to `.env` and configure your environment variables:

```bash
cp .env.example .env
```

Set your database, Cloudinary, and Auth0 configurations in the `.env` file.

### Generate Application Key

```bash
php artisan key:generate
```

### Run Migrations

```bash
php artisan migrate
```

### Serve the Application

```bash
php artisan serve
```

The backend should now be running on `http://127.0.0.1:8000`.

## Contributing

Feel free to submit issues or pull requests. For major changes, please open an issue first to discuss what you would like to change.

## License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).