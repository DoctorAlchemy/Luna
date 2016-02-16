<?php

// Make sure no one attempts to run this view directly.
if (!defined('FORUM'))
	exit;

?>
<div class="main container">
	<div class="row">
		<div class="col-xs-12">
			<div class="title-block title-block-primary">
				<h2><i class="fa fa-fw fa-search"></i> <?php _e('Search results', 'luna') ?></h2>
			</div>
			<div class="tab-content">
				<?php echo $paging_links ?>
				<?php draw_search_results(); ?>
			</div>
		</div>
	</div>
</div>