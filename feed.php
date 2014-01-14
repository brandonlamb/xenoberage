<?
#Feed#
include "config/config.php";
if (checklogin())
{
    die();
}

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email='$username'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$playerinfo = $res->fields;

$default_logs_show_per_page = 20;
					if($_POST)
					{
						$group_number = filter_var($_POST["group_no"], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);
						//throw HTTP error if group number is not valid
						if(!is_numeric($group_number)){
							header('HTTP/1.1 500 Invalid number!');
							exit();
						}
						//get current starting point of records
						$position = ($group_number * $default_logs_show_per_page);
						$log_manager = new manage_log();
						echo $log_manager->show_log('ar_player_logs',$position,$default_logs_show_per_page,$playerinfo['ship_id']);
					}
?>