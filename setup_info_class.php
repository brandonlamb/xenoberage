<?php
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2012 Ron Harwood and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: setup_info_class.php

if (preg_match("/setup_info_class.php/i", $_SERVER['PHP_SELF']))
{
    echo "You can not access this file directly!";
    die();
}

class SETUPINFO_CLASS
{
    var $appinfo;
    var $ADOdb_status;
    var $database_server_version;

    var $switches;
    var $cookie_test;

    // Constructor
    function SETUPINFO_CLASS($in_Value = 0)
    {
        global $connectedtodb,$db;

        // Register destructor
        register_shutdown_function(array(&$this, '_SETUPINFO_CLASS'));

        $this->appinfo['title'] = "Setup Information Class";
        $this->appinfo['description'] = "This class has been written for BNT v0.55 and updated for the BNT Core";
        $this->appinfo['version'] = "0.6.20";
        $this->appinfo['releasetype'] = "OEM CLASS";
        $this->appinfo['createdate'] = date("l, F d, Y",strtotime ("December 27, 2005"));
        $this->appinfo['updatedate'] = date("l, F d, Y",filemtime (basename (__FILE__)));
        $this->appinfo['author'] = "TheMightyDude";
        $this->appinfo['email'] = "TheMightyDude@gmail.com";
        $this->appinfo['hash'] = strtoupper(md5_file(__FILE__));
        $this->appinfo['test'] = __FILE__;

        ################################
        # Display Enviroment Variables #
        ################################
        $this->switches['Enable_Database'] = array("caption" => "Enable Database Testing",
            "info" => "This will enable Database Connection and Testing.", "enabled" => false);

        ################################
        # Display Enviroment Variables #
        ################################
        $this->switches['Show_Env_Var'] = array("caption" => "Display Environment Variables",
            "info" => "This test will display all variables stored in $"."_SERVER.", "enabled" => false);

        #######################
        # Enable Cookie Tests #
        #######################
        $this->switches['Test_Cookie'] = array("caption" => "Test the cookie creation",
            "info" => "This test uses Sessions to test the creation of cookie!", "enabled" => true);

        ######################
        # Display Patch Info #
        ######################
        $this->switches['Display_Patches'] = array("caption" => "Display Installed Patches",
            "info" => "This enables the look up of installed patches!", "enabled" => false);

        ##################
        # Display Errors #
        ##################
        $this->switches['Display_Errors'] = array("caption" => "Display Errors",
            "info" => "This test will display all errors, warnings and parse errors that it finds.", "enabled" => true);

        $this->error_switching();

    }

    // Destructor
    function _SETUPINFO_CLASS()
    {
        global $db;

        if ($db)
        {
            $db->Close();
            $db = null;
        }
    }

    function initDB()
    {
        global $connectedtodb,$db;
        if ($this->switches['Enable_Database']['enabled']==true)
        {
            if (!$connectedtodb)
            {
                connectdb(false);
            }
        }
    }

    function error_switching()
    {
        if ($this->switches['Display_Errors']['enabled'])
        {
            ini_set('error_reporting', E_ALL | E_STRICT);
            ini_set('display_errors', 'On');
        }
        else
        {
            ini_set('error_reporting', 0);
            ini_set('display_errors', 'On');
        }
    }

    ##############################
    #  This gets the Game Path.  #
    ##############################
    function get_gamepath($compare = false)
    {
        $game_path['result']  = null;
        $game_path['info']    = null;
        $game_path['status']  = false;

        $result=dirname($_SERVER["PHP_SELF"]);
        if (isset($result) && strlen($result) > 0)
        {
            if ($result === "\\")
            {
                $result = "/";
            }
            if ($result[0] != ".")
            {
                if ($result[0] != "/")
                {
                    $result = "/$result";
                }
                if ($result[strlen($result)-1] != "/")
                {
                    $result = "$result/";
                }
            }
            else
            {
                $result ="/";
            }
            $game_path['result'] = str_replace("\\", "/", stripcslashes($result));
            $game_path['status'] = true;
        }
        else
        {
            $game_path['info']   =(($compare) ? "Unable to detect gamepath to compare!" : "Unable to detect gamepath!");
            $game_path['status'] = false;
        }

        return $game_path;
    }

