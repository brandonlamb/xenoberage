<?

require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
$process_xenobes = new manage_xenobe();
echo $process_xenobes->process_xenobes();
?>