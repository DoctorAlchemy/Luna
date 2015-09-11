<?php

// Make sure no one attempts to run this view directly.
if (!defined('FORUM'))
	exit;

?>

<form id="search" method="get" action="search.php?section=advanced">
	<h2 class="profile-title"><?php _e('Search', 'luna') ?><span class="btn-group pull-right"><button class="btn btn-primary" type="submit" name="search" accesskey="s"><span class="fa fa-fw fa-search"></span> <?php _e('Search', 'luna') ?></button></span></h2>
	<div class="panel panel-default">
		<div class="panel-body">
			<fieldset class="form-inline">
				<input type="hidden"  name="action" value="search" />
				<input placeholder="<?php _e('Keyword', 'luna') ?>" class="form-control" type="text" name="keywords" maxlength="100" />
				<input placeholder="<?php _e('Author', 'luna') ?>"  class="form-control" id="author" type="text" name="author" maxlength="25" />
				<select class="form-control" id="search_in" name="search_in">
					<option value="0"><?php _e('Message text and topic subject', 'luna') ?></option>
					<option value="1"><?php _e('Message text only', 'luna') ?></option>
					<option value="-1"><?php _e('Topic subject only', 'luna') ?></option>
				</select>
				<select class="form-control" name="sort_by">
					<option value="0"><?php _e('Post time', 'luna') ?></option>
					<option value="1"><?php _e('Author', 'luna') ?></option>
					<option value="2"><?php _e('Subject', 'luna') ?></option>
					<option value="3"><?php _e('Forum', 'luna') ?></option>
				</select>
				<select class="form-control" name="sort_dir">
					<option value="DESC"><?php _e('Descending', 'luna') ?></option>
					<option value="ASC"><?php _e('Ascending', 'luna') ?></option>
				</select>
				<select class="form-control" name="show_as">
					<option value="topics"><?php _e('Topics', 'luna') ?></option>
					<option value="posts"><?php _e('Posts', 'luna') ?></option>
				</select>
			</fieldset>
			<fieldset>
				<div class="row">
					<?php echo draw_search_forum_list(); ?>
				</div>
			</fieldset>
		</div>
	</div>
</form>