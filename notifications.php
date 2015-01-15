<?php

/*
 * Copyright (C) 2013-2015 Luna
 * Based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * Based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * Licensed under GPLv3 (http://modernbb.be/license.php)
 */

define('FORUM_ROOT', dirname(__FILE__).'/');
require FORUM_ROOT.'include/common.php';

// Load the me functions script
require FORUM_ROOT.'include/me_functions.php';

// Include UTF-8 function
require FORUM_ROOT.'include/utf8/substr_replace.php';
require FORUM_ROOT.'include/utf8/ucwords.php'; // utf8_ucwords needs utf8_substr_replace
require FORUM_ROOT.'include/utf8/strcasecmp.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;
$type = isset($_GET['type']) ? $_GET['type'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 2)
	message($lang['Bad request'], false, '404 Not Found');

$result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.facebook, u.msn, u.twitter, u.google, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.date_format, u.time_format, u.last_visit, u.color, g.g_id, g.g_user_title, g.g_moderator FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result))
	message($lang['Bad request'], false, '404 Not Found');

$user = $db->fetch_assoc($result);

$user_username = luna_htmlspecialchars($user['username']);
$avatar_field = generate_avatar_markup($id);
$avatar_user_card = draw_user_avatar($id, 'visible-lg-block');

if ($action == 'newnoti') {
	if ($type == 'windows') {
		new_notification('2', 'index.php', 'Windows 8.1 is recent', 'fa-windows');
	} elseif ($type == 'comment') {
		new_notification('2', 'index.php', 'Someone made a comment on your topic', 'fa-comment');
	} elseif ($type == 'check') {
		new_notification('2', 'index.php', 'Check this out', 'fa-check');
	} elseif ($type == 'version') {
		new_notification('2', 'index.php', 'You are using Luna '.$luna_config['o_core_version'].'! Awesome!', 'fa-moon-o');
	} elseif ($type == 'cogs') {
		new_notification('2', 'index.php', 'This icon usualy indicates settings, not now through...', 'fa-cogs');
	}

	redirect('notifications.php?id='.$id);
} else if ($action == 'readnoti') {
	$db->query('UPDATE '.$db->prefix.'notifications SET viewed = 1 WHERE user_id = '.$id.' AND viewed = 0') or error('Unable to update the notification status', __FILE__, __LINE__, $db->error());
	confirm_referrer('me.php');

	redirect('notifications.php?id='.$id);
} else if ($action == 'delnoti') {
	$db->query('DELETE FROM '.$db->prefix.'notifications WHERE viewed = 1') or error('Unable to remove notifications', __FILE__, __LINE__, $db->error());
	confirm_referrer('me.php');

	redirect('notifications.php?id='.$id);
}

$page_title = array(luna_htmlspecialchars($luna_config['o_board_title']).' / '.$lang['Profile']);
define('FORUM_ACTIVE_PAGE', 'me');
require load_page('header.php');

require load_page('notifications.php');

require load_page('footer.php');