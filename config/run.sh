#!/bin/sh

echo "Init config"
FILE="$PHP_INI_DIR/php.ini"
cp "$PHP_INI_DIR/php.ini-production" $FILE

chown -R www-data:www-data /mnt/

#cp -f /opt/parametersLocation.php /var/www/html/tool/parametersLocation.php
#chown www-data:www-data /var/www/html/tool/parametersLocation.php
#chmod 777 /var/www/html/tool/parametersLocation.php

echo "\
max_input_vars = $PHP_MAX_INPUT_VARS                        ; must be > 2000\n\
request_terminate_timeout = $PHP_REQUEST_TERMINATE_TIMEOUT  ; must not end requests on timeout to let cron run without ending\n\
max_execution_time = $PHP_MAX_EXECUTION_TIME                ; minimum advised\n\
memory_limit = $PHP_MEMORY_LIMIT                            ; minimum advised for PDF generation\n\
file_uploads = On                                           ; to allow attachements and documents management\n\
upload_max_filesize = $PHP_MAX_UPLOAD_SIZE                  ; upload max file size
post_max_size = $PHP_MAX_UPLOAD_SIZE                        ; upload max size
allow_url_fopen = True
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT\n" >>"$FILE"

echo "Init apache2"
apache2-foreground
