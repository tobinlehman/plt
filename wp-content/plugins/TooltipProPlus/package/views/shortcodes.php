<?php
$shortcodes			 = $currentPlugin->getOption( 'plugin-shortcodes', array() );
$shortcodes_action	 = $currentPlugin->getOption( 'plugin-shortcodes-action' );
if ( empty( $shortcodes ) && empty( $shortcodes_action ) ) {
	return;
}
?>

<section id="" class="cm">
	<div class="box padding">
		<div  class="postbox">
			<h3>
				<span>Available Shortcodes</span>
				<?php if ( $this->getUserguideUrl() ): ?>
					<strong class="label-title-link"> <a class="label-title-link-class"  target="_blank" href="<?php echo $this->getUserguideUrl(); ?>">View Plugin Documentation >></a></strong>
				<?php endif; ?>
			</h3>
			<div class="inside">
				<?php echo $shortcodes; ?>
				<?php
				if ( !empty( $shortcodes_action ) ) {
					echo do_action( $shortcodes_action );
				}
				?>
			</div>
		</div>
	</div>
</section>