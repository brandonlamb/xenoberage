<?php
require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
class user
{
	private $_db;
	private $_isLogged;
	
	public function __construct()
	{
		if (!session_id())
		{
			session_start();
		}
		$this->connect = db::init();
		$this->_checkLogin();
	}
	
	public function isLogged() 
    {
        return $this->_isLogged;
    }
	public function loggedUserFullName() 
    {
        return $this->_isFullName;
    }
	public function loggedUserUsername() 
    {
        return $this->_isUsername;
    }
	
    
    public function login($username, $password)
    {
		$sth = $this->connect->prepare("SELECT * FROM  WHERE username = ?");
		$sth->execute(array($username));
		$result = $sth->fetch();
		
		if ($result)
		{
			if ($result['password'] == md5($password))
			{
				$this->_setLogin($result);
				return true;
			}
		}
		
		return false;
	}
	
	public function logout() 
	{
		session_destroy();
		setcookie ("XRLogin", "", time() - 3600, '/');
	}
	
	public function fbLogin($fbId)
	{
		$sth = $this->connect->prepare("SELECT * FROM  WHERE facebook_id = ?");
		$sth->execute(array($fbId));
		$result = $sth->fetch();
		
		if ($result)
		{
			$this->_setLogin($result);
			return true;
		}
		return false;
	}
	
	private function _setLogin($userData)
	{
		$_SESSION['logged'] = true;
		$_SESSION['user_id'] = $userData['user_id'];
		$_SESSION['username'] = $userData['username'];
		$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
		//$_SESSION['hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		//$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
		
		try {
			
			$sth = $this->connect->prepare("UPDATE SET ip = ? WHERE user_id = ?");
			$sth->execute(array($_SERVER['REMOTE_ADDR'], $userData['user_id']));
			
			$forCookie = array(
				'user_id' => $userData['user_id'],
				'ip' => $_SERVER['REMOTE_ADDR'],
				'username' => $_SESSION['username']
			);
			
			setcookie('XRLogin', serialize($forCookie), time()+60*60*24*30, '/');
			
		} catch (Exception $e) {
			die('Database error: ' . $e->getMessage());
		}
	}
	
    private function _checkLogin()
    {
		$shared_function = new shared();
		$this->_isLogged = false;
		$time_date_full = $shared_function->manage_time("full");
		if(isset($_SESSION['logged']) && $_SESSION['logged'])
        {
			$sth = $this->connect->query("SELECT * FROM WHERE user_id = " . $_SESSION['user_id']);
			$result = $sth->fetch();
			
			if ($result['ip'] == $_SESSION['ip'])
			{
				$this->_isLogged = true;
				$this->_isFullName =  $result['name'];
				$this->_isUsername = $result['username'];
				$this->_isIndentification = $result['facebook_id'];
				$this->_isDatabaseID = $result['user_id'];
				
			}
		}
		else if (isset($_COOKIE['XRLogin']) && $_COOKIE['XRLogin'])
		{
			$cookieData = unserialize(stripslashes($_COOKIE['XRLogin']));
			
			$sth = $this->connect->query("SELECT * FROM WHERE user_id = " . $cookieData['user_id']);
			$result = $sth->fetch();
			
			if ($result['ip'] == $cookieData['ip'])
			{
				$this->_isLogged = true;
				$this->_setLogin($result);
				$this->_isFullName =  $result['name'];
				$this->_isUsername = $result['username'];
				$this->_isIndentification = $result['facebook_id'];
				$this->_isDatabaseID = $result['user_id'];
			}
		}
		if($this->_isDatabaseID > 0)
		{
					$sth = $this->connect->prepare("UPDATE SET last_activity = ? WHERE user_id = ?");
					if ($sth->execute(array($time_date_full, $this->_isDatabaseID)))
					{
						//update successful
					}
					else
					{
						//user not logged in
					}
		}
	}
}
