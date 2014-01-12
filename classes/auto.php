<?php
/**
 * Website autoloader for all the class files.
 *
 * @copyright 	2013 - David Dawson
 * @Contact		web.developer@live.co.uk
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
session_start();
function __autoload($class_name) 
{
	/*WARNING USING STRICT CASE-SENSATIVE*/
	require_once '/kunden/homepages/29/d418208802/htdocs/sites/blacknova/class/'.$class_name. '.php';
}

?>