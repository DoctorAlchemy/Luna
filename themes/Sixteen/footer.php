<?php

/*
 * Copyright (C) 2013-2015 Luna
 * Based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * Based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * Licensed under GPLv3 (http://getluna.org/license.php)
 */

// Make sure no one attempts to run this script "directly"
if (!defined('FORUM'))
	exit;

?>
		</div>
<?php
// If no footer style has been specified, we use the default (only copyright/debug info)
$footer_style = isset($footer_style) ? $footer_style : NULL;

// Generate the feed links
if ($footer_style == 'index') {
	$feed_lang = ($luna_config['o_feed_type'] == '1') ? __('RSS active thread feed', 'luna') : __('Atom active thread feed', 'luna');
	$feed_id = '';
} elseif ($footer_style == 'viewforum') {
	$feed_lang = ($luna_config['o_feed_type'] == '1') ? __('RSS forum feed', 'luna') : __('Atom forum feed', 'luna');
	$feed_id = '&fid='.$forum_id;
} elseif ($footer_style == 'thread') {
	$feed_lang = ($luna_config['o_feed_type'] == '1') ? __('RSS thread feed', 'luna') : __('Atom thread feed', 'luna');
	$feed_id = '&tid='.$id;
}

if ($luna_config['o_feed_type'] == 1)
	$feed_type = 'rss';
elseif ($luna_config['o_feed_type'] == 2)
	$feed_type = 'atom';

if (($luna_config['o_feed_type'] == 1 || $luna_config['o_feed_type'] == 2) && (isset($footer_style)))
	'<span><a href="extern.php?action=feed&type='.$feed_type.$feed_id.'">'.$feed_lang.'</a></span>'."\n";

?>
			</div>
			<footer class="footer">
				<div class="container">
					<?php if ($luna_config['o_board_statistics'] == 1): ?>
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
									<div class="statistic-item"><?php echo _n( 'User', 'Users', get_total_users(), 'luna' ) ?>: <strong><?php total_users(); ?></strong></div>
								</div>
								<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
									<div class="statistic-item"><?php echo _n( 'Thread', 'Threads', get_total_threads(), 'luna' ) ?>: <strong><?php total_threads(); ?></strong></div>
								</div>
								<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
									<div class="statistic-item"><?php echo _n( 'Comment', 'Comments', get_total_comments(), 'luna' ) ?>: <strong><?php total_comments(); ?></strong></div>
								</div>
								<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
									<div class="statistic-item"><?php _e('Newest user', 'luna') ?>: <strong><?php newest_user(); ?></strong></div>
								</div>
								<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
									<div class="statistic-item">
												<?php if ($luna_config['o_users_online']) { ?>
												<div class="dropup">
													<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
														<?php echo _n('User online', 'Users online', num_users_online(), 'luna') ?>: <strong><?php users_online(); ?></strong> <span class="fa fa-fw fa-angle-up hide-if-no-js"></span>
														<span class="sr-only">Toggle Dropdown</span>
													</a>
													<ul class="dropdown-menu" role="menu">
														<?php echo online_list() ?>
													</ul>
												</div>
												<?php } else {
													echo _n('User online', 'Users online', num_users_online(), 'luna').' <strong>'.users_online().'</strong>';  } ?></div>
								</div>
								<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
									<div class="statistic-item"><?php _e('Guests online', 'luna') ?>: <strong><?php guests_online(); ?></strong></div>
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<div class="copyright">
					<div class="container">
						<hr />
						<div class="row">
							<div class="col-sm-5 col-xs-12">
<?php
	if ($luna_config['o_show_copyright'] == '1') {
		if ($luna_config['o_copyright_type'] == '0')
			echo __('Copyright', 'luna').' &copy; '.date('Y').' &middot; '.$luna_config['o_board_title'];
		if ($luna_config['o_copyright_type'] == '1')
			echo $luna_config['o_custom_copyright'];
	}
?>
							</div>
							<div class="col-sm-2 col-xs-12"><?php if ($luna_config['o_back_to_top'] == '1'): ?><div class="text-center"><a href="#"><span class="fa fa-fw fa-chevron-up"></span></a></div><?php endif; ?></div>
							<div class="col-sm-5 col-xs-12"><span class="pull-right" id="poweredby"><?php printf(__('Powered by %s', 'luna'), ' <a href="http://getluna.org/">Luna '.$luna_config['o_cur_version'].'</a>') ?></span></div>
						</div>
					</div>
				</div>
			</footer>
		</div>
<?php if (($luna_config['o_cookie_bar'] == 1) && ($luna_user['is_guest']) && (!isset($_COOKIE['LunaCookieBar']))) { ?>
		<div class="navbar navbar-inverse navbar-fixed-bottom cookie-bar">
			<div class="container">
				<p class="navbar-text"><?php _e('We use cookies to give you the best experience on this board.', 'luna') ?></p>
				<form class="navbar-form navbar-right">
					<div class="form-group">
						<div class="btn-toolbar"><a class="btn btn-link" href="<?php echo $luna_config['o_cookie_bar_url'] ?>"><?php _e('More info', 'luna') ?></a><a class="btn btn-default" href="index.php?action=disable_cookiebar"><?php _e('Don\'t show again', 'luna') ?></a></div>
					</div>
				</form>
			</div>
		</div>
		<style>
			body { margin-bottom: 40px; }
			@media screen and (max-width: 767px) { body { margin-bottom: 80px; } }
		</style>
<?php
}

// Display debug info (if enabled/defined)
if (defined('LUNA_DEBUG')) {
	echo '<p id="debug">[ ';

	// Calculate script generation time
	$time_diff = sprintf('%.3f', get_microtime() - $luna_start);
	echo sprintf(__('Generated in %1$s seconds, %2$s queries executed', 'luna'), $time_diff, $db->get_num_queries());

	if (function_exists('memory_get_usage')) {
		echo ' - '.sprintf(__('Memory usage: %1$s', 'luna'), file_size(memory_get_usage()));

		if (function_exists('memory_get_peak_usage'))
			echo ' '.sprintf(__('(Peak: %1$s)', 'luna'), file_size(memory_get_peak_usage()));
	}

	echo ' ]</p>'."\n";
}


// End the transaction
$db->end_transaction();

// Display executed queries (if enabled)
if (defined('LUNA_SHOW_QUERIES'))
	display_saved_queries();


// Close the db connection (and free up any result data)
$db->close();

require ('footer.php');
?>
	</body>
</html>