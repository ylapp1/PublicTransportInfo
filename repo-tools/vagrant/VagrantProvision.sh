#!/usr/bin/env bash

apt-get update


# Install some utils
apt-get install -y bash-completion


# Install php
apt-get install -y php7.0 php7.0-json php7.0-mbstring php7.0-dom php-xdebug

# Install apache
apt-get install -y apache2 libapache2-mod-php7.0

# Install composer
apt-get install -y composer unzip


# Disable the phar.readonly option
sed -i 's/;phar.readonly *=.*/phar.readonly = Off/' /etc/php/7.0/cli/php.ini


# Temporarily turn off xdebug
phpdismod xdebug

# Install the composer dependencies
cd /vagrant
su -c "composer install" vagrant

# Create the phar package and copy it to the example web page
su -c "vendor/bin/phing pack" vagrant
mv PublicTransportInfo.phar example

phpenmod xdebug

service apache2 restart
