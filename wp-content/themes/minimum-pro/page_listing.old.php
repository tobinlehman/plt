<?php
/**
 * This file adds the Landing template to the Minimum Pro Theme.
 *
 * @author StudioPress
 * @package Minimum Pro
 * @subpackage Customizations
 */

/*
Template Name: Listing
*/

$grade_level = urldecode($wp_query->query_vars['grade_level']);


echo get_template_part('eunit', 'header');

do_action( 'genesis_before_content_sidebar_wrap' );


if($grade_level == ""){
	$args = array(
		'post_type'  => 'unit-topic',  
	    'order'      => 'DESC'
	);
}else{
	$args = array(
		'post_type'  => 'unit-topic',  
	    'order'      => 'DESC',
	    'grade-level'=> $grade_level
	);
}
					
	$query = new WP_Query($args); 

	$auth_url = "http://shop.plt.org/api/query/licenses";
	$cookie = new WP_Http_Cookie( 'JSESSIONID' );
	$cookie->name = 'JSESSIONID';
	$cookie->value = $_COOKIE['JSESSIONID'];

	$cookies[] = $cookie;
	$auth_args = array(
		'cookies' => $cookies
	);

	$auth_response = wp_remote_get($auth_url, $auth_args);
	$auth_body = wp_remote_retrieve_body($auth_response);
	$auth_headers = wp_remote_retrieve_headers($auth_response);
	$auth_response_code = wp_remote_retrieve_response_code($auth_response);
	$user_license_check = json_decode($auth_body)->{'user-licenses'};
	$user_license_bindings = json_decode($auth_body)->{'license-bindings'};


	
	if(empty($user_license_check)){
		$status = "sign-in";
	} else {
 	    $json = json_decode($auth_body);
 	    // var_dump($json);
 		$user_licenses_data = $json->{'user-licenses'}[0]->{'user-license-info'}->userlicenses;
 		foreach($user_licenses_data as $ul){
 			// var_dump($ul);
 			$user_licenses[] = array(
 				"id" => $ul->controlled,
 				"active" => $ul->active
 			);
 		}
	}
	
?>
	<div class="test" style="display:none;">
		<?php 
			var_dump($auth_args);
			var_dump($auth_url);
			var_dump($auth_response_code);
			var_dump($auth_headers);
			var_dump($status);
			echo "test";
		?>
		<!-- <h1>Test2</h1> -->
	</div>
	<div class="wrap">
		<ul class="unit-listing">
			<?php if ( $query->have_posts() ) : ?>
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>

					<?php 
						$args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all');
						$terms = wp_get_post_terms( $post->ID, 'grade-level', $args );

						
						$unit_id = get_field('license_id');

						$unit_ids = explode(", ", $unit_id); 

						if($status == "sign-in"){
							$unit_link = "http://shop.plt.org/~userInfo";
							$unit_text = "Sign <br>In";
							if($post->ID == 3339){
								$unit_text = "Coming Soon";
								$unit_link_tree = true;
							}
						} else {
							$unit_link = "http://shop.plt.org/browse?searchMode=advanced&context=-2&sortField=Order";
							$unit_text = "Buy Unit";
							if($post->ID == 3339){
								$unit_text = "Coming Soon";
								$unit_link = "http://shop.plt.org/browse?searchMode=advanced&context=-2&sortField=Order";
								$unit_link_tree = true;
							} else {
								foreach($user_licenses as $ulb){
									if(in_array($ulb['id'], $unit_ids) && $ulb['active'] == true){

										$unit_link = get_the_permalink();
										$unit_text = "View <br>E-Unit";
										$unit_link_tree = false;
										if($post->ID == 3339){
											$unit_link = "http://shop.plt.org/browse?searchMode=advanced&context=-2&sortField=Order";
											$unit_link_tree = true;
										}
										
									}
								}
							}
							
							
							
						}
						
					?>
					
					<li class="unit-<?php echo $terms[0]->slug; ?>-listing">
						<!--  -->
						<a>
							<?php the_post_thumbnail(); ?>
						</a>
						<div class="unit-info">
							<h3><?php the_title(); ?></h3>
							<h6>Grades <?php echo $terms[0]->name; ?></h6>
							<p class="description">
								<?php the_excerpt(); ?>
							</p>
							<div class="unit-icon">
								<img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/images/<?php echo $terms[0]->slug; ?>-icon.png">
							</div>
							<a target="_blank" data-tree="<?php echo $unit_link_tree; ?>" 
							<?php
								if($post->ID == 3339 && $unit_link_tree){
									
								} elseif(!$unit_link_tree){
									echo "href=$unit_link"; 
								}
								else{
									echo "href=$unit_link"; 
								}
							?>
								

								class="unit-cta-btn">
								<img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/images/right-arrow.png" alt="">
								<span class="text">
									<?php 
									
										echo $unit_text; 
									
									?>
								</span>
							</a>
						</div>
					</li>
					
				<?php endwhile; ?>
			<?php endif; ?>
		<?php wp_reset_postdata();?>
		
			
		</ul>
	</div>

<?php

get_template_part('eunit', 'footer');

