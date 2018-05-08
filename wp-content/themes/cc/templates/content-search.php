<li class="result">
	<?php 
		$post_type = get_post_type( get_the_ID());
		switch($post_type){
			case 'tribe_events':
				$post_name = "Event";
				break;
			case 'tribe_venue':
				$post_name = "Event Venue";
				break;
			case 'page':
				$post_name = "Page";
				break;
			case 'post':
				$post_name = "Blog";
				break;

		}
	?>
	<span class="post-type"><?php echo $post_name; ?></span>
   <a class="" href="<?php the_permalink(); ?>"><h3><?php the_title(); ?></h3></a>
   <p>
   	<?php the_excerpt(); ?>
   </p>
</li>   