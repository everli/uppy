# create uppy user
CREATE USER 'uppy'@'%' IDENTIFIED WITH mysql_native_password BY 'uppy';

# Create uppy_test schema
CREATE DATABASE IF NOT EXISTS `uppy_test` CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci` ;
GRANT ALL ON `uppy_test`.* TO 'uppy'@'%' ;