    function get_gamedomain($compare = false)
    {
        $game_domain['result']  = null;
        $game_domain['info']    = null;
        $game_domain['status']  = false;

        $RemovePORT = true;
        $result = $_SERVER['HTTP_HOST'];

        if (isset($result) && strlen($result) >0)
        {
            $pos = strpos($result,"http://");
            if (is_integer($pos))
            {
                $result = substr($result,$pos+7);
            }
            $pos = strpos($result,"www.");
            if (is_integer($pos))
            {
                $result = substr($result,$pos+4);
            }
            if ($RemovePORT)
            {
                $pos = strpos($result,":");
            }
            if (is_integer($pos))
            {
                $result = substr($result,0,$pos);
            }
            if ($result[0]!=".")
            {
                $result=".$result";
            }
            $game_domain['result'] = $result;
            $game_domain['status'] = true;
        }
        else
        {
            $game_domain['info']   = (($compare) ?"Unable to detect the gamedomain to compare!":"Unable to detect the gamedomain!");
            $game_domain['status'] = false;
        }

        return $game_domain;
    }

    ################################
    # This is where we test the    #
    # connection to the database.  #
    ################################
    function testdb_connection()
    {
        global $ADODB_SESSION_CONNECT, $dbport, $ADODB_SESSION_USER, $ADODB_SESSION_PWD, $ADODB_SESSION_DB, $db, $ADODB_FETCH_MODE;
        global $default_lang;

        $this->mysql_version = null;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

        // This my not be needed, but I will leave it here just in case we need it :)
        // $this->database_client_version = mysql_get_client_info();

        if ($this->switches['Enable_Database']['enabled'])
        {
            $this->db_status['status'] = (( ($db instanceof ADOConnection) && is_resource($db->_connectionID))? "Connected OK":"Not Connected");

#echo "<pre>[ServerInfo]\n". print_r($db->ServerInfo(), true) ."</pre>\n";
#echo "<pre>[IsConnected]\n". print_r($db->IsConnected(), true) ."</pre>\n";

#echo "<pre>[dump]\n". print_r($db, true) ."</pre>\n";

            if ( ($db instanceof ADOConnection) && $db->IsConnected() )
            {
                $server_version = $db->ServerInfo();
                $this->database_server_version = "{$server_version['version']}";
                $return = true;
            }
            else
            {
                $this->db_status['error'] = "Please check you have the correct db info set in config_local.php.";
                $return = false;
            }
        }
        else
        {
            $this->db_status['status'] = "Not Connected";
            $this->db_status['error'] = "Database Tests have been disabled.";
            $return = false;
        }

        return $return;
    }

    function validate_database()
    {
        global $db;
        $db_info = null;

        if ($this->switches['Enable_Database']['enabled']==true)
        {
            if ($db)
            {
                  // This currently doesn't work, I'll have to code a replacement later
/*                $db_info['status'] = "Setup Info has found ".count($dbtables)." tables in the Tables List.";

                foreach ($dbtables as $k => $v)
                {
                    $test = @$db->Execute("SELECT COUNT(*) as record_count FROM $v");
                    db_op_result ($db, $test, __LINE__, __FILE__, $db_logging);
                    if (is_bool($test) && $test == false)
                    {
                        $count = 0;
                    }
                    else
                    {
                        $count = $test->fields['record_count'];
                    }
                    $db_info[$k]['name']="$v";
                    $db_info[$k]['status']="Failed";
                    $db_info[$k]['info']=$db->ErrorMsg();
                    if ($db->ErrorNo()==0)
                    {
                        $db_info[$k]['name']="$v";
                        $db_info[$k]['status']="Passed";
                        $db_info[$k]['info']="Found $count records in the $k table.";
                    }
                }*/
            }
            else
            {
                $db_info['status'] = "Not connected to DB -- Skipping validation!";
            }
        }
        else
        {
                $db_info['status'] = "Database Test have been Disabled -- Skipping validation!";
        }

        return $db_info;
    }

