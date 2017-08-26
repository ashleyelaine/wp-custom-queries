<?php
/**
 * The page template file for Recipients / Grants
 */

get_header(); ?>
<div class="recipients-page clearfix">
	<!-- PAGE TOP -->
	<div class="page-top">
		<div class="grid-row">
			<h1><?php echo get_field('header');?></h1>
		</div>
		<div class="grid-row">
			<h3><?php echo get_field('sub_header');?></h3>
		</div>
		<!-- FILTERS -->
		<div class="grant-filters">
			<!-- REGION SELECTOR -->
			<div class="grant-filter region">
				<label for="region">Region</label>
				<div class="select-style">
					<select name="region" id="region">
						<option value="">Region</option>
						<?php
						$terms = get_terms( array('taxonomy' => 'grant_region'));
						foreach ( $terms as $term ) {
								echo '<option value="'.$term->slug.'">' . $term->name . '</option>';
							}
						?>
					</select>
					<i class="fa fa-chevron-down" aria-hidden="true"></i>
				</div>
			</div>
			<!-- INVESTMENT TYPE SELECTOR -->
			<div class="grant-filter investment-type">
				<label for="investment-type">Investment Type</label>
				<div class="select-style">
					<select name="investment-type" id="investment-type">
						<option value="">Investment Type</option>
						<?php
						$terms = get_terms( array('taxonomy' => 'grant_type'));
						foreach ( $terms as $term ) {
								echo '<option value="'.$term->slug.'">' . $term->name . '</option>';
							}
						?>
					</select>
					<i class="fa fa-chevron-down" aria-hidden="true"></i>
				</div>
			</div>
			<!-- INVESTMENT CATEGORY SELECTOR -->
			<div class="grant-filter investment-category">
				<label for="investment-category">Investment Category</label>
				<div class="select-style">
					<select name="investment-category" id="investment-category">
						<option value="">Investment Category</option>
						<?php
						$terms = get_terms( array('taxonomy' => 'grant_category'));
						foreach ( $terms as $term ) {
								echo '<option value="'.$term->slug.'">' . $term->name . '</option>';
							}
						?>
					</select>
					<i class="fa fa-chevron-down" aria-hidden="true"></i>
				</div>
			</div>
			<!-- GRANT SEARCH -->
			<div class="grant-filter grant-search">
				<label for="grant-search">Grant Search</label>
				<input class="grant-search" name="search" title="Grant Search" type="search" id="grant-search" placeholder="Search" />
				<i class="fa fa-search" aria-hidden="true"></i>
			</div>
		</div>
	</div>

	<!-- RESULTS -->
	<div class="aboveResults">
		<div class="resultsContainer">
			<span class="resultsNumber">X,XXX</span> Results
		</div>
		<div class="sortBy">
			<label for="sort-by">Sort by</label>
			<div class="select-style">
				<select name="sort-by" id="sort-by">
					<option value="alphabetical">Alphabetical</option>
					<option value="mostRecent">Most Recent</option>
				</select>
				<i class="fa fa-chevron-down" aria-hidden="true"></i>
			</div>
		</div>
	</div>
	<?php if ( have_posts() ) : ?>
        <div id="grantResults" class="grant-recipients recipients-grid">
            <!-- AJAX CALL -->
        </div>
    <?php endif; ?>

	<div class="button" id="LoadMore"><span>Load More</span></div>
</div>

