FROM php:7.4-apache

ADD projeqtor.tar.gz /var/www/html/
#COPY projeqtor.zip /var/www/html/
#RUN unzip /var/www/html/projeqtor.zip && rm /var/www/html/projeqtor.zip
RUN chown -R www-data:www-data /var/www/html/

ENV PHP_MAX_INPUT_VARS=4000
ENV PHP_REQUEST_TERMINATE_TIMEOUT=0
ENV PHP_MAX_EXECUTION_TIME=30
ENV PHP_MEMORY_LIMIT=512M

ENV PJT_DB_TYPE=mysql
ENV PJT_DB_HOST=127.0.0.1
ENV PJT_DB_PORT=3306
ENV PJT_DB_USER=root
ENV PJT_DB_PASSWORD=root
ENV PJT_DB_NAME=projeqtor
ENV PJT_DB_PREFIX=pjt
ENV PJT_SSL_KEY=''
ENV PJT_SSL_CERT=''
ENV PJT_SSL_CA=''
ENV PJT_ATTACHMENT_MAX_SIZE_MAIL=2097152
ENV PJT_LOG_LEVEL=2
ENV PJT_ENFORCE_UTF8=1

VOLUME /mnt/documents/
VOLUME /mnt/logs/

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ADD config/parameters.php /opt
RUN chown www-data:www-data /opt/parameters.php
ADD config/run.sh /opt
RUN chmod 777 /opt/run.sh
#ADD config/parametersLocation.php /opt

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN apt-get update -yqq && \
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
    libfreetype6-dev

RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

RUN docker-php-ext-configure ldap
RUN docker-php-ext-install ldap

RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl
RUN docker-php-ext-install -j$(nproc) imap

#RUN docker-php-ext-configure openssl
#RUN docker-php-ext-install openssl

RUN docker-php-ext-install \
#  mbstring \
  mysqli \
  pdo \
  pdo_mysql \
  pdo_pgsql \
  pgsql

EXPOSE 80
ENTRYPOINT ["sh" ,"/opt/run.sh"]