    ###################################
    #  This validates the ADOdb Path  #
    ###################################
    function validate_ADOdb_path($do_status=true)
    {
        global $ADOdbpath,$ADODB_vers;

        $this->ADOdb_status = null;

        if (file_exists(realpath("$ADOdbpath/adodb.inc.php"))==true)
        {
            if ($do_status==true)
            {
                $this->ADOdb_status['status'] = "ADOdb is correctly setup";
                $this->ADOdb_status['version'] = $ADODB_vers;
            }
            $return = true;
        }
        else
        {
            if ($do_status==true)
            {
                $this->ADOdb_status['status'] = "Invalid ADOdb Folder";
                $this->ADOdb_status['help'] = "Please check your $"."ADOdbpath setting in config_local.php";
            }
            $return = false;
        }

        return $return;
    }

    function MySQL_Status()
    {
        global $db;

        if (!mysql_ping($db))
        {
            $MYSQL_STATUS= "Down";
        }
        else
        {
            $MYSQL_STATUS= "Running";
        }

        return $MYSQL_STATUS;
    }

    #########################################
    #  This gets the Environment Variables  #
    #########################################
    function get_env_variables(&$env_info)
    {
        $env_info = null;
        if ($this->switches['Show_Env_Var']['enabled'])
        {
            $id=0;
            ksort($_SERVER);
            reset($_SERVER);
            foreach ($_SERVER as $name => $value)
            {
                $array_var = explode(";", "$value");
                $value =implode("; ",$array_var);
                $env_info[$id]['name']=trim($name);
                $env_info[$id]['value']=trim($value);
                $id++;
            }
            $return = true;
        }
        else
        {
            $env_info['status'][] = "This feature has been switched off.";
            $env_info['status'][] = "Try enabling the Switch to use this function.";
            $return = false;
        }

        return $return;
    }

    #########################################
    #   Current Config_Local Information.   #
    #########################################
    function get_current_db_config_info()
    {
        global $release_version, $game_name;
        global $ADODB_SESSION_DRIVER;
        global $db_persistent;
        global $ADODB_SESSION_CONNECT, $dbport;
        global $ADODB_SESSION_DB;
        global $db_prefix;
        global $adminname;
        global $admin_mail;
        global $gamepath, $gamedomain, $ADOdbpath;

        $current_info['status'][]="// This is what you already have set in db_config.php.";
        $current_info['status'][]="// This will also tell you if what you have set in config_local.php is the same as what Setup Info has Auto Detected.";

        $current_info[] = array("caption" => 'Release Version', "value" => (strlen($release_version)>0) ? $release_version : "NOT SET or NOT Available in this Version");
        $current_info[] = array("caption" => 'Game Name', "value" => (strlen($game_name)>0) ? $game_name : "NOT SET or NOT Available in this Version");

        $current_info[] = array("caption" => 'Database Type', "value" => $ADODB_SESSION_DRIVER);
        $current_info[] = array("caption" => 'Connection Type', "value" => $db_persistent ? "Persistent Connection" : "Non Persistent Connection");
        $current_info[] = array("caption" => 'Database Server Address', "value" => ($dbport=="") ? "$ADODB_SESSION_CONNECT:3306":"$ADODB_SESSION_CONNECT");
        $current_info[] = array("caption" => 'Database Name', "value" => $ADODB_SESSION_DB);
        $current_info[] = array("caption" => 'Table Prefix', "value" => $db_prefix);
        $current_info[] = array("caption" => 'Admin Name', "value" => (strlen($adminname)>0) ? $adminname : "NOT SET or NOT Available in this Version");
        $current_info[] = array("caption" => 'Admin Email', "value" => str_replace("@"," AT ",$admin_mail));

        $current_info[] = "%SEPERATOR%";

        $game_path = $this->get_gamepath(true);
        if ($game_path['status'] != false)
        {
            $current_info[] = array("caption" => '$gamepath', "value" => $gamepath, "status" => (trim($gamepath) == trim($game_path['result']) ? "Correct" : "Incorrect") );
        }
        else
        {
            $current_info[] = array("caption" => '$gamepath', "value" => $game_path['info'], "status" => "Unknown" );
        }


        $game_domain = $this->get_gamedomain(true);
        if ($game_domain['status'] != false)
        {
            $current_info[] = array("caption" => '$gamedomain', "value" => $gamedomain, "status" => (trim($gamedomain) == trim($game_domain['result']) ? "Correct" : "Incorrect") );
        }
        else
        {
            $current_info[] = array("caption" => '$gamedomain', "value" => $game_domain['info'], "status" => "Unknown" );
        }
        $current_info[] = "%SEPERATOR%";

        $current_info[] = array("caption" => '$ADOdbpath', "value" => $ADOdbpath,"status" => ($this->validate_ADOdb_path()) ? "Correct":"Incorrect" );

        return $current_info;
    }

