<?php
/**
 * The page template file for Recipients / Grants
 */

get_header(); ?>
<div class="recipients-page clearfix">
	<!-- PAGE TOP -->
	<div class="page-top">
		<div class="grid-row">
			<div class="max-contain">
				<h1><?php echo get_field('header');?></h1>
			</div>
		</div>
		<div class="grid-row">
			<div class="max-contain">
				<h3><?php echo get_field('sub_header');?></h3>
			</div>
		</div>
		<!-- FILTERS -->
		<div class="max-contain">
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
	</div>

	<!-- RESULTS -->
	<div class="max-contain">
		<div class="aboveResults">
			<div class="resultsContainer">
				<span class="resultsNumber">No</span> Results
			</div>
			<div class="sortBy">
				<label for="sort-by">Sort by</label>
				<div class="select-style">
					<select name="sort-by" id="sort-by">
						<option value="meta_value">Alphabetical</option>
						<option value="date">Most Recent</option>
					</select>
					<i class="fa fa-chevron-down" aria-hidden="true"></i>
				</div>
			</div>
		</div>
	</div>
	<?php if ( have_posts() ) : ?>
		<div class="max-contain">
	        <div id="grantResults" class="grant-recipients recipients-grid">
	            <!-- AJAX CALL -->
	        </div>
		</div>
    <?php endif; ?>
	<div class="whiteFade"></div>
	<div class="max-contain tcenter">
		<div class="button" id="LoadMore"><span>Load More</span></div>
	</div>
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
		$(".resultsNumber").html(savePostCount);
    });

	/* ON CHANGE OF SELECT FIELDS */
	$('#region, #investment-type, #investment-category, #sort-by').on('change',function(){
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
	});

    //GET RECIPIENTS - AJAX
    function getRecipients() {
		getActiveRegion();
		getActiveType();
		getActiveCategory();
		getActiveSearch();
		getActiveSort()
		var resultcount = "0";
		if(pageID ==1){
			$("#grantResults").html("");
			$("#LoadMore").addClass('disabled');
			$("#LoadMore span").text('Loading Posts');
		}
		//console.log('GET RECIPIENTS');
        $.ajax({
            url: "/wp-admin/admin-ajax.php",
            type: 'post',
            data: {
                action: 'ajax_get_grants',
                page : pageID,
				region : filterRegion,
				type : filterType,
				category : filterCategory,
				s: searchText,
				orderby : orderby,
				order : order
            },
			dataType: "JSON",
            complete: function(result) {
				if(result && result.responseText){
					var resultJson = jQuery.parseJSON(result.responseText);
					var resultcount = resultJson.resultCount;
			        var grants = jQuery(resultJson.resultHTML);

			        if(resultcount == 0){
						var savePostCount = $('.resultsNumber').html();
						$(".resultsNumber").html(savePostCount);
			            console.log("No posts");
					  	$("#LoadMore").addClass('disabled');
					  	$("#LoadMore span").text('No More Posts');
						$("#grantResults").masonry();
						if( $('#grantResults').is(':empty') ) {
							$(".resultsNumber").html(resultcount);
						}
			        }else{
				        //$("#grantResults").masonry('remove', $(".masonry-grid-item"));
						$(".resultsNumber").html(resultcount);
					  	$("#LoadMore").removeClass('disabled');
						$("#LoadMore span").text('Load More');
				        $("#grantResults").append(grants);
				        $("#grantResults").masonry('appended', grants).masonry('layout');
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

	//SET & GET ACTIVE SORT BY
	function getActiveSort() {
		var activeSort = $('#sort-by').val();
		var activeOrder = ""
		if (activeSort === 'date') {
			activeOrder = 'DESC';
		} else {
			activeOrder = "ASC"
		}
		orderby = activeSort;
		order = activeOrder;
	}

}); // end no-conflict document ready
</script>

<?php get_footer(); ?>
