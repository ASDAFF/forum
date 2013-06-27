<?php

/**
 * Специальный файл для обеспечения связки аккаунтов на форуме и на
 * основном сайте РосЯмы.
 * @author Дмитрий Никифоров (Dmitry Nikiforov) <axshavan@yandex.ru>
 */

// небольшой кусок дефолтного кода из phpBB
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);

// если не авторизован - отправляем авторизоваться
if(!$user->data['is_registered'])
{
	login_box(request_var('redirect', "rosyama.$phpEx"), '', '', false, true, true);
}

// проверка реферера
$referer = explode('?', $_SERVER['HTTP_REFERER']);
$referer = explode('/', $referer[0]);
$referer = $referer[2];
if
(
	$referer == $_SERVER['HTTP_HOST']
	|| $referer == 'rosyama.ru'
	|| $referer == 'dev.rosyama.ru'
	|| $referer == 'rosyama.other.local' // локальная отладочная площадка
	// || $referer == '' // добавить или изменить по своему усмотрению
)
{
	$redirect = explode('.', $_SERVER['HTTP_HOST']);
	unset($redirect[0]);
	if(!isset($_GET['rosyamauserid']))
	{
		$redirect = 'http://'.implode('.', $redirect).'/userGroups/?service=forum&uid='.(int)$user->data['user_id'].'&secretkey='.htmlspecialchars($_GET['secretkey']);
		$db->sql_freeresult($db->sql_query("update `".$table_prefix."users` set `rosyama_secretkey` = '".addslashes($_GET['secretkey'])."' where `user_id` = '".(int)$user->data['user_id']."'"));
		echo '<script type="text/javascript">document.location="'.$redirect.'";</script>';
	}
	else
	{
		$result = $db->sql_query("select `rosyama_secretkey` from `".$table_prefix."users` where `user_id` = '".(int)$user->data['user_id']."'");
		$row    = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if($row['rosyama_secretkey'] == $_GET['secretkey'])
		{
			$db->sql_freeresult($db->sql_query("update `".$table_prefix."users` set `rosyama_secretkey` = '', `rosyama_user_id` = '".(int)$_GET['rosyamauserid']."' where `user_id` = '".(int)$user->data['user_id']."'"));
			$redirect = 'http://'.implode('.', $redirect).'/userGroups/?service=forum&finished&secretkey='.htmlspecialchars($_GET['secretkey']);
			echo '<script type="text/javascript">document.location="'.$redirect.'";</script>';
		}
	}
	echo 'Если вы видите эту надпись, значит, что-то пошло не так.';
}
else
{
	redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
}

?>