    #########################################
    #         Scheduler Information         #
    #########################################
    function get_scheduler_info()
    {
        global $sched_ticks, $sched_turns, $sched_ports, $sched_planets, $sched_igb;
        global $sched_ranking, $sched_news, $sched_degrade, $sched_apocalypse;

        $scheduler_info[] = array("name" => "Scheduler Ticks", "caption" => "The rate every update happens", "value" => "$sched_ticks Minutes");
        $scheduler_info[] = array("name" => "Scheduler Turns", "caption" => "Turns will happen every", "value" => "$sched_turns Minutes");
        $scheduler_info[] = array("name" => "Scheduler Ports", "caption" => "Ports will regenerate every", "value" => "$sched_ports Minutes");

        $scheduler_info[] = array("name" => "Scheduler Planets", "caption" => "Planets will generate production every", "value" => "$sched_planets Minutes");
        $scheduler_info[] = array("name" => "Scheduler IGB", "caption" => "Interests on IGB accounts will be accumulated every", "value" => "$sched_igb Minutes");
        $scheduler_info[] = array("name" => "Scheduler Rankings", "caption" => "Rankings will be generated every", "value" => "$sched_ranking Minutes");

        $scheduler_info[] = array("name" => "Scheduler News", "caption" => "News will be generated every", "value" => "$sched_news Minutes");
        $scheduler_info[] = array("name" => "Scheduler Rate", "caption" => "Sector Defences will degrade every", "value" => "$sched_degrade Minutes");
        $scheduler_info[] = array("name" => "Scheduler Apocalypse", "caption" => "The planetary apocalypse will occur every", "value" => "$sched_apocalypse Minutes");

        return $scheduler_info;
    }

    function get_switches()
    {
        for ($n = 0; $n < count($this->switches); $n++)
        {
            list($switch_name, $switch_array) = each($this->switches);
            $switch_info[$switch_name] = array("caption" => "{$switch_array['caption']}", "info" => "{$switch_array['info']}", "value" => "{$switch_array['enabled']}");
        }

        return $switch_info;
    }

    function get_server_software()
    {
        ##########################
        # Get System Information #
        ##########################
        if (function_exists('php_uname'))
        {
            $software_info[]['System'] = php_uname();
        }

        ########################
        # Get Operating System #
        ########################
        $var = $_SERVER['SERVER_SOFTWARE'];
        $Spos = strpos($var, "(")+1;
        $Epos = strpos($var, ")",(int)$Spos);

        if (is_integer(strpos($var, "Apache")))
        {
            $PlatOS = "Apache";
        }
        else
        {
            $PlatOS = "IIS";
        }

        ######################
        # Get Remote Address #
        ######################
        if (!empty($_SERVER['REMOTE_ADDR']))
        {
            $RemoteAddr = "{$_SERVER['REMOTE_ADDR']}";
        }

        ######################
        # Get Server Address #
        ######################
        if (!empty($_SERVER['SERVER_ADDR'])&&!empty($_SERVER['SERVER_PORT']))
        {
            $ServerAddr = "{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']}";
        }

        $_SERVER['SERVER_ADDR'] = ((empty($_SERVER['SERVER_ADDR'])) ? $_SERVER['LOCAL_ADDR'] : $_SERVER['SERVER_ADDR']);

        $ServerAddr = ((!empty($_SERVER['SERVER_ADDR'])) ? $_SERVER['SERVER_ADDR'] : $_SERVER['HTTP_HOST']).":{$_SERVER['SERVER_PORT']}";

        $software_info[]['Operating System'] = PHP_OS;
        $software_info[]['Platform System'] = $PlatOS;
        $software_info[]['Remote Address'] = $RemoteAddr;
        $software_info[]['Server Address'] = $ServerAddr;

        return $software_info;
    }

