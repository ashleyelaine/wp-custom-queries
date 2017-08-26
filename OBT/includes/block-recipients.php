<?php while ( $grants_loop->have_posts() ) : $grants_loop->the_post(); ?>
<div class="recipients-card masonry-grid-item">
	<div class="left_card">
		<h2><?php the_field('organization_name') ?></h2>
		<div class="city_state">
			<span class="city"><?php the_field('grantee_city') ?></span>, <span class="state"><?php the_field('grantee_state') ?></span>
		</div>
		<div class="purpose">
			<?php the_field('purpose') ?>
		</div>
	</div>
	<div class="right_card">
		<div class="grant_type"><?php the_field('grant_type') ?></div>
		<div class="grant_amount"><?php the_field('amount_awarded') ?></div>
		<div class="grant_info">
			<div class="ginfo grant_year">
				<span class="info_title">Year Approved:</span><span class="year_text gtext"> <?php the_field('grant_year') ?></span>
			</div>
			<div class="ginfo grant_region">
				<span class="info_title">Region Served:</span><span class="region_text gtext"> <?php the_field('grant_region') ?></span>
			</div>
			<div class="ginfo grant_category">
				<span class="info_title">Grant Category:</span><span class="category_text gtext"> <?php the_field('grant_category') ?></span>
			</div>
		</div>
	</div>
</div>
<?php endwhile; ?>
<?php wp_reset_postdata(); ?>
