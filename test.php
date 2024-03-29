<?
require_once(__DIR__.'/library/init.inc.php');
require_once(__DIR__.'/library/file_factory.php');
$sql = "SELECT id from mc_book WHERE is_rec = 1  and source_url REGEXP 'www.paoshu8.info'  order by uptime DESC LIMIT 20000";
echo 1111111;
?>