    function get_software_versions()
    {
        if (function_exists('zend_version'))
        {
            $software_info[]['zend_version'] = zend_version();
        }

        if (function_exists('apache_get_version'))
        {
            $software_info[]['apache_version'] = apache_get_version();
        }

        if (defined('PHP_VERSION'))
        {
            $software_info[]['php_version'] = PHP_VERSION;
        }

        #####################
        # Get PHP Interface #
        #####################
        if (function_exists('php_sapi_name'))
        {
            $sapi_type = php_sapi_name();
            if (preg_match ("/cgi/", $sapi_type))
            {
                $software_info[]['php_interface'] = "CGI PHP";
            }
            else if (preg_match ("/apache/", $sapi_type))
            {
                $software_info[]['php_interface'] = "mod_PHP";
            }
            else if (preg_match ("/isapi/", $sapi_type))
            {
                $software_info[]['php_interface'] = "ISAPI";
            }
            else
            {
                $software_info[]['php_interface'] = "Unknown ($sapi_type)";
            }
        }
        else
        {
            $PHP_Interface = "Unknown (Function Not found.)";
        }
        #####################

        if (!empty($PHP_Interface))
        {
            $software_info[]['php_interface'] = "$PHP_Interface";
        }

#        $ar = split("[/ ]",$_SERVER['SERVER_SOFTWARE']);
#        New version.
        $ar = preg_split("/[\s\/]+/",$_SERVER['SERVER_SOFTWARE']);

        for ($i=0;$i<(count($ar));$i++)
        {
            switch (strtoupper($ar[$i]))
            {
                case 'MOD_SSL':$i++;if (empty($MOD_SSL_VERSION)) $MOD_SSL_VERSION = $ar[$i];break;
                case 'OPENSSL':$i++;if (empty($OPENSSL_VERSION)) $OPENSSL_VERSION = $ar[$i];break;
                case 'MICROSOFT-IIS':$i++;if (empty($IIS_VERSION)) $IIS_VERSION = $ar[$i];break;
            }
        }

        if (!empty($MOD_SSL_VERSION))
        {
            $software_info[]['* mod_ssl Version'] = "$MOD_SSL_VERSION";
        }
        if (!empty($OPENSSL_VERSION))
        {
            $software_info[]['* OpenSSL Version'] = "$OPENSSL_VERSION";
        }
        if (!empty($IIS_VERSION))
        {
            $software_info[]['iis_version'] = "$IIS_VERSION";
        }

        $software_info[]['MySQL Server Version'] = (($this->switches['Enable_Database']['enabled']) ?$this->database_server_version : "Database tests disabled");

 // This my not be needed, but I will leave it here just in case we need it :)
//        $software_info[]['MySQL Client Version'] = $this->database_client_version;

        return $software_info;
    }

    function findinfile($filename,$pattern)
    {
        $result=false;
        if (isset($filename) && function_exists('file'))
        {
            $lines = file($filename);

            foreach ($lines as $line_num => $line)
            {
                if (preg_match("/\b$pattern\b/i", $line))
                {
                    $line = substr($line,strpos($line,$pattern));
                    list($fixname,$fixversion,$fixdate,$fixauthor) = preg_split("/[,]+/", $line, 4);

                    $result['version'] = "V$fixversion";
                    $result['date']    = "$fixdate";
                    $result['author']  = "$fixauthor";

                    unset($lines);
                    break;
                }
            }
        }

        return $result;
    }

    #########################################
    #     TRUE or FALSE Function.     #
    #########################################
    function SI_TRUEFALSE($truefalse,$Stat,$true,$false)
    {
        return(($truefalse == $Stat) ? $true : $false);
    }

