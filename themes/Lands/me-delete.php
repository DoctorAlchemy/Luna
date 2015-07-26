<?php

// Make sure no one attempts to run this view directly.
if (!defined('FORUM'))
	exit;

?>

<form id="confirm_del_user" method="post" action="settings.php?id=<?php echo $id ?>">
	<fieldset>
		<div class="panel panel-danger">
			<div class="panel-heading">
				<h3 class="panel-title"><?php _e('Please confirm that you want to delete the user', 'luna').' <strong>'.luna_htmlspecialchars($username).'</strong>' ?><span class="pull-right"><input type="submit" class="btn btn-danger" name="delete_user_comply" value="<?php _e('Delete', 'luna') ?>" /></span></h3>
			</div>
			<div class="panel-body">
				<?php _e('Warning! Deleted users and/or posts cannot be restored. If you choose not to delete the posts made by this user, the posts can only be deleted manually at a later time.', 'luna') ?>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="delete_posts" value="1" checked />
						<?php _e('Delete any posts and topics this user has made', 'luna') ?>
					</label>
				</div>
			</div>
		</div>
	</fieldset>
</form>
<?php

	require load_page('footer.php');