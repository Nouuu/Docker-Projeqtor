# Define base arguments for ProjeQtOr
# PHP_VERSION: the version of php to use
# PJT_VERSION: the version of ProjeQtOr to use
# PJT_ARCHIVE_NAME: the archive name for ProjeQtOr
# PJT_EXTRACT_DIR: the directory name to extract the  to
# PJT_ARCHIVE_URL: the URL to download ProjeQtOr
ARG PHP_VERSION=8.2
ARG PJT_VERSION=11.0.4
ARG PJT_ARCHIVE_NAME=projeqtorV${PJT_VERSION}.zip
ARG PJT_EXTRACT_DIR=projeqtor
ARG PJT_ARCHIVE_URL=https://freefr.dl.sourceforge.net/project/projectorria/projeqtorV${PJT_VERSION}.zip

# Stage 1: Base. Prepares the base image for our Docker build
FROM debian:bookworm-slim AS base
ARG PJT_ARCHIVE_URL
ARG PJT_ARCHIVE_NAME
ARG PJT_EXTRACT_DIR

# Update and install necessary Debian packages
RUN echo "Installing base packages" \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates unzip wget \
    && rm -rf /var/lib/apt/lists/*


# Download and extract ProjeQtOr archive
RUN echo "Downloading projeqtor"  \
    && wget -q -O /tmp/${PJT_ARCHIVE_NAME} ${PJT_ARCHIVE_URL} \
    && echo "Extract projeqtor archive" \
    && unzip -q /tmp/${PJT_ARCHIVE_NAME} -d /tmp \
    && mv /tmp/${PJT_EXTRACT_DIR} /opt/projeqtor \
    && rm /tmp/${PJT_ARCHIVE_NAME}

# Stage 2: PHP. Prepares the PHP environment
FROM php:${PHP_VERSION}-apache

# Copy the output from base
COPY --from=base /opt/projeqtor/ /var/www/html

# Copy configuration files
COPY config/parametersLocation.php /var/www/html/tool/

# Environment Variables for PHP and ProjeQtOr configuration. They can be overridden in docker-run command.
ENV PHP_MAX_INPUT_VARS=4000 \
 PHP_REQUEST_TERMINATE_TIMEOUT=0 \
 PHP_MAX_EXECUTION_TIME=30 \
 PHP_MEMORY_LIMIT=512M \
 PHP_MAX_UPLOAD_SIZE=1G \
 PJT_DB_TYPE=mysql \
 PJT_DB_HOST=127.0.0.1 \
 PJT_DB_PORT=3306 \
 PJT_DB_USER=root \
 PJT_DB_PASSWORD=root \
 PJT_DB_NAME=projeqtor \
 PJT_DB_PREFIX='' \
 PJT_SSL_KEY='' \
 PJT_SSL_CERT='' \
 PJT_SSL_CA='' \
 PJT_ATTACHMENT_MAX_SIZE_MAIL=2097152 \
 PJT_LOG_LEVEL=2 \
 PJT_ENFORCE_UTF8=1

# Create persistent storage volumes for documents and logs
VOLUME /mnt/documents/
VOLUME /mnt/logs/


COPY config/parameters.php config/run.sh /opt/
# Setup apache and php environments
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    chown www-data:www-data /opt/parameters.php && \
    chmod 777 /opt/run.sh && \
    chown -R www-data:www-data /var/www/html/ && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    echo "Update and install packages" && \
    apt-get update -yqq && \
    apt-get install -yqq --no-install-recommends \
    apt-utils \
    libpq-dev \
    openssl \
    libzip-dev zip unzip \
    libc-client-dev libkrb5-dev \
    mariadb-client \
    libpng-dev \
    libldap2-dev \
    libonig-dev \
    libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev \
    libfreetype6-dev && \
    docker-php-ext-configure zip && \
    docker-php-ext-install zip && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd && \
    docker-php-ext-configure ldap && \
    docker-php-ext-install ldap && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install -j$(nproc) imap && \
    docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql

# Expose ports.
EXPOSE 80

# Define the entrypoint script
ENTRYPOINT ["sh" ,"/opt/run.sh"]