    #########################################
    #       Display BNT Patch Status.       #
    #########################################
    function get_patch_info(&$patch_info)
    {
        if ($this->switches['Display_Patches']['enabled'])
        {
            ############################
            # Patch Settings Section   #
            ############################
            # Written by TheMightyDude #
            ############################

            #######################################
            # Register Glopbals Patch Lookup Info #
            #######################################
            $result=$this->findinfile("global_cleanups.php","reg_global_fix");
            $PATCH_INFO['global_funcs']['name']="Register Globals Fix";
            $PATCH_INFO['global_funcs']['patched']=$this->SI_TRUEFALSE($result,true,$result['version'],"Not Found");
            $PATCH_INFO['global_funcs']['info']="This is required if register_globals is disabled.";
            $PATCH_INFO['global_funcs']['author']=$result['author'];
            $PATCH_INFO['global_funcs']['date']=$result['date'];

            #######################################
            #    Planet Hack Patch Lookup Info    #
            #######################################
            $result=$this->findinfile("planet_report_ce.php","planet_hack_fix");
            $PATCH_INFO['planet-report-CE']['name']="Planet Hack Fix";
            $PATCH_INFO['planet-report-CE']['patched']=$this->SI_TRUEFALSE($result,true,$result['version'],"Not Found");
            $PATCH_INFO['planet-report-CE']['info']="This is required to stop 3rd party scripts from hacking planets.";
            $PATCH_INFO['planet-report-CE']['author']=$result['author'];
            $PATCH_INFO['planet-report-CE']['date']=$result['date'];

            #######################################
            #  Create Universe Patch Lookup Info  #
            #######################################
            $result=$this->findinfile("create_universe.php","create_universe_port_fix");
            $PATCH_INFO['create_universe']['name']="Create Universe Port Fix";
            $PATCH_INFO['create_universe']['patched']=$this->SI_TRUEFALSE($result,true,$result['version'],"Not Found");
            $PATCH_INFO['create_universe']['info']="This maybe required to fix some servers having problems creating all the ports.";
            $PATCH_INFO['create_universe']['author']=$result['author'];
            $PATCH_INFO['create_universe']['date']=$result['date'];

            foreach ($PATCH_INFO as $n => $s)
            {
                $patch_info[$n][0]= array("name" => $PATCH_INFO[$n]['name'], "info" => $PATCH_INFO[$n]['info'], "patched" => $PATCH_INFO[$n]['patched']);
                if ($PATCH_INFO[$n]['patched']!="Not Found")
                {
                    $patch_info[$n][1]=array("caption" => "Patch Information", "author" => $PATCH_INFO[$n]['author'],"created" => $PATCH_INFO[$n]['date']);
                }
            }
        }
    }

    ################################
    #       Test the Cookies       #
    ################################
    function testcookies()
    {
        global $gamepath, $gamedomain,$DoneRefresh,$_COOKIE,$_SESSION;
        $COOKIE_Info = null;

        if ($this->switches['Test_Cookie']['enabled'])
        {
            if (function_exists('session_start'))
            {
                @session_start();
                if (!isset($_SESSION["count"]) || is_null($_SESSION["count"]))
                {
                    $_SESSION['count'] = 0;
                    SetCookie ("TestCookie", "",0);
                    SetCookie ("TestCookie", "Shuzbutt",time()+3600,$gamepath, $gamedomain);
                    $header_location = ( @preg_match('/Microsoft|WebSTAR|Xitami/', $_SERVER["SERVER_SOFTWARE"]) ) ? 'Refresh: 0; URL=' : 'Location: ';
                    header($header_location . $this->append_sid($_SERVER["PHP_SELF"], false));
                    exit;
                }
                else
                {
                    $_SESSION['count'] = null;
                    unset($_SESSION["count"]);
                }
            }
            $this->cookie_test['enabled'] = true;

            if (isset($_COOKIE['TestCookie']))
            {
                $this->cookie_test['result'] = true;
            }
            else
            {
                $this->cookie_test['result'] = false;
                $this->cookie_test['status'] = "Please check your $"."gamepath and $"."gamedomain settings in config_local.php";
            }
        }
        else
        {
                $this->cookie_test['result'] = false;
                $this->cookie_test['enabled'] = false;
                $this->cookie_test['status'] = "Cookie Tests Disabled.";

        }
    }

    ##############################
    #  Used for refreshing Page. #
    ##############################
    function append_sid($url, $non_html_amp = false)
    {
        global $SID;

        if ( !empty($SID) && !strpos($url, 'sid='))
        {
            $url .= ( ( strpos($url, '?') != false ) ?  ( ( $non_html_amp ) ? '&' : '&amp;' ) : '?' ) . $SID;
        }

        return($url);
    }

    ##############################
    ##   Displaying Functions   ##
     ##############################

    ##############################
    #   Display Text Function.   #
    ##############################
    Function DisplayFlush($Text)
    {
        echo $Text; 
        //flush();
    }

