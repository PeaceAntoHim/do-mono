# Docker Deployment for Dariordal Admin Panel

This README provides instructions for deploying the Dariordal Admin Panel using Docker in a production environment.

## Prerequisites

- Docker and Docker Compose installed on your server
- Let's Encrypt SSL certificates for your domain
- Access to your production database

## Deployment Steps

### 1. Clone the Repository

```bash
git clone <your-repository-url>
cd admin-panel
```

### 2. Set Up Environment Variables

Create a `.env` file with your production settings:

```bash
cp .env.example .env
```

Edit the `.env` file and update the following variables with your production values:

- `APP_KEY` - Generate with `php artisan key:generate --show`
- `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` - Your database credentials
- `MAIL_*` - Your mail server settings
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` - Your Google OAuth credentials
- `PHOTO_API_URL`, `SCRAPE_API_URL` - Your API endpoints

### 3. SSL Certificates

Ensure your Let's Encrypt certificates are installed on the server:

```bash
# Example using certbot (install if not already installed)
sudo apt install certbot python3-certbot-nginx

# Obtain certificates
sudo certbot --nginx -d dariordal.com
```

The certificates should be available at:
- `/etc/letsencrypt/live/dariordal.com/fullchain.pem`
- `/etc/letsencrypt/live/dariordal.com/privkey.pem`

### 4. Deploy with Docker

Run the setup script:

```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

The script will:
- Check for required files and directories
- Set proper permissions
- Detect the correct Docker Compose command format
- Build and start Docker containers

If you get a `docker-compose command not found` error, you may need to install Docker Compose:

```bash
# For Docker Compose plugin (newer Docker installations)
apt-get update && apt-get install -y docker-compose-plugin

# Or for standalone Docker Compose
apt-get update && apt-get install -y docker-compose
```

### 5. Verify Deployment

Visit your domain (https://dariordal.com) to verify the application is running correctly.

## Security Considerations

This deployment includes:

- HTTPS with strong SSL settings
- Non-root user running PHP-FPM
- Proper file permissions
- Removal of development tools from production container
- Environment variables instead of hardcoded credentials

## Troubleshooting

- **SSL Issues**: Check that the certificates are properly mounted in the Nginx container
- **Database Connection Errors**: Verify your database credentials and ensure the database is accessible from the container
- **Permission Problems**: Run `chmod -R 775 storage bootstrap/cache` on the host if needed
- **Docker Compose Command Not Found**: Modern Docker installations use `docker compose` (without hyphen) instead of `docker-compose` (with hyphen). The script should detect this automatically, but you may need to install the appropriate package.

## Maintenance

### Updating the Application

```bash
# Pull latest changes
git pull

# Rebuild and restart containers
# Use the appropriate Docker Compose command format for your system
docker compose down  # or docker-compose down
docker compose up -d --build  # or docker-compose up -d --build
```

### Logs

```bash
# View application logs
docker logs admin-panel-app

# View nginx logs
docker logs admin-panel-nginx
``` 