<?
define("SHORT_INSTALL", true);
define("SHORT_INSTALL_CHECK", true);

define("MYSQL_TABLE_TYPE", "INNODB");
define("BX_UTF", true);

define("DBPersistent", false);
$DBType = "mysql";
$DBHost = "localhost";
$DBName = 'vh34203_bi001';
$DBLogin = 'vh34203_bius001';
$DBPassword = 'xah4faV4Nohwiecop7';
$DBDebug = false;
$DBDebugToFile = false;

define("BX_FILE_PERMISSIONS", 0644);
define("BX_DIR_PERMISSIONS", 0755);
@umask(~BX_DIR_PERMISSIONS);

define("BX_USE_MYSQLI", true);
define("DELAY_DB_CONNECT", true);
define("CACHED_menu", 3600);
define("CACHED_b_file", 3600);
define("CACHED_b_file_bucket_size", 10);
define("CACHED_b_lang", 3600);
define("CACHED_b_option", 3600);
define("CACHED_b_lang_domain", 3600);
define("CACHED_b_site_template", 3600);
define("CACHED_b_event", 3600);
define("CACHED_b_agent", 3660);
//Cron Added
if(!(defined("CHK_EVENT") && CHK_EVENT===true))
   define("BX_CRONTAB_SUPPORT", true);

?>
