<?php
/*************************************************************/
/*  Filter Recipients/Grants							*/
/***********************************************************/
function ajax_get_recipients() {
    $perpage = 10;
    $page = $_REQUEST['page'];
    $region = $_REQUEST['region'];
    $type = $_REQUEST['type'];
    $category = $_REQUEST['category'];
    $searchTerm = $_REQUEST['s'];
    $grant_data = get_transient('grant_data');

    //THIS NEEDED TO BE COMMENTED OUT TO WORK AGAIN AFTER THE TRANSIENT TIMEOUT - NEEDS TO BE FIXED
    if ( false === $grant_data || !count($grant_data)){ //} || 1==1 ) {
        //print "GET DATA";

        // BASE QUERY -- ADD IF YOU NEED MORE DATA ON THE ORGANIZATION
        $querystr = "
        SELECT DISTINCT
        max(case when wpostmeta.meta_key = 'organization_name' then wpostmeta.meta_value end) as 'organization',
        max(case when wpostmeta.meta_key = 'name_for_sorting' then wpostmeta.meta_value end) as 'name_for_sorting',
        max(case when wpostmeta.meta_key = 'purpose' then wpostmeta.meta_value end) as 'purpose',
        max(case when wptaxonomies.taxonomy = 'grant_region' then wpterms.slug end) as 'region',
        max(case when wptaxonomies.taxonomy = 'grant_type' then wpterms.slug end) as 'type',
        max(case when wptaxonomies.taxonomy = 'grant_category' then wpterms.slug end) as 'category'
        FROM wp_posts wposts
        INNER JOIN wp_postmeta wpostmeta ON wposts.ID = wpostmeta.post_id
        INNER JOIN wp_term_relationships wptermrelationships ON (wposts.ID = wptermrelationships.object_id)
        INNER JOIN wp_term_taxonomy wptaxonomies ON (wptermrelationships.term_taxonomy_id = wptaxonomies.term_taxonomy_id)
        INNER JOIN wp_terms wpterms ON (wptaxonomies.term_id = wpterms.term_id)
        WHERE wposts.post_status = 'publish'
        AND wposts.post_type = 'grants'
        GROUP BY wpostmeta.post_id
        ";

        global $wpdb;
        $results = $wpdb->get_results($querystr);

        set_transient("grant_data", $results, 60 * MINUTE_IN_SECONDS );
        $grant_data = $results;
    }

    // array filters
    $filtered_grant_data = array_filter($grant_data, function($row) use($region, $type, $category, $searchTerm, $organizationsFound) {

        if($region && $row->region != $region){
            return false;
        }
        elseif($type && $row->type != $type){
            return false;
        }
        elseif($category && $row->category != $category){
            return false;
        }
        elseif ($searchTerm && strpos(strtolower($row->organization), strtolower($searchTerm)) == false) {
            return false;
        }
        else{
            return true;
        }
    });

    //unique  ((SUPER SLOW ))
    //$grant_data = array_unique($grant_data);
    //$grant_data_keys = array_unique(array_column($filtered_grant_data, 'organization'));
    global $uniqueStorage;
    $uniqueStorage = array();
    $grant_data = array_filter(
        $filtered_grant_data,
        function ($val, $key){ // N.b. $val, $key not $key, $val
            global $uniqueStorage;
            if(!in_array($val->organization, $uniqueStorage)){
                array_push($uniqueStorage, $val->organization);
                return true;
            }
            else{
                return false;
            }
        },
        ARRAY_FILTER_USE_BOTH
    );
    //print "DATA";
    //print_r($uniqueStorage);

    //$grant_data_unique = array_intersect_key($grant_data, $grant_data_keys);
    //print_r($grant_data);

    //SOMETHING HERE TO SORT ARRAY HOW YOU WANT IT TO
    sort($grant_data);

    // get row count
    $serviceResult = array();
    $serviceResult['rowcount'] = count($grant_data);

    //pagination
    $grant_data = array_slice($grant_data,$perpage*($page-1),$perpage);

    //LOOP FILTERED ORGANIZATIONS
    $resultHTML = "";

    foreach($grant_data as $result){
        if ($result->organization) :
            $resultHTML .= '<div class="recipients-card masonry-grid-item"><h2>'.$result->organization.'</h2>';
            //$resultHTML .=     count($grant_data_keys)."<br/>";
                // SEARCH GRANTS PER ORGANIZATION
                $arryParams = array(
                    'post_type' => 'grants',
                    'posts_per_page' => '-1',
                    'meta_query' => array(
                        array(
                            'key' => 'organization_name',
                            'value' => $result->organization,
                            'compare' => 'LIKE'
                        )
                    )
                );
                //APPLY TAXONOMY FILTER TO GRANT SEARCH
                $custom_terms = get_terms('organization_name');
                if($custom_terms){
                    $arryParams['tax_query'] = array(
                        'relation'=>'AND'
                    );
                    foreach($custom_terms as $custom_term) {
                       $taxonomyObj = array(
                           'taxonomy' => 'organization_name',
                           'field' => 'slug',
                           'terms' => $custom_term->slug,
                       );
                       array_push($taxonomyObj, $arryParams['tax_query']);
                    }
                }

                // QUERY AND LOOP THE GRANTS FOR LINKS
                $grants_loop = new WP_Query($arryParams);
                $resultHTML .= '<div class="innerGrantInfo">';
                $grantCount = 0;
                    while($grants_loop->have_posts()) : $grants_loop->the_post();
                        $grantCount++;
                        if ($grantCount === 1) {
                            $activeGrant = "active";
                        } else {
                            $activeGrant = "";
                        }
                        //VARS
                        $grantTypeTerms = get_the_terms( $post->ID , 'grant_type' );
                        $grantTypeTerm = array_pop($grantTypeTerms);
                        $grantTypeTermName = $grantTypeTerm->name;
                        $grantTypeTermSlug = $grantTypeTerm->slug;

                        $grantRegionTerms = get_the_terms( $post->ID , 'grant_region' );
                        $grantRegionTerm = array_pop($grantRegionTerms);
                        $grantRegionTermName = $grantRegionTerm->name;
                        $grantRegionTermSlug = $grantRegionTerm->slug;

                        $grantCategoryTerms = get_the_terms( $post->ID , 'grant_category' );
                        $grantCategoryTerm = array_pop($grantCategoryTerms);
                        $grantCategoryTermName = $grantCategoryTerm->name;
                        $grantCategoryTermSlug = $grantCategoryTerm->slug;

                        $resultHTML .= '<div data-count="'.$grantCount.'" class="grantRowContain '.$activeGrant.' '.$grantTypeTermSlug.' '.$grantRegionTermSlug.' '.$grantCategoryTermSlug.'">';
                            // GRANT TYPE
                            $resultHTML .= '<div class="grantTypeCard '.$grantTypeTermSlug.'" id="type-'.get_the_ID().'">'.$grantTypeTermName.'</div>';
                            // GRANT REGION
                            $resultHTML .= '<div class="grantRegion" id="region-'.get_the_ID().'">'.$grantRegionTermName.'</div>';
                            // GRANT PURPOSE
                            $resultHTML .= '<div class="grantPurpose" id="purpose-'.get_the_ID().'">';
                                $resultHTML .= get_field('purpose');
                            $resultHTML .= '</div>';
                            // GRANT ROWS
                            $resultHTML .= '<div class="grantRow" id="row-'.get_the_ID().'">';
                                $resultHTML .= '<span class="grantTitle">'.get_field('grant_title').'</span> <span class="grantAmount">'.get_field('amount_awarded').'</span>';
                            $resultHTML .= '</div>';
                        $resultHTML .= '</div>';
                    endwhile;
                $resultHTML .= '</div>';
                //RESET
                wp_reset_postdata();
            $resultHTML .= '</div>';
        endif;
    }
    $serviceResult['pageHTML'] = $resultHTML;
    print json_encode($serviceResult);

  die();
}
add_action( 'wp_ajax_nopriv_getrecipients', 'ajax_get_recipients' );
add_action( 'wp_ajax_getrecipients', 'ajax_get_recipients' );
