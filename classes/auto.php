<?php
/**
 * Website autoloader for all the class files.
 *
 * @copyright 	2013 - David Dawson
 * @Contact		web.developer@live.co.uk
# @license    	http://www.gnu.org/licenses/agpl.txt										#
 */
session_start();
function __autoload($class_name) 
{
	/*WARNING USING STRICT CASE-SENSATIVE*/
	require_once '/class/'.$class_name. '.php';
}

?>