# Docker-Projeqtor

This docker image provides a [Projeqtor](https://www.projeqtor.org) container with LDAP support.

This image is derived from different versions of `php-apache`:

- `php:8.2-apache` starting from version 10.3 onward.
- `php:8.1-apache` used in version 10.0 to version 10.2.
- `php:7.4-apache` was used before version 10.0.

Available tags of Projeqtor in this image are :

- **latest** (10.4.0)
- **10.4.0**
- **10.3.6**
- **10.3.0**
- **10.2.3**
- **10.2.1**
- **10.1.3**
- **10.1.2**
- **9.5.4**
- **9.4.2**
- **9.2.2**
- **9.1.2**
- **9.0.6**

## Exposed ports :

- 80 : Projeqtor Webpanel
- 25 : SMTP

## Volumes

They are two volume mounted on this image :

- /mnt/documents
- /mnt/logs

Note: Both volumes require read and write (rw) access.

## ## Environment Variables:

### PHP Environment Variables

| Variable                      | Default | Recommendation                                         |
|-------------------------------|---------|--------------------------------------------------------|
| PHP_MAX_INPUT_VARS            | 4000    | Should be > 2000 for real work allocation screen       |
| PHP_REQUEST_TERMINATE_TIMEOUT | 0       | Should be set to allow cron to run without termination |
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

## Installed PHP extensions

| Extension | Usage                                                  |
|-----------|--------------------------------------------------------|
| gd        | For reports graphs                                     |
| imap      | To retrieve mails to insert reply as notes             |
| mbstring  | Mandatory for UTF-8 compatibility                      |
| mysqli    | For default MySql database                             |
| pgsql     | If the database is PostgreSql                          |
| pdo       | Database connector                                     |
| pdo_mysql | For default MySql database                             |
| pdo_pgsql | If the database is PostgreSql                          |
| openssl   | To send mails if SMTP access is authenticated          |
| ldap      | Used to access "Directory Servers"                     |
| zip       | Mandatory to manage plugins and export to Excel format |

# Ready 2 Go Stack

Here is my own [docker compose](./docker-compose.yml.example) stack I use to deploy Projeqtor stack with MySQL database.

First deploy may require admin login (on Projeqtor login page) to init DB.

> :warning: This stack is for Docker Swarm, if you want to run it on simple docker compose, you must replace `overlay`
> in network definition by `bridge`

```yaml
version: '3.8'

services:
  mysql_service:
    image: mysql:latest
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - projeqtor_network
    environment:
      - MYSQL_ROOT_PASSWORD=changeme
      - MYSQL_DATABASE=projeqtor
  projeqtor_service:
    image: nospy/projeqtor:latest
    depends_on:
      - mysql_service
    volumes:
      - projeqtor_documents:/mnt/documents
      - projeqtor_logs:/mnt/logs
    ports:
      - "25:25"
      - "80:80"
    networks:
      - projeqtor_network
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

networks:
  projeqtor_network:
    driver: overlay
    attachable: true
```
