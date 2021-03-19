================================================================================
= INSTALL PROJEQTOR                                                            =
================================================================================

Pre-requisites :
  - http server
  - PHP server 5.4 or over
    PHP 5.6 or over is strongly advised
    PHP 7.0 is advised when available : it brings great performance improvments
  - MySQL database (5 or over) or PostgreSql database (V9.1 or over)
  
  For instance, you may try to set-up an EasyPHP server, including all required elements.
  This set-up is not recommanded for production purpose, but only for testing and evaluation purpose.
  You may also set-up a Zend Server, including all required elements.
  This set-up can be used for production purpose.

  PHP configuration advised :
    max_input_vars = 4000 ; must be > 2000 for real work allocation screen
    request_terminate_timeout = 0 ; must not end requests on timeout to let cron run without ending
	  max_execution_time = 30 ; minimum advised
    memory_limit = 512M ; minimum advised for PDF generation
    file_uploads = On ; to allow attachements and documents management
    error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
  PHP extensions required :  
    gd => for reports graphs
    imap ==> to retrieve mails to insert replay as notes
    mbstring => mandatory. for UTF-8 compatibility
    mysql => for default MySql database
    openssl => to send mails if smtp access is authentified (with user / password) 
    pdo_mysql => for default MySql database
    pdo_pqsql => if database is PostgreSql
    pgsql => if database is PostgreSql
    php-xml => for xml parsing
    zip => ZipArchive class is mandatory to manage plugins and export to Excel format

Set-up :
  - Unzip projeqtorVx.y.z.zip to the web server directory
  - Run application in your favorite browser, using http://yourserver/projeqtor
  - Enjoy !
  
Configuration : 
  - At first run, configuration screen will be displayed.
  - Default parameters may work for a test instance. 
  - For MySql, default user is 'root', default password is 'mysql' but may also be '' (blank) on some xAMP stacks. 
  - To run again configuration screen, just delete "/tool/parametersLocation.php" file.
  - On first connection, database will be automatically updated.
  - login : admin/admin

Security advise :
   - Setup attachments directory and documents directory out of web access (outside document_root of web server)
	 This will prevent hachers from uploading php file and executing it on your server ...
  
Deploy new version ;
  - Unzip projeqtorVx.y.z.zip to the web server directory
    (installing from existing version before V4.0.0, please take care that root directory name has changed)
  - Connect as administrator : database will update automatically.
  
Support :
  - you may request support in the Forum of ProjeQtOr web site : http://projeqtor.org