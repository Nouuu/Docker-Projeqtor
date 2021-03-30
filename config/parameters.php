<?php
$paramDbType = getenv('PJT_DB_TYPE');
$paramDbHost = getenv('PJT_DB_HOST');
$paramDbPort = getenv('PJT_DB_PORT');
$paramDbUser = getenv('PJT_DB_USER');
$paramDbPassword = getenv('PJT_DB_PASSWORD');
$paramDbName = getenv('PJT_DB_NAME');
$paramDbPrefix = getenv('PJT_DB_PREFIX');
$SslKey = getenv('PJT_SSL_KEY');
$SslCert = getenv('PJT_SSL_CERT');
$SslCa = getenv('PJT_SSL_CA');
$AttachmentMaxSizeMail = '2097152';
$logLevel = getenv('PJT_LOG_LEVEL');
$enforceUTF8 = getenv('PJT_ENFORCE_UTF8');
$documentRoot = '/mnt/documents/';
$logFile = '/mnt/logs/projeqtor_${date}.log';
$paramDefaultLocale = 'en';
