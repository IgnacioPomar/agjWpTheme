FROM wordpress:php8.3-apache

#
# Herramientas de desarrollo
#
RUN apt-get update \
    && apt-get install -y \
        git \
        curl \
        unzip \
        less \
        vim \
        sudo \
    && rm -rf /var/lib/apt/lists/*

#
# Composer
#
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

#
# WP-CLI
#
RUN curl -L \
    https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    -o /usr/local/bin/wp \
    && chmod +x /usr/local/bin/wp

#
# Xdebug
#
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY .devcontainer/xdebug.ini \
    /usr/local/etc/php/conf.d/99-xdebug.ini

#
# Usuario de desarrollo
#
RUN useradd -m -s /bin/bash vscode \
    && echo "vscode ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/vscode \
    && chmod 0440 /etc/sudoers.d/vscode \
    && mkdir -p /home/vscode/.ssh \
    && chown -R vscode:vscode /home/vscode

WORKDIR /var/www/html