# Docker-Projeqtor

> ⚠️ This project will no longer be maintained. If you want to upgrade to the latest version of Projeqtor, you could try
> to update the version ARGS in Dockerfile and rebuild the image.

Docker image for [Projeqtor](https://www.projeqtor.org) with LDAP support.

*This image is derived from various `php-apache` versions:*

- For version 10.3 onwards, `php:8.2-apache` is used.
- From versions 10.0 to 10.2, `php:8.1-apache` was utilized.
- Prior to version 10.0, `php:7.4-apache` was the choice.

The **available Projeqtor tags** in this image include:

- latest, 10.4.5, 10.4.3, 10.4.2, 10.4.0, 10.3.6, 10.3.0, 10.2.3, 10.2.1, 10.1.3, 10.1.2, 9.5.4, 9.4.2, 9.2.2, 9.1.2, and 9.0.6.

## Technical Specifications

**Exposed Ports:**

- 80 (Projeqtor Webpanel)
- 25 (SMTP)

**Volumes:**

- /mnt/documents
- /mnt/logs

*Note: Read and write (rw) access is required for both volumes.*

## Environment Variables

### PHP Environment Variables

| Variable                      | Default | Description                                            |
|-------------------------------|---------|--------------------------------------------------------|
| PHP_MAX_INPUT_VARS            | 4000    | Recommended > 2000 for real work allocation screen     |
| PHP_REQUEST_TERMINATE_TIMEOUT | 0       | Set to allow cron to run without termination           |
| PHP_MAX_EXECUTION_TIME        | 30      | Minimum recommended setting is 30                      |
| PHP_MEMORY_LIMIT              | 512M    | Minimum recommended setting is 512M for PDF generation |

### Projeqtor Environment Variables

| Variable                     | Default   | Description                                                                            |
|------------------------------|-----------|----------------------------------------------------------------------------------------|
| PJT_DB_TYPE                  | mysql     | Can be `mysql` or `pgsql`                                                              |
| PJT_DB_HOST                  | 127.0.0.1 | Server name                                                                            |
| PJT_DB_PORT                  | 3306      | Database port                                                                          |
| PJT_DB_USER                  | root      | User to connect database                                                               |
| PJT_DB_PASSWORD              | root      | Password for database user                                                             |
| PJT_DB_NAME                  | projeqtor | Database schema name                                                                   |
| PJT_DB_PREFIX                | `empty`   | Prefix for table names                                                                 |
| PJT_SSL_KEY                  | `empty`   | SSL Certificate key path                                                               |
| PJT_SSL_CERT                 | `empty`   | SSL Certificate path                                                                   |
| PJT_SSL_CA                   | `empty`   | SSL Certificate CA path                                                                |
| PJT_ATTACHMENT_MAX_SIZE_MAIL | 2097152   | Max file size in email                                                                 |
| PJT_LOG_LEVEL                | 2         | Log level ('4' for tracing, '3' for debug, '2' for trace, '1' for error, '0' for none) |
| PJT_ENFORCE_UTF8             | 1         |                                                                                        |

## Installed PHP Extensions

| Extension | Purpose                                                |
|-----------|--------------------------------------------------------|
| gd        | For reports graphs                                     |
| imap      | To retrieve mails to insert reply as notes             |
| mbstring  | Necessary for UTF-8 compatibility                      |
| mysqli    | For default MySql database                             |
| pgsql     | For PostgreSql database                                |
| pdo       | Database connector                                     |
| pdo_mysql | For default MySql database                             |
| pdo_pgsql | For PostgreSql database                                |
| openssl   | For mails if SMTP access is authenticated              |
| ldap      | To access "Directory Servers"                          |
| zip       | Required for plugins management/export to Excel format |

## Start the Stack

Proceed with the following [Docker Compose](./docker-compose.yml.example), which I use to deploy a Projeqtor stack with
a MySQL database.

During the first deployment, Admin login might be required (on the Projeqtor login page) for DB initialization.

```yaml

services:
  mysql:
    image: mysql:latest
    volumes:
      - mysql_data:/var/lib/mysql
    environment:
      - MYSQL_ROOT_PASSWORD=changeme
      - MYSQL_DATABASE=projeqtor
  projeqtor:
    image: nospy/projeqtor:latest
    depends_on:
      - mysql_service
    volumes:
      - projeqtor_documents:/mnt/documents
      - projeqtor_logs:/mnt/logs
    ports:
      - "25:25"
      - "80:80"
    environment:
      - PHP_MAX_EXECUTION_TIME=30
      - PHP_MAX_INPUT_VARS=4000
      - PHP_MAX_UPLOAD_SIZE=1G
      - PHP_MEMORY_LIMIT=512M
      - PHP_REQUEST_TERMINATE_TIMEOUT=0
      - PJT_ATTACHMENT_MAX_SIZE_MAIL=2097152
      - PJT_DB_TYPE=mysql
      - PJT_DB_HOST=mysql_service
      - PJT_DB_PORT=3306
      - PJT_DB_NAME=projeqtor
      - PJT_DB_USER=root
      - PJT_DB_PASSWORD=changeme
volumes:
  mysql_data:
  projeqtor_documents:
  projeqtor_logs:
```