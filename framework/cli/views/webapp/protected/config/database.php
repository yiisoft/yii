<?php

// This is the database connection configuration.
// For Docker: use 'mysql_aisana' as host
// For local: use 'localhost' or '127.0.0.1' as host
$dbHost = getenv('DB_HOST') ?: 'mysql_aisana'; // Default to Docker service name
$dbName = getenv('DB_NAME') ?: 'aisana';
$dbUser = getenv('DB_USER') ?: 'aisana_user';
$dbPassword = getenv('DB_PASSWORD') ?: 'aisana_password';

return array(
	// MySQL database configuration
	'connectionString' => "mysql:host={$dbHost};dbname={$dbName}",
	'emulatePrepare' => true,
	'username' => $dbUser,
	'password' => $dbPassword,
	'charset' => 'utf8',
	'tablePrefix' => 'tbl_',
	
	// SQLite configuration (commented out)
	/*
	'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/aisana.db',
	'tablePrefix' => 'tbl_',
	*/
);