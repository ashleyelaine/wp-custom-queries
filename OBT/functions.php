<?php

/*************************************************************/
/*  ENQUEUE SCRIPTS AND STYLES 								*/
/***********************************************************/
// for documentation and a list of scripts that are pre-registered by wordpress see https://developer.wordpress.org/reference/functions/wp_enqueue_script
// for a quick overview read this http://www.wpbeginner.com/wp-tutorials/how-to-properly-add-javascripts-and-styles-in-wordpress

function my_add_theme_scripts() {

    // stylesheets for compiling sass on the server at runtime

    wp_enqueue_style( 'style', get_template_directory_uri().'/dist/css/style.css' );
    // fontawesome
    wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/fonts/font-awesome-4.6.3/css/font-awesome.min.css' );

    // scripts
    wp_register_script( 'main.js', get_template_directory_uri() . '/dist/js/main.js', array('jquery'), '1.1.0', true );
    wp_enqueue_script('main.js');
    wp_register_script( 'scrollreveal.js', get_template_directory_uri() . '/dist/js/libs/scrollreveal.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script('scrollreveal.js');
    wp_register_script( 'masonry.js', get_template_directory_uri() . '/dist/js/libs/masonry.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script('masonry.js');
}
add_action( 'wp_enqueue_scripts', 'my_add_theme_scripts' );


/*************************************************************/
/*  REGISTER MENUS 			 								*/
/***********************************************************/

function register_my_menus() {

  register_nav_menus(
    array(
    'main-menu' => __( 'Main Menu' ),
	  'footer-menu-one' => __( 'Footer Menu One' ),
    'footer-menu-two' => __( 'Footer Menu Two' ),
	  'mobile-menu' => __( 'Mobile Menu' ),
    )
  );

}
add_action( 'init', 'register_my_menus' );


/*************************************************************/
/*  REGISTER SIDEBAR 										*/
/***********************************************************/

function arphabet_widgets_init() {

  register_sidebar( array(
    'name'          => 'Sidebar One',
    'id'            => 'sidebar_one',
    'before_widget' => '<div class="widget">',
    'after_widget'  => '</div>',
    'before_title'  => '<h2>',
    'after_title'   => '</h2>',
    'description'   => ''
  ) );

  register_sidebar( array(
    'name'          => 'Sidebar Two',
    'id'            => 'sidebar_two',
    'before_widget' => '<div class="widget">',
    'after_widget'  => '</div>',
    'before_title'  => '<h2>',
    'after_title'   => '</h2>',
    'description'   => ''
  ) );

}
add_action( 'widgets_init', 'arphabet_widgets_init' );
/*************************************************************/
/*  Add ACF Options 										*/
/***********************************************************/
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page();
}
/*************************************************************/
/*  ACF Google Maps										*/
/***********************************************************/
function my_acf_init() {

	acf_update_setting('google_api_key', 'AIzaSyDRbXdSykboo3LmzdC5NLpIflhG_W_ntbU');
}

add_action('acf/init', 'my_acf_init');
/*************************************************************/
/*  Load more news articles									*/
/***********************************************************/
function more_post_ajax(){
    $offset = $_POST["offset"];
    $ppp = $_POST["ppp"];
    $postType = $_POST["postType"];
    $terms = $_POST["terms"];
    $taxonomy = $_POST["taxonomy"];
    if($terms == "all"){
      $args = array(
        'post_type' => $postType,
        'order' => 'ASC',
        'posts_per_page' => $ppp,
        'offset' => $offset,
      );
    } else{
    $args = array(
      'post_type' => $postType,
      'order' => 'ASC',
      'posts_per_page' => $ppp,
      'offset' => $offset,
        'tax_query' => array(
          array(
            'taxonomy'=> $taxonomy,
            'field'=> 'slug',
            "terms" => array($terms)
          )
        ),
      );
    }

    $loop = new WP_Query($args);
    while ($loop->have_posts()) { $loop->the_post();
      $headline = get_field('headline');
      $featuredImage = get_field('featured_image');
      $category = get_field('type_of_news')[0];
      $date = get_the_date();
      $author = get_field('author');
      if ($postType == "news_stories"){
        if ($featuredImage != ""){
          echo'<a href="';
          the_permalink();
          echo'">';
          echo'<div class="news-card masonry-grid-item">
          <div class="news-image" style="background-image:url('. $featuredImage .');"></div>
              <div class="news-card-text">
                <h4 class="news-category">'.$category->name .'</h4>
                <h3 class="news-headline">'. $headline .'</h3>
                <p class="news-info">'.$date. '- By '. $author .'</p>
              </div>
          </div></a>';
        }else{
          echo'<a href="';
          the_permalink();
          echo'">';
          echo'<div class="news-card masonry-grid-item">
              <div class="news-card-text">
                <h4 class="news-category">'.$category->name .'</h4>
                <h3 class="news-headline">'. $headline .'</h3>
                <p class="news-info">'.$date. '- By '. $author .'</p>
              </div>
          </div></a>';
        }
      }
      elseif ($postType == 'stories') {
        $title = get_field('title');
        $featuredImage = get_field('featured_image');
        $category = get_field('story_type')[0];
        $organizationName = get_field('organization_name');
        $organizationLoacation= get_field('organization_location');
        $storyCardContent= get_field('story_card_content');
        if ($featuredImage != ""){
          echo'<a href="';
          the_permalink();
          echo'">';
          	echo "
              <div class='story-card masonry-grid-item'>
                <div class='story-image' style='background-image:url(" . $featuredImage. ");'></div>
                <div class='story-card-text'>
                  <h4>". $category->name ."</h4>
                  <h3>". $title ."</h3>
                  <h6>". $organizationName ."</h6>
                  <h6>". $organizationLoacation ."</h6>
                  <p>". $storyCardContent ."</p>
                  </div>
                </div></a>";
        } else{
          echo'<a href="';
          the_permalink();
          echo'">';
          echo "
            <div class='story-card masonry-grid-item'>
              <div class='story-card-text'>
                <h4>". $category->name ."</h4>
                <h3>". $title ."</h3>
                <h6>". $organizationName ."</h6>
                <h6>". $organizationLoacation ."</h6>
                <p>". $storyCardContent ."</p>
                </div>
            </div></a>";
        }
      }
    }
    exit;
}
add_action('wp_ajax_nopriv_more_post_ajax', 'more_post_ajax');
add_action('wp_ajax_more_post_ajax', 'more_post_ajax');
/*************************************************************/
/*  Filter News Articles									*/
/***********************************************************/
function filter_post_ajax(){
  $terms = $_POST["terms"];
  $taxonomy = $_POST["taxonomy"];
  $postType = $_POST["postType"];
  if($terms == "all"){
    $args = array(
      'post_type' => $postType,
      'order' => 'ASC',
      'posts_per_page' => 12,
    );
  } else{
  $args = array(
    'post_type' => $postType,
    'order' => 'ASC',
    'posts_per_page' => -1,
      'tax_query' => array(
        array(
          'taxonomy'=> $taxonomy,
          'field'=> 'slug',
          "terms" => array($terms)
        )
      ),
    );
  }
  $loop = new WP_Query($args);
  while ($loop->have_posts()) { $loop->the_post();
    if($taxonomy == "news_type"){
      $headline = get_field('headline');
      $featuredImage = get_field('featured_image');
      $category = get_field('type_of_news')[0];
      $date = get_the_date();
      $author = get_field('author');
      if ($featuredImage != ""){
        echo'<a href="';
        the_permalink();
        echo'">';
        echo'<div class="news-card masonry-grid-item">
        <div class="news-image" style="background-image:url('. $featuredImage .');"></div>
            <div class="news-card-text">
              <h4 class="news-category">'.$category->name .'</h4>
              <h3 class="news-headline">'. $headline .'</h3>
              <p class="news-info">'.$date. '- By '. $author .'</p>
            </div>
        </div></a>';
      }else{
        echo'<a href="';
        the_permalink();
        echo'">';
        echo'<div class="news-card masonry-grid-item">
            <div class="news-card-text">
              <h4 class="news-category">'.$category->name .'</h4>
              <h3 class="news-headline">'. $headline .'</h3>
              <p class="news-info">'.$date. '- By '. $author .'</p>
            </div>
        </div></a>';
      }
    }
    else if($taxonomy == "story_types"){
      $featuredImage = get_field('featured_image');
      $category = get_field('story_type')[0];
      $organizationName = get_field('organization_name');
      $organizationLoacation= get_field('organization_location');
      $storyCardContent= get_field('story_card_content');
      if ($featuredImage != ""){

        	echo "
            <div class='story-card masonry-grid-item'>
              <div class='story-image' style='background-image:url(" . $featuredImage. ");'></div>
              <div class='story-card-text'>
                <h4>". $category->name ."</h4>
                <h3>";
                the_title();
                echo"</h3>
                <h6>". $organizationName ."</h6>
                <h6>". $organizationLoacation ."</h6>
                <p>". $storyCardContent ."</p>
                </div>
              </div>";
      } else{

        echo "
          <div class='story-card masonry-grid-item'>
            <div class='story-card-text'>
              <h4>". $category->name ."</h4>
              <h3>";
          the_title();
        echo"</h3>
              <h6>". $organizationName ."</h6>
              <h6>". $organizationLoacation ."</h6>
              <p>". $storyCardContent ."</p>
              </div>
          </div>";
      }
    }
    else if($taxonomy == "contact_type"){
      $title = get_field('title');
      $regionOne = get_field('region_served_one');
      $regionNameOne = $regionOne -> post_title;
      $regionTwo = get_field('region_served_two');
      $regionNameTwo = $regionTwo -> post_title;
      $regionThree = get_field('region_served_three');
      $regionNameThree = $regionThree -> post_title;
      $phone = get_field('phone_number');
      $email = get_field('email');
      echo '<div class="contact-card masonry-grid-item"><div class="text-wrapper"><h3>';
      the_title();
      echo '</h3><h4>'. $title.'</h4>';
      if($regionNameOne != ""){ echo '<h5>Regions Served</h5><p>';
        echo $regionNameOne;
        echo '</p>';
      } if($regionNameTwo != ""){
        echo '<p>';
        echo $regionNameTwo;
        echo '</p>';
      } if($regionNameThree != "" && $terms == "hybrid-investment-inquiries"){
        echo '<p>';
        echo $regionNameThree;
        echo'</p>';
      }
      echo'</div>  <div class="contact-methods"><a href="tel:' . $phone .'">'. $phone. '</a>
              <a href ="mailto:' . $email .'">'.$email.'</a>';

      echo'</div></div>';
    }
  }
}

  add_action('wp_ajax_nopriv_filter_post_ajax', 'filter_post_ajax');
  add_action('wp_ajax_filter_post_ajax', 'filter_post_ajax');

  // Relevanssi add content to custom excerpts.
add_filter('relevanssi_excerpt_content', 'custom_fields_to_excerpts', 10, 3);
function custom_fields_to_excerpts($content, $post, $query) {

		$custom_fields = get_post_custom_keys($post->ID);
		$remove_underscore_fields = true;

		if (is_array($custom_fields)) {
			$custom_fields = array_unique($custom_fields);	// no reason to index duplicates
			foreach ($custom_fields as $field) {
				if ($remove_underscore_fields) {
					if (substr($field, 0, 1) == '_') continue;
				}
				$values = get_post_meta($post->ID, $field, false);
				if ("" == $values) continue;
				foreach ($values as $value) {
					if ( !is_array ( $value ) ) {
						$content .= " " . $value;
					}
				}
			}
		}

    return $content;
}

/*************************************************************/
/*  Filter Recipients/Grants							*/
/***********************************************************/
function fjorge_grants_loop($page = 1, $region, $type, $category, $searchText, $orderby, $order){
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
function fjorge_get_grants() {
  $page = $_REQUEST['page'];
  $region = $_REQUEST['region'];
  $type = $_REQUEST['type'];
  $category = $_REQUEST['category'];
  $searchText = $_REQUEST['s'];
  $orderby = $_REQUEST['orderby'];
  $order = $_REQUEST['order'];
  global $resultcount;

  $grants_loop = fjorge_grants_loop($page, $region, $type, $category, $searchText, $orderby, $order);

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

add_action( 'wp_ajax_nopriv_fjorge_get_grants', 'fjorge_get_grants' );
add_action( 'wp_ajax_fjorge_get_grants', 'fjorge_get_grants' );
?>
