<?php

/*************************************************************/
/*  Filter Recipients/Grants							*/
/***********************************************************/
function ajax_grants_loop($page = 1, $region, $type, $category, $searchText, $orderby, $order){
    //MAIN QUERY
    $arrParams = array(
        'post_type' => array('grants'),
        'paged' => $page,
        'posts_per_page' => '6',
        'meta_key'			=> 'organization_name',
        'orderby'			=> $orderby,
        'order'				=> $order,
        's'=> $searchText
    );

    //FILTERED TAX QUERY
    if ($region || $type || $category || $searchText) {
        $taxParams = array('relation' => 'AND');
        //SEARCH
    	if($searchText){
    		//search
    		array_push($taxParams,
    			array(
    				's'=>$searchText
    			)
    		);
    	};
        //REGION
        if ($region) {
            array_push($taxParams,
    			array(
    				'taxonomy' => 'grant_region',
    				'field'    => 'slug',
    				'terms'    => $region,
    				'operator' => 'IN'
    			)
    		);
        };
        //TYPE
        if ($type) {
            array_push($taxParams,
    			array(
    				'taxonomy' => 'grant_type',
    				'field'    => 'slug',
    				'terms'    => $type,
    				'operator' => 'IN'
    			)
    		);
        };
        //CATEGORY
        if ($category) {
            array_push($taxParams,
    			array(
    				'taxonomy' => 'grant_category',
    				'field'    => 'slug',
    				'terms'    => $category,
    				'operator' => 'IN'
    			)
    		);
        };

        //PUSH TO MAIN QUERY
        $arrParams['tax_query'] = $taxParams;
    }

    $loop = new WP_Query($arrParams);

    global $resultcount;
    $resultcount = $loop->found_posts;
    $resultcount =  number_format($resultcount);

    return $loop;
}
function ajax_get_grants() {
  $page = $_REQUEST['page'];
  $region = $_REQUEST['region'];
  $type = $_REQUEST['type'];
  $category = $_REQUEST['category'];
  $searchText = $_REQUEST['s'];
  $orderby = $_REQUEST['orderby'];
  $order = $_REQUEST['order'];
  global $resultcount;

  $grants_loop = ajax_grants_loop($page, $region, $type, $category, $searchText, $orderby, $order);

ob_start ();
include(locate_template('includes/block-recipients.php'));
$resultHTML = ob_get_contents();
ob_clean();

  $results = array();
  $results['resultCount'] = $resultcount;
  $results['resultHTML'] = $resultHTML;

  print json_encode($results);

  die();
}

add_action( 'wp_ajax_nopriv_ajax_get_grants', 'ajax_get_grants' );
add_action( 'wp_ajax_ajax_get_grants', 'ajax_get_grants' );
?>
