<?php

/*
 * Copyright (C) 2013-2015 Luna
 * Based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * Based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * Licensed under GPLv3 (http://getluna.org/license.php)
 */

if (isset($_GET['action']))
	define('FORUM_QUIET_VISIT', 1);

define('FORUM_ROOT', dirname(__FILE__).'/');
require FORUM_ROOT.'include/common.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;

$cur_index = 1;

if ($action == 'markread') {
	if ($luna_user['is_guest'])
		message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

	$db->query('UPDATE '.$db->prefix.'users SET last_visit='.$luna_user['logged'].' WHERE id='.$luna_user['id']) or error('Unable to update user last visit data', __FILE__, __LINE__, $db->error());

	// Reset tracked topics
	set_tracked_topics(null);

	redirect('index.php');
}

// Mark the topics/posts in a forum as read?
elseif ($action == 'markforumread') {
	if ($luna_user['is_guest'])
		message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

	$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
	if ($fid < 1)
		message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

	$tracked_topics = get_tracked_topics();
	$tracked_topics['forums'][$fid] = time();
	set_tracked_topics($tracked_topics);

	redirect('viewforum.php?id='.$fid);
} elseif (isset($_GET['email'])) {
	if ($luna_user['is_guest'] || $luna_user['g_send_email'] == '0')
		message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

	$recipient_id = intval($_GET['email']);
	if ($recipient_id < 2)
		message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

	$result = $db->query('SELECT username, email, email_setting FROM '.$db->prefix.'users WHERE id='.$recipient_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))
		message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

	list($recipient, $recipient_email, $email_setting) = $db->fetch_row($result);

	if ($email_setting == 2 && !$luna_user['is_admmod'])
		message(__('The user you are trying to send an email to has disabled form email.', 'luna'));


	if (isset($_POST['form_sent'])) {
		confirm_referrer('misc.php');

		// Clean up message and subject from POST
		$subject = luna_trim($_POST['req_subject']);
		$message = luna_trim($_POST['req_message']);

		if ($subject == '')
			message(__('You must enter a subject.', 'luna'));
		elseif ($message == '')
			message(__('You must enter a message.', 'luna'));
		// Here we use strlen() not luna_strlen() as we want to limit the post to FORUM_MAX_POSTSIZE bytes, not characters
		elseif (strlen($message) > FORUM_MAX_POSTSIZE)
			message(__('Messages cannot be longer than 65535 characters (64 KB).', 'luna'));

		if ($luna_user['last_email_sent'] != '' && (time() - $luna_user['last_email_sent']) < $luna_user['g_email_flood'] && (time() - $luna_user['last_email_sent']) >= 0)
			message(sprintf(__('At least %s seconds have to pass between sent emails. Please wait %s seconds and try sending again.', 'luna'), $luna_user['g_email_flood'], $luna_user['g_email_flood'] - (time() - $luna_user['last_email_sent'])));

		// Load the "form email" template
		$mail_tpl = trim(__('Subject: <mail_subject>

<sender> from <board_title> has sent you a message. You can reply to <sender> by replying to this email.

The message reads as follows:
-----------------------------------------------------------------------

<mail_message>

-----------------------------------------------------------------------

--
<board_mailer> Mailer', 'luna'));

		// The first row contains the subject
		$first_crlf = strpos($mail_tpl, "\n");
		$mail_subject = luna_trim(substr($mail_tpl, 8, $first_crlf-8));
		$mail_message = luna_trim(substr($mail_tpl, $first_crlf));

		$mail_subject = str_replace('<mail_subject>', $subject, $mail_subject);
		$mail_message = str_replace('<sender>', $luna_user['username'], $mail_message);
		$mail_message = str_replace('<board_title>', $luna_config['o_board_title'], $mail_message);
		$mail_message = str_replace('<mail_message>', $message, $mail_message);
		$mail_message = str_replace('<board_mailer>', $luna_config['o_board_title'], $mail_message);

		require_once FORUM_ROOT.'include/email.php';

		luna_mail($recipient_email, $mail_subject, $mail_message, $luna_user['email'], $luna_user['username']);

		$db->query('UPDATE '.$db->prefix.'users SET last_email_sent='.time().' WHERE id='.$luna_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());

		// Try to determine if the data in redirect_url is valid (if not, we redirect to index.php after login)
		$redirect_url = validate_redirect($_POST['redirect_url'], 'index.php');

		redirect(luna_htmlspecialchars($redirect_url));
	}


	// Try to determine if the data in HTTP_REFERER is valid (if not, we redirect to the user's profile after the email is sent)
	if (!empty($_SERVER['HTTP_REFERER']))
		$redirect_url = validate_redirect($_SERVER['HTTP_REFERER'], null);

	if (!isset($redirect_url))
		$redirect_url = get_base_url(true).'/profile.php?id='.$recipient_id;
	elseif (preg_match('%viewtopic\.php\?pid=(\d+)$%', $redirect_url, $matches))
		$redirect_url .= '#p'.$matches[1];

	$page_title = array(luna_htmlspecialchars($luna_config['o_board_title']), __('Send email to', 'luna').' '.luna_htmlspecialchars($recipient));
	$required_fields = array('req_subject' => __('Subject', 'luna'), 'req_message' => __('Message', 'luna'));
	$focus_element = array('email', 'req_subject');
	define('FORUM_ACTIVE_PAGE', 'misc');
	require load_page('header.php');

	require load_page('mail.php');

	require load_page('footer.php');
} elseif (isset($_GET['report'])) {
	if ($luna_user['is_guest'])
		message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

	$post_id = intval($_GET['report']);
	if ($post_id < 1)
		message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

	if (isset($_POST['form_sent'])) {
		// Make sure they got here from the site
		confirm_referrer('misc.php');

		// Clean up reason from POST
		$reason = luna_linebreaks(luna_trim($_POST['req_reason']));
		if ($reason == '')
			message(__('You must enter a reason.', 'luna'));
		elseif (strlen($reason) > 65535) // TEXT field can only hold 65535 bytes
			message(__('Your message must be under 65535 bytes (~64kb).', 'luna'));

		if ($luna_user['last_report_sent'] != '' && (time() - $luna_user['last_report_sent']) < $luna_user['g_report_flood'] && (time() - $luna_user['last_report_sent']) >= 0)
			message(sprintf(__('At least %s seconds have to pass between reports. Please wait %s seconds and try sending again.', 'luna'), $luna_user['g_report_flood'], $luna_user['g_report_flood'] - (time() - $luna_user['last_report_sent'])));

		// Get the topic ID
		$result = $db->query('SELECT topic_id FROM '.$db->prefix.'posts WHERE id='.$post_id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

		$topic_id = $db->result($result);

		// Get the subject and forum ID
		$result = $db->query('SELECT subject, forum_id FROM '.$db->prefix.'topics WHERE id='.$topic_id) or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

		list($subject, $forum_id) = $db->fetch_row($result);
		define('MARKED', '1');

		// Should we use the internal report handling?
		if ($luna_config['o_report_method'] == '0' || $luna_config['o_report_method'] == '2')
			$db->query('INSERT INTO '.$db->prefix.'reports (post_id, topic_id, forum_id, reported_by, created, message) VALUES('.$post_id.', '.$topic_id.', '.$forum_id.', '.$luna_user['id'].', '.time().', \''.$db->escape($reason).'\')' ) or error('Unable to create report', __FILE__, __LINE__, $db->error());
			$db->query('UPDATE '.$db->prefix.'posts SET marked = 1 WHERE id='.$post_id) or error('Unable to create report', __FILE__, __LINE__, $db->error());

		// Should we email the report?
		if ($luna_config['o_report_method'] == '1' || $luna_config['o_report_method'] == '2') {
			// We send it to the complete mailing-list in one swoop
			if ($luna_config['o_mailing_list'] != '') {
				// Load the "new report" template
				$mail_tpl = trim(__('Subject: Report(<forum_id>) - "<topic_subject>"

User "<username>" has reported the following message: <post_url>

Reason: <reason>

--
<board_mailer> Mailer
(Do not reply to this message)', 'luna'));

				// The first row contains the subject
				$first_crlf = strpos($mail_tpl, "\n");
				$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
				$mail_message = trim(substr($mail_tpl, $first_crlf));

				$mail_subject = str_replace('<forum_id>', $forum_id, $mail_subject);
				$mail_subject = str_replace('<topic_subject>', $subject, $mail_subject);
				$mail_message = str_replace('<username>', $luna_user['username'], $mail_message);
				$mail_message = str_replace('<post_url>', get_base_url().'/viewtopic.php?pid='.$post_id.'#p'.$post_id, $mail_message);
				$mail_message = str_replace('<reason>', $reason, $mail_message);
				$mail_message = str_replace('<board_mailer>', $luna_config['o_board_title'], $mail_message);

				require FORUM_ROOT.'include/email.php';

				luna_mail($luna_config['o_mailing_list'], $mail_subject, $mail_message);
			}
		}

		$db->query('UPDATE '.$db->prefix.'users SET last_report_sent='.time().' WHERE id='.$luna_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());

		redirect('viewforum.php?id='.$forum_id);
	}

	// Fetch some info about the post, the topic and the forum
	$result = $db->query('SELECT f.id AS fid, f.forum_name, t.id AS tid, t.subject FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$luna_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$post_id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))
		message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

	$cur_post = $db->fetch_assoc($result);

	if ($luna_config['o_censoring'] == '1')
		$cur_post['subject'] = censor_words($cur_post['subject']);

	$page_title = array(luna_htmlspecialchars($luna_config['o_board_title']), __('Report post', 'luna'));
	$required_fields = array('req_reason' => __('Reason', 'luna'));
	$focus_element = array('report', 'req_reason');
	define('FORUM_ACTIVE_PAGE', 'misc');
	require load_page('header.php');

	require load_page('report.php');

	require load_page('footer.php');
} elseif (isset($_GET['answer'])) {
	if ($luna_user['is_guest'])
		message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

	$topic_id = intval($_GET['tid']);
	$post_id = intval($_GET['answer']);
	if ($post_id < 1 || $topic_id < 1)
		message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

	if (isset($_POST['form_sent'])) {
		// Make sure they got here from the site
		confirm_referrer('misc.php');

		$db->query('UPDATE '.$db->prefix.'topics SET solved = '.$post_id.' WHERE id= '.$topic_id) or error('Unable to update solved post', __FILE__, __LINE__, $db->error());

		redirect('viewtopic.php?pid='.$post_id.'#p'.$post_id);
	}

	define('FORUM_ACTIVE_PAGE', 'misc');
	require load_page('header.php');

	require load_page('answer.php');

	require load_page('footer.php');
} elseif ($action == 'subscribe') {
	if ($luna_user['is_guest'])
		message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

	$topic_id = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
	$forum_id = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
	if ($topic_id < 1 && $forum_id < 1)
		message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

	if ($topic_id) {
		if ($luna_config['o_topic_subscriptions'] != '1')
			message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

		// Make sure the user can view the topic
		$result = $db->query('SELECT 1 FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id='.$luna_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$topic_id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

		$result = $db->query('SELECT 1 FROM '.$db->prefix.'topic_subscriptions WHERE user_id='.$luna_user['id'].' AND topic_id='.$topic_id) or error('Unable to fetch subscription info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
			message(__('You are subscribed', 'luna'));

		$db->query('INSERT INTO '.$db->prefix.'topic_subscriptions (user_id, topic_id) VALUES('.$luna_user['id'].' ,'.$topic_id.')') or error('Unable to add subscription', __FILE__, __LINE__, $db->error());

		redirect('viewtopic.php?id='.$topic_id);
	}

	if ($forum_id) {
		if ($luna_config['o_forum_subscriptions'] != '1')
			message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

		// Make sure the user can view the forum
		$result = $db->query('SELECT 1 FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$luna_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id='.$forum_id) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

		$result = $db->query('SELECT 1 FROM '.$db->prefix.'forum_subscriptions WHERE user_id='.$luna_user['id'].' AND forum_id='.$forum_id) or error('Unable to fetch subscription info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
			message(__('You\'re already subscribed to this forum.', 'luna'));

		$db->query('INSERT INTO '.$db->prefix.'forum_subscriptions (user_id, forum_id) VALUES('.$luna_user['id'].' ,'.$forum_id.')') or error('Unable to add subscription', __FILE__, __LINE__, $db->error());

		redirect('viewforum.php?id='.$forum_id);
	}
} elseif ($action == 'unsubscribe') {
	if ($luna_user['is_guest'])
		message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

	$topic_id = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
	$forum_id = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
	if ($topic_id < 1 && $forum_id < 1)
		message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');

	if ($topic_id) {
		if ($luna_config['o_topic_subscriptions'] != '1')
			message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

		$result = $db->query('SELECT 1 FROM '.$db->prefix.'topic_subscriptions WHERE user_id='.$luna_user['id'].' AND topic_id='.$topic_id) or error('Unable to fetch subscription info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message(__('You\'re not subscribed to this topic.', 'luna'));

		$db->query('DELETE FROM '.$db->prefix.'topic_subscriptions WHERE user_id='.$luna_user['id'].' AND topic_id='.$topic_id) or error('Unable to remove subscription', __FILE__, __LINE__, $db->error());

		redirect('viewtopic.php?id='.$topic_id);
	}

	if ($forum_id) {
		if ($luna_config['o_forum_subscriptions'] != '1')
			message(__('You do not have permission to access this page.', 'luna'), false, '403 Forbidden');

		$result = $db->query('SELECT 1 FROM '.$db->prefix.'forum_subscriptions WHERE user_id='.$luna_user['id'].' AND forum_id='.$forum_id) or error('Unable to fetch subscription info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message(__('You\'re not subscribed to this topic.', 'luna'));

		$db->query('DELETE FROM '.$db->prefix.'forum_subscriptions WHERE user_id='.$luna_user['id'].' AND forum_id='.$forum_id) or error('Unable to remove subscription', __FILE__, __LINE__, $db->error());

		redirect('viewforum.php?id='.$forum_id);
	}
} else
	message(__('Bad request. The link you followed is incorrect, outdated or you are simply not allowed to hang around here.', 'luna'), false, '404 Not Found');