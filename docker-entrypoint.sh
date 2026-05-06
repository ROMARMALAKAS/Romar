#!/bin/bash

# Create persistent directories if they don't exist
mkdir -p /data/uploads /data/uploads/chat /data/database

# Symlink persistent storage
rm -rf /var/www/html/uploads
ln -sf /data/uploads /var/www/html/uploads

rm -rf /var/www/html/database
ln -sf /data/database /var/www/html/database

# Ensure proper permissions
chown -R www-data:www-data /data

# Start Apache
apache2-foreground
