# Docker-Projeqtor

This docker image provides a [Projeqtor](https://www.projeqtor.org) container with LDAP support.

This image is based on `php:7.4-apache`

Version of Projeqtor in this image is currently **9.0.6**

## Exposed ports :

- 80 : Projeqtor Webpanel

## Volumes

Comming soon...

## Environment

Current used environments vars :

| Environment variable          | Default | Recommended                                                  |
| ----------------------------- | ------- | ------------------------------------------------------------ |
| PHP_MAX_INPUT_VARS            | 4000    | Must be > 2000 for real work allocation screen               |
| PHP_REQUEST_TERMINATE_TIMEOUT | 0       | Must not end requests on timeout to let cron run without ending |
| PHP_MAX_EXECUTION_TIME        | 30      | 30 is minimum advised                                        |
| PHP_MEMORY_LIMIT              | 512M    | 512M is minimum advised for PDF generation                   |

## Installed PHP extensions

| Extension | Usage                                                        |
| --------- | ------------------------------------------------------------ |
| qd        | For reports graphs                                           |
| imap      | To retrieve mails to insert replay as notes                  |
| mbstring  | Mandatory. for UTF-8 compatibility                           |
| mysqli    | For default MySql database                                   |
| pgsql     | If database is PostgreSql                                    |
| pdo       | BDD connector                                                |
| pdo_mysql | For default MySql database                                   |
| pdo_pgsql | If database is PostgreSql                                    |
| openssl   | To send mails if smtp access is authentified (with user / password) |
| ldap      | Directory Access Protocol, and is a protocol used to access "Directory Servers" |
| zip       | ZipArchive class is mandatory to manage plugins and export to Excel format |

