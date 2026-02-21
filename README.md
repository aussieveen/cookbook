# Cookbook

A personal recipe management application built with Symfony 7 and PHP 8. It allows you to store and organise recipes with ingredients, steps, components, and images. An EasyAdmin-powered admin interface makes it easy to manage your cookbook content.

## Stack

- **PHP 8.5** with Symfony 7
- **MySQL** for data storage
- **Nginx** as the web server
- **Webpack Encore** for frontend assets

## Getting started

### Prerequisites

- Docker and Docker Compose

### Setup

1. Copy the example environment file and set the port the app will be available on:

   ```bash
   cp .env.example .env
   ```

3. Build and start the containers:

   ```bash
   docker compose up -d --build
   ```

   On first run the entrypoint will wait for the database to be ready and then automatically run migrations.

4. Open [http://localhost:10000](http://localhost:10000) in your browser.

### Admin interface

The EasyAdmin interface is available at `/admin`.
