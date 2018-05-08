<?php get_header(); ?>

	<div class="container">
		<div class="row">
			<div class="col-md-12 no-p-m breadcrumb-container">
				<?php the_breadcrumbs(">"); ?>
			</div>
			<div class="col-md-9 no-p-m content">
				<?php if( have_posts() ) : while( have_posts() ): ?>
					<?php the_post(); ?>
						<?php the_content(); ?>
						<h1>
							Headline
						</h1>
						<p>
							Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam ligula velit, condimentum vel facilisis eu, mollis in diam. Mauris vestibulum, purus quis vehicula ultrices, erat nunc ultrices nulla, pretium lacinia mauris mi sit amet ligula.
						</p>
						<h2>
							Subhead
						</h2>
						<p>
							Sed sit amet turpis at turpis volutpat volutpat. Maecenas a pretium nibh. Aenean est neque, cursus ut lacinia eget, accumsan vel felis. Praesent vel lorem volutpat, tristique lectus at, mattis est. Nam fringilla accumsan neque eget.
						</p>
						<ul>
							<li>
								<a href="">
									Proin eu sapien tincidunt	
								</a>
							</li>
							<li>
								Proin eu sapien tincidunt	
							</li>
							<li>
								Proin eu sapien tincidunt	
							</li>
						</ul>
						<ol>
							<li>
								<a href="">
									Proin eu sapien tincidunt	
								</a>
							</li>
							<li>
								Proin eu sapien tincidunt	
							</li>
							<li>
								Proin eu sapien tincidunt	
							</li>
						</ol>
						<h3>
							Subhead 2
						</h3>
						<p>
							Sed sit amet turpis at turpis volutpat volutpat. Maecenas a pretium nibh. Aenean est neque, cursus ut lacinia eget
						</p>
						<h4>
							Subhead 3
						</h4>
						<p>
							Sed sit amet turpis at turpis volutpat volutpat. Maecenas a pretium nibh. Aenean est neque, cursus ut lacinia eget
						</p>
						<h5>
							Subhead 4
						</h5>
						<p>
							Sed sit amet turpis at turpis volutpat volutpat. Maecenas a pretium nibh. Aenean est neque, cursus ut lacinia eget
						</p>
						<h6>
							Subhead 5
						</h6>
						<p>
							Sed sit amet turpis at turpis volutpat volutpat. Maecenas a pretium nibh. Aenean est neque, cursus ut lacinia eget
						</p>
						<table>
							<tr>
								<th>Title</th>
								<th>Title</th>
								<th>Title</th>
								<th>Title</th>
								<th>Title</th>
							</tr>
							<tbody>
								<tr>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
								</tr>
								<tr>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
								</tr>
								<tr>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
									<td>
										Sed sit amet turpis at turpis volutpat 
									</td>
								</tr>
							</tbody>
						</table>

					<?php endwhile; ?>
				<?php endif; ?>
			</div>
			<div class="col-md-3 no-p-m">
				<?php get_template_part('templates/sidebar'); ?>
			</div>
				
		</div>
	</div>
		
<?php get_footer(); ?>
