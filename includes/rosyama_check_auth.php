<?php

/**
 * Проверить авторизацию пользователя на РосЯме и авторизовать его,
 * если есть привязка аккаунтов.
 * @author Dmitry Nikiforov <axshavan@yandex.ru>
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

global $user, $auth;
$user->session_begin();
$auth->acl($user->data);

if($user->data['user_id'] > 1) // 1 == ANONYMOUS by default
{
	return;
}
if(isset($_COOKIE['dontcheckryauth']))
{
	return;
}
if(isset($_GET['rosyamaauth']))
{
	$redirect = preg_replace('/rosyamaauth\=([\d]+)/', '', $_SERVER['REQUEST_URI']);
	if($_GET['rosyamaauth'])
	{
		global $db, $table_prefix;
		$row = $db->sql_fetchrow($db->sql_query("select `user_id` from  `".$table_prefix."users` where `rosyama_user_id` = '".(int)$_GET['rosyamaauth']."'"));
		if(isset($_GET['secretkey']) && $_GET['secretkey'] == md5($_COOKIE['rysk2'].$row['user_id']))
		{
			$user->session_create($row['user_id'], 0, 1, 1);
		}
		else
		{
			setcookie('dontcheckryauth', 1, time() + 86400, '/');
		}
	}
	else
	{
		setcookie('dontcheckryauth', 1, time() + 86400, '/');
	}
	header("Location: ".$redirect);
	die();
}
else
{
	$secretkey = md5(time().mt_rand(0, 10000));
	$redirect  = explode('.', $_SERVER['HTTP_HOST']);
	unset($redirect[0]);
	$redirect  = 'http://'.implode('.', $redirect).'/profile/checkauth/?secretkey='.$secretkey;
	setcookie('rysk2', $secretkey, time() + 60, '/');
	echo '<script type="text/javascript">document.location="'.$redirect.'"</script>';
}

?>