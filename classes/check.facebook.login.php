<?
echo "there";
	function __autoload($class_name) 
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/classes/' . strtolower($class_name) . '.php';
	}
	$user = new user();
	$facebook = new facebook(array(
	  'appId'  => FB_ID,
	  'secret' => FB_SECRET,
	));
	
	if ($user->isLogged())
	{
		$registerOrLoginNeeded = false;
		$extracted_name = $user->loggedUserFullName();
		$extracted_username = $user->loggedUserUsername();
		$extracted_facebook_indentification = $user->loggedUserID();
		$extracted_database_id = $user->loggedUserDatabaseId();
		$extracted_access_level = $user->loggedUserAccessLevel();
		$access_rights = $permissions->accessRights($extracted_database_id);
					######
	}
	else
	{
		$registerOrLoginNeeded = true;
	}

	
	?>