<script type="text/javascript">
jQuery( document ).ready( function( $ ) {
	/* RECIPIENTS GRID */
	$('.recipients-grid').masonry({
	  columnWidth: ".recipients-card",
	  gutter: 30,
	  itemSelector: '.recipients-card'
    });

	//DEFINE THE VARIABLES
    var pageID = 1;

    /* ON LOAD GET RECIPIENTS */
    getRecipients();

	/* ON LOAD MORE GET RECIPIENTS */
    $("#LoadMore").click(function(){
        pageID++;
        getRecipients();
    });

	/* ON CHANGE OF SELECT FIELDS */
	$('#region, #investment-type, #investment-category').on('change',function(){
		pageID=1;
		getRecipients();
	});

	/* SEARCH */
	var timeout = null;
	$('#grant-search').on('keyup', function(){
		clearTimeout(timeout);
		timeout = setTimeout(function(){
			pageID = 1;
			searchText = $('#grant-search').val();
			getRecipients();
			console.log(searchText);
		},400);

		/*setTimeout(function(){
		  if($('#filter-container').height() === 0) {
				$('.noSearchResults div').fadeIn();
			}
			else {
				$('.noSearchResults div').fadeOut();
			}
		}, 100);*/


	});

    //GET RECIPIENTS - AJAX
    function getRecipients() {
		getActiveRegion();
		getActiveType();
		getActiveCategory();
		getActiveSearch();
		if(pageID ==1){
			$("#grantResults").html("");
			$("#LoadMore").addClass('disabled');
			$("#LoadMore span").text('Loading Posts');
		}
		console.log('GET RECIPIENTS');
        $.ajax({
            url: "/wp-admin/admin-ajax.php",
            type: 'post',
            data: {
                action: 'getrecipients',
                page : pageID,
				region : filterRegion,
				type : filterType,
				category : filterCategory,
				s : searchText,
            },
			dataType: "JSON",
            complete: function(result) {
				console.log(result.responseText);
				resultJson = jQuery.parseJSON(result.responseText);
				console.log(resultJson);
				if(resultJson){
			        var recipients = jQuery(resultJson.pageHTML);
					$(".resultsNumber").html(resultJson.rowcount);
			        if(!resultJson.pageHTML){
			            console.log("No posts");
					  	$("#LoadMore").addClass('disabled');
					  	$("#LoadMore span").text('No More Posts');
			        }else{
				        //$("#grantResults").masonry('remove', $(".masonry-grid-item"));
					  	$("#LoadMore").removeClass('disabled');
						$("#LoadMore span").text('Load More');
				        $("#grantResults").append(recipients)
				        $("#grantResults").masonry('appended', recipients).masonry('layout');
						//STYLE CARD HELPERS
						$('.recipients-card').each(function(){
							var grantRows = $(this).find($('.innerGrantInfo .grantRowContain'));
							if(grantRows.length > 1) {
								if ($(this).find($('.innerGrantInfo .grantRowContain:nth-of-type(2) .grantTypeCard')).hasClass('pri')) {
									$(this).find($('.innerGrantInfo .grantRowContain:first-of-type .grantTypeCard.grant')).css('right','123px');
								}
							}
						});

						//HOVER DISPLAY CORRECT REGION AND PURPOSE
						$('.grantRow').hover(
							function() {
								$(this).parents('.innerGrantInfo').find('.grantRowContain').removeClass('active');
								$(this).parent('.grantRowContain').addClass('active');
							}, function() {
								$(this).parents('.innerGrantInfo').find('.grantRowContain').removeClass('active');
								$(this).parents('.innerGrantInfo').find('.grantRowContain:first-of-type').addClass('active');
							}
						);

						console.log(filterRegion);
						//$('#grantResults').addClass(filterRegion);
						if (filterRegion) {
							console.log('filter REGION');
							$('.grantRowContain').each(function(){
								if ($(this).hasClass(filterRegion)) {
									$(this).addClass('filtered-active');
								} else {
									$(this).remove();
									checkActiveGrantData()
								}
							});
							//REDO MASONRY
							$("#grantResults").masonry();
						}

						function checkActiveGrantData() {
							$('.grantRowContain:first-of-type').addClass('active');
						}
					}
				}
            }
        });
    }

	//SET & GET ACTIVE REGION
	function getActiveRegion() {
		var activeRegion = $('#region').val();
		filterRegion = activeRegion;
	}

	//SET & GET ACTIVE INVESTMENT TYPE
	function getActiveType() {
		$('#grantResults').attr('data-type','');
		var activeType = $('#investment-type').val();
		$('#grantResults').attr('data-type', activeType);
		filterType = activeType;
	}

	//SET & GET ACTIVE INVESTMENT CATEGORY
	function getActiveCategory() {
		var activeCatgory = $('#investment-category').val();
		filterCategory = activeCatgory;
	}

	//SET & GET ACTIVE SEARCH TERM
	function getActiveSearch() {
		var activeSearch = $('#grant-search').val();
		searchText = activeSearch;
	}


}); // end no-conflict document ready
</script>

<?php get_footer(); ?>
