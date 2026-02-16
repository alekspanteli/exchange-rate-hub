# Exchange Rate Hub

A production-grade WordPress plugin for managing and displaying currency exchange rates, with full Docker development environment.

**🚀 Ready to use in 5 minutes** • **🐳 Docker-based** • **⚡ Auto-updating rates** • **🔒 Production-ready**

---

## 📋 Table of Contents

- [Features](#features)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Usage](#usage)
- [Managing the Environment](#managing-the-environment)
- [Development](#development)
- [Troubleshooting](#troubleshooting)
- [Deployment](#deployment)

---

## Features

### Core Features ✅
- ✅ **External API Integration** - Fetches rates from exchangerate.host API
- ✅ **Periodic Automatic Updates** - WordPress Cron with configurable frequency (hourly, twice daily, daily)
- ✅ **Custom Database Tables** - Separate tables for latest rates and historical data
- ✅ **Caching Strategy** - WordPress Transients API for optimized performance
- ✅ **Complete Admin UI** - Full settings management and rate viewing
- ✅ **Frontend Display** - Shortcode with customizable columns
- ✅ **Theme Integration** - Custom page template for dedicated exchange rates page
- ✅ **Security** - Nonces, input sanitization, output escaping, capability checks
- ✅ **Error Handling** - Comprehensive error logging and graceful degradation

## Prerequisites

- Docker and Docker Compose installed on your machine
- Git (for cloning the repository)
- A modern web browser

## Quick Start

### ⚡ 5-Minute Setup Overview

1. Clone repo → `cd docker` → `docker compose up -d`
2. Open http://localhost:8000 and complete WordPress installation
3. Activate "Exchange Rate Hub" plugin
4. Configure currencies and fetch rates
5. Done! View rates at **Exchange Rates → View Rates**

**First time using Docker?** Follow the detailed guide below.

---

### Step-by-Step Setup Guide

#### 1. Clone the Repository
```bash
git clone https://github.com/alekspanteli/exchange-rate-hub.git
cd exchange-rate-hub
```

#### 2. Start the Docker Environment
```bash
cd docker
docker compose up -d
```

**Expected output:**
```
✔ Container docker-db-1         Started
✔ Container docker-wordpress-1  Started
```

**Wait 10-20 seconds** for WordPress to fully initialize.

#### 3. Complete WordPress Installation

Open your browser and navigate to: **http://localhost:8000**

You'll see the WordPress installation wizard. Fill in:
- **Site Title**: Exchange Rate Hub (or your preferred name)
- **Username**: admin (or your preferred username)
- **Password**: Choose a strong password (save it!)
- **Email**: Your email address
- **Search Engine Visibility**: Leave unchecked for development

Click **Install WordPress** and wait for completion.

#### 4. Log In to WordPress

You'll be redirected to the login page at http://localhost:8000/wp-admin

- **Username**: The username you just created
- **Password**: The password you just created

Click **Log In**

#### 5. Activate the Exchange Rate Hub Plugin

Once logged in:
1. Go to **Plugins** in the left sidebar
2. Find **Exchange Rate Hub** in the plugin list
3. Click **Activate**

**You should see a success message** and a new **Exchange Rates** menu item in the sidebar.

#### 6. Configure the Plugin

1. Click **Exchange Rates** → **Settings** in the sidebar
2. Configure the following:
   - **Base Currency**: Select your base currency (default: USD)
   - **Target Currencies**: Select currencies to track (e.g., EUR, GBP, JPY)
   - **Update Frequency**: Choose how often to fetch new rates (default: Twice Daily)
3. Click **Save Settings**

#### 7. Fetch Initial Exchange Rates

1. Still in **Exchange Rates** → **Settings**
2. Click the **Fetch Rates Now** button
3. You should see a success message confirming rates were fetched

#### 8. View Exchange Rates

Go to **Exchange Rates** → **View Rates** to see the current exchange rates.

#### 9. (Optional) Test Frontend Display

Create a test page:
1. Go to **Pages** → **Add New**
2. Title: "Exchange Rates"
3. In the content area, add the shortcode: `[exchange_rates]`
4. Click **Publish**
5. Click **View Page** to see the rates displayed on the frontend

**✅ Setup Complete!** Your Exchange Rate Hub is now fully configured and running.

### 🔗 Quick Links

After setup, bookmark these URLs:

- **WordPress Site**: http://localhost:8000
- **Admin Dashboard**: http://localhost:8000/wp-admin
- **Plugin Settings**: http://localhost:8000/wp-admin/admin.php?page=exchange-rates-settings
- **View Rates**: http://localhost:8000/wp-admin/admin.php?page=exchange-rates

### 🎯 What's Next?

Now that your Exchange Rate Hub is set up, you can:

1. **Customize the display** - Try different shortcode options on your test page
2. **Create a dedicated rates page** - Use the "Exchange Rates" page template
3. **Adjust update frequency** - Change how often rates are fetched
4. **Monitor automatic updates** - Rates will update automatically based on your settings
5. **Explore the admin UI** - Check out all available currencies and options

**Need help?** See the [Usage](#usage) section for detailed examples and the [Troubleshooting](#troubleshooting) section if you encounter any issues.

### Default Configuration

- **WordPress Port**: 8000 (customizable in `.env`)
- **Database**: MariaDB (internal container)
- **Default DB Credentials**: wordpress / wordpress
- **Data Storage**: Docker volumes (persists between restarts)

### Customizing Port (Optional)

If port 8000 is already in use:

```bash
# In the exchange-rate-hub directory (not docker/)
cp .env.example .env
# Edit .env and change: WORDPRESS_PORT=8000 to another port like 8080
cd docker
docker compose down
docker compose up -d
```

### Managing the Environment

#### Stopping WordPress (keeps your data)
```bash
cd docker
docker compose down
```
Your WordPress site and database will be preserved. Run `docker compose up -d` to start again.

#### Restarting WordPress
```bash
cd docker
docker compose restart
```

#### Viewing Logs
```bash
cd docker
docker compose logs -f  # Follow logs in real-time
docker compose logs wordpress  # WordPress logs only
docker compose logs db  # Database logs only
```

#### Complete Reset (removes ALL data)
```bash
cd docker
docker compose down -v
```
⚠️ **Warning**: This deletes your database, WordPress settings, and all content. You'll need to run the installation wizard again.

## Usage

### Displaying Exchange Rates on Your Site

#### Method 1: Using Shortcode (Recommended)

Add the shortcode to any page or post:

**Basic usage:**
```
[exchange_rates]
```
Displays all configured exchange rates in a default 4-column layout.

**Custom columns:**
```
[exchange_rates columns="3"]
```
Display rates in 3 columns (options: 1, 2, 3, 4, 5, 6).

**Different base currency:**
```
[exchange_rates base="EUR"]
```
Show rates with EUR as the base currency instead of your configured default.

**Combined options:**
```
[exchange_rates columns="2" base="GBP"]
```

#### Method 2: Custom Page Template

1. Create a new page in WordPress
2. In the **Page Attributes** box (right sidebar), select **Exchange Rates** template
3. Publish the page

The template will automatically display exchange rates with a custom design.

### Admin Features

#### View Current Rates
**Exchange Rates → View Rates**
- See all current exchange rates
- View last update timestamp
- Quick overview of all tracked currencies

#### Configure Settings
**Exchange Rates → Settings**
- Change base currency
- Add/remove target currencies
- Set update frequency (Hourly, Twice Daily, Daily)
- Manually fetch rates with "Fetch Rates Now" button

#### Automatic Updates
Once configured, the plugin automatically fetches new rates based on your chosen frequency:
- **Hourly**: Every hour
- **Twice Daily**: Every 12 hours
- **Daily**: Once per day at midnight

No manual intervention required!

## Development

### Local Development

This project works on **any machine with Docker installed** - no GitHub Codespaces required.

**Directory Structure:**
```
├── docker/                     # Docker configuration
│   ├── docker-compose.yml      # Docker services definition
│   └── wp-content/             # WordPress content directory (plugins, themes)
├── .env.example                # Environment variables template
├── .gitignore                  # Git ignore rules
└── README.md                   # This file
```

### Accessing the Database Directly

For advanced debugging with database tools like MySQL Workbench, phpMyAdmin, or TablePlus:

1. Edit [docker/docker-compose.yml](docker/docker-compose.yml) and uncomment the ports section under the `db` service:
   ```yaml
   ports:
     - "3306:3306"
   ```

2. Restart the database container:
   ```bash
   cd docker
   docker compose down
   docker compose up -d
   ```

3. Connect using your database client:
   - **Host**: `localhost`
   - **Port**: `3306`
   - **User**: `wordpress`
   - **Password**: `wordpress`
   - **Database**: `wordpress`

⚠️ **Security Note**: Only expose the database port in development. Never expose it in production.

## Troubleshooting

### "This site can't be reached" or Connection Refused

**Problem**: Can't access http://localhost:8000

**Solutions**:
1. Check if containers are running:
   ```bash
   cd docker
   docker compose ps
   ```
   Both `wordpress` and `db` should show "running" status.

2. If not running, start them:
   ```bash
   docker compose up -d
   ```

3. Wait 10-20 seconds after starting - WordPress needs time to initialize.

### Port 8000 Already in Use

**Problem**: Error message about port 8000 being in use

**Solution**:
```bash
# From the main project directory
cp .env.example .env
# Edit .env file and change WORDPRESS_PORT=8000 to 8080 (or any free port)
cd docker
docker compose down
docker compose up -d
```
Then access WordPress at http://localhost:8080 (or your chosen port).

### WordPress Installation Wizard Doesn't Appear

**Problem**: Blank page or error when accessing http://localhost:8000

**Solutions**:
1. Check WordPress logs:
   ```bash
   cd docker
   docker compose logs wordpress
   ```

2. Restart containers:
   ```bash
   docker compose restart
   ```

3. Complete reset (last resort):
   ```bash
   docker compose down -v
   docker compose up -d
   # Wait 20 seconds, then go to http://localhost:8000
   ```

### Plugin Not Visible in WordPress

**Problem**: Exchange Rate Hub doesn't appear in Plugins list

**Solutions**:
1. Verify plugin files exist:
   ```bash
   ls -la docker/wp-content/plugins/exchange-rate-hub/
   ```
   You should see `exchange-rate-hub.php` and other plugin files.

2. Check file permissions:
   ```bash
   cd docker
   sudo chown -R www-data:www-data wp-content
   ```

3. Restart WordPress:
   ```bash
   docker compose restart wordpress
   ```

### Database Connection Errors

**Problem**: WordPress shows "Error establishing a database connection"

**Solutions**:
1. Check if database container is running:
   ```bash
   cd docker
   docker compose ps
   ```

2. View database logs:
   ```bash
   docker compose logs db
   ```

3. Restart all containers:
   ```bash
   docker compose restart
   ```

4. If still failing, reset everything:
   ```bash
   docker compose down -v
   docker compose up -d
   ```

### Exchange Rates Not Updating

**Problem**: Rates are stale or not fetching

**Solutions**:
1. Check WordPress Cron:
   - Go to **Exchange Rates → Settings**
   - Click **Fetch Rates Now** button
   - Check for success/error messages

2. Verify internet connectivity from container:
   ```bash
   docker compose exec wordpress ping -c 3 api.exchangerate.host
   ```

3. Check WordPress error logs in wp-content/debug.log (if debugging enabled)

### Still Having Issues?

1. **Check container status**:
   ```bash
   cd docker
   docker compose ps
   ```

2. **View all logs**:
   ```bash
   docker compose logs
   ```

3. **Full reset** (nuclear option - deletes everything):
   ```bash
   cd docker
   docker compose down -v
   cd ..
   rm -rf docker/wp-content/*
   cd docker
   docker compose up -d
   ```
   Then start from Step 3 of the setup guide.

## Deployment

### Production Deployment

For production deployment, consider:

1. **Use Strong Credentials**: Update database passwords in production
2. **Environment Variables**: Never commit `.env` to version control
3. **SSL/HTTPS**: Configure SSL certificates for secure connections
4. **Backups**: Regular database and wp-content backups
5. **Updates**: Keep WordPress and plugins updated

### Deploying to Different Environments

The project is portable and can be deployed to:
- ✅ Local development machines (Windows, Mac, Linux)
- ✅ GitHub Codespaces
- ✅ Cloud servers (AWS, DigitalOcean, etc.)
- ✅ Any Docker-compatible hosting

## Plugin Architecture

All requirements implemented with production-grade code quality, security, and performance optimization.

See full documentation in code comments and [IMPLEMENTATION_REVIEW.md](IMPLEMENTATION_REVIEW.md).
