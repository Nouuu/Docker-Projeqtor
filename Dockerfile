ARG PHP_VERSION=8.1
ARG PJT_VERSION=10.1.3
ARG PJT_ARCHIVE_URL=https://freefr.dl.sourceforge.net/project/projectorria/projeqtorV${PJT_VERSION}.zip
ARG PJT_ARCHIVE_NAME=projeqtorV${PJT_VERSION}.zip
ARG PJT_EXTRACT_DIR=projeqtor

FROM debian:11-slim AS base
ARG PJT_ARCHIVE_URL
ARG PJT_ARCHIVE_NAME
ARG PJT_EXTRACT_DIR

RUN echo "Installing base packages" \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        unzip \
    && rm -rf /var/lib/apt/lists/*


RUN echo "Downloading projeqtor"
ADD ${PJT_ARCHIVE_URL} /tmp/${PJT_ARCHIVE_NAME}

RUN echo "Extract projeqtor archive" \
    && unzip -q /tmp/${PJT_ARCHIVE_NAME} -d /tmp \
    && mv /tmp/${PJT_EXTRACT_DIR} /opt/projeqtor \
    && rm /tmp/${PJT_ARCHIVE_NAME}

FROM php:${PHP_VERSION}-apache

# Add from base
COPY --from=base /opt/projeqtor/ /var/www/html

COPY config/parametersLocation.php /var/www/html/tool/

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

VOLUME /mnt/documents/
VOLUME /mnt/logs/


COPY config/parameters.php config/run.sh /opt/
#ADD config/run.sh /opt
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

EXPOSE 80
ENTRYPOINT ["sh" ,"/opt/run.sh"]