    ##############################
    #    HTML Table Functions.   #
    ##############################
    Function do_Table_Title($title="Title",$Cols=2)
    {
        $this->DisplayFlush("<div align=\"center\">\n");
        $this->DisplayFlush("  <center>\n");
        $this->DisplayFlush("  <table border=\"0\" cellpadding=\"2\" cellspacing=\"1\" width=\"700\" bgcolor=\"#000000\">\n");
        $this->DisplayFlush("    <tr>\n");
        $this->DisplayFlush("      <td width=\"100%\" colspan=\"$Cols\" align=\"center\" bgcolor=\"#9999cc\">\n");
        $this->DisplayFlush("        <p align=\"center\"><strong><font color=\"#000000\">$title</font></strong></td>\n");
        $this->DisplayFlush("    </tr>\n");
    }

    ##############################
    #     Display Blank Row.     #
    ##############################
    Function do_Table_Blank_Row()
    {
        global $Cols;

        $Col_Str="colspan=\"".($Cols)."\"";
        $this->DisplayFlush("    <tr>\n");
        $this->DisplayFlush("      <td style=\"background-color:#9999cc; width:75%; height:1px; padding:0px;\" $Col_Str></td>\n");
        $this->DisplayFlush("    </tr>\n");
    }

    ##############################
    #     Display Single Row.    #
    ##############################
    Function do_Table_Single_Row($col1="Col1")
    {
        global $Cols;

        $Col_Str="colspan=\"".($Cols)."\"";
        $this->DisplayFlush("    <tr>\n");
        $this->DisplayFlush("      <td bgcolor=\"#C0C0C0\" width=\"100%\" align=\"left\" $Col_Str bgcolor=\"#C0C0C0\"><font size=\"1\" color=\"#000000\">$col1</font></td>\n");
        $this->DisplayFlush("    </tr>\n");
    }

    ##############################
    #     Display Table Row.     #
    ##############################
    Function do_Table_Row($col1="Col1",$col2="Col2",$status=false)
    {
        global $Cols, $Wrap;

        $Col_Str=''; $WrapStr=" nowrap";

        If ($Wrap==true) $WrapStr = '';
        if ($status==false)
        {
            if ($Cols==3) $Col_Str="colspan=\"".($Cols-1)."\"";
            $this->DisplayFlush("    <tr>\n");
            $this->DisplayFlush("      <td width=\"25%\" bgcolor=\"#ccccff\"$WrapStr align=\"left\" valign=\"top\"><font size=\"1\" color=\"#000\">$col1</font></td>\n");
            $this->DisplayFlush("      <td width=\"75%\" $Col_Str bgcolor=\"#C0C0C0\"$WrapStr align=\"left\" valign=\"top\"><font size=\"1\" color=\"#000\">$col2</font></td>\n");
            $this->DisplayFlush("    </tr>\n");
        }
        else
        {
            $this->DisplayFlush("    <tr>\n");
            $this->DisplayFlush("      <td width=\"25%\" bgcolor=\"#ccccff\"$WrapStr align=\"left\" valign=\"top\"><font size=\"1\" color=\"#000\">$col1</font></td>\n");
            $this->DisplayFlush("      <td width=\"65%\" bgcolor=\"#C0C0C0\"$WrapStr align=\"left\" valign=\"top\"><font size=\"1\" color=\"#000\">$col2</font></td>\n");
            $this->DisplayFlush("      <td width=\"10%\" bgcolor=\"#ccccff\" align=\"center\"$WrapStr valign=\"top\"><font size=\"1\" color=\"#000\"><strong>$status</strong></font></td>\n");
            $this->DisplayFlush("    </tr>\n");
        }
    }

    ##############################
    #    Display Table Footer.   #
    ##############################
    Function do_Table_Footer($endline="<br>")
    {
        global $Cols;

        $Col_Str="colspan=\"".($Cols)."\"";
        $this->DisplayFlush("    </tr>\n");
        $this->DisplayFlush("    <tr>\n");
        $this->DisplayFlush("      <td style=\"background-color:#9999cc; width:75%; height:4px; padding:0px;\" $Col_Str></td>\n");
        $this->DisplayFlush("    </tr>\n");
        $this->DisplayFlush("  </table>\n");
        $this->DisplayFlush("  </center>\n");
        $this->DisplayFlush("</div>\n");
        $this->DisplayFlush("$endline\n");
    }

}

?>

