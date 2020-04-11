<?php



/////////////// disable Gutenberg css
add_action( 'wp_print_styles', 'wp_team_deregister_styles', 100 );
function wp_team_deregister_styles() {
    wp_dequeue_style( 'wp-block-library' );
    wp_deregister_style( 'wp-block-library' );
}
// disable Gutenberg for posts
add_filter('use_block_editor_for_post', '__return_false', 10);
// disable Gutenberg for post types
add_filter('use_block_editor_for_post_type', '__return_false', 10);



// Adding GTM
function sx_child_theme_head_gtm_script() { ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXX');</script>
<!-- End Google Tag Manager -->
<?php }
add_action( 'wp_head', 'sx_child_theme_head_gtm_script' );


// Add SVG icon
function add_favicon() {
	echo '<link rel="icon" href="'.content_url().'/uploads/favicon.svg" type="image/svg+xml">';
	//echo '<link rel="icon" href="'.content_url().'/favicon.png" type="image/png">';
}
add_action('wp_head', 'add_favicon');

/**
Add ACF options page
**/
add_action('acf/init', 'my_acf_op_init');
function my_acf_op_init() {
    // Check function exists.
    if( function_exists('acf_add_options_page') ) {
        // Register options page.
        $option_page = acf_add_options_page(array(
            'page_title'    => __('Plaque Direct Settings'),
            'menu_title'    => __('Plaque Direct Settings'),
            'menu_slug'     => 'plaque-direct-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
    }
}


/**
Enable SVG
**/
// SVG
function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
 
  function fix_svg_thumb_display() {
  echo '
    td.media-icon img[src$=".svg"], img[src$=".svg"].attachment-post-thumbnail { 
      width: 100% !important; 
      height: auto !important; 
    }
  ';
}
add_action('admin_head', 'fix_svg_thumb_display');
}
add_filter('upload_mimes', 'cc_mime_types');


/**
find_link_of_fierst_term
**/
function find_link_of_fierst_term( ){
  	global $post;
  	$first_term_id = get_the_terms( $post->ID, 'product_cat' )[0];
  $term_link = get_term_link( $first_term_id );
	return $term_link;
}
add_shortcode( 'link_term', 'find_link_of_fierst_term' );


/**
get Elementor testimonial from acf repeater dinamicly
**/
// get testimonial from acf repeater
add_action( "elementor/widget/before_render_content", function($widget){	
	// מוטב שהקוד יעבוד רק בפרונט
	if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		return;
	}
	// אנחנו מתעסקים רק עם ווידג'ט מסוג טסטימוניאל
	if("icon-list" !== $widget->get_name()){
		return;
	}
	// המזהה הייחודי שנתנו לוויג'ט כדי לא לדפוק את שאר הווידג'טים מסוג זה
	if("benefits" !== $widget->get_settings('_element_id')){
		return;
	}
	// מאוחר יותר נחליף את הנתונים שקיימים בווידג'ט עם נתונים מהשדות שלנו
	$slides = array();
	//הזרקת הנתונים מהשדות שלנו לאלמנטור
	if($testimonial = get_field('benefits')){
		$counter = 0;
		foreach($testimonial as $item){
			$slides[] = array(
				'text' => $item['line'], // תת שדה תוכן מהרפיטר
				//'title' => $item['title'],
				'_id' => 'd395c38'.$counter, // מזהה משתנה לכל פריט ברשימה
				// במקרה של הלקוח לא היה צורך בתמונת ממליץ
				'selected_icon' => array(
					'value' => 'fas fa-check',
					'library' => 'fa-solid',
				)
			);
			$counter++;
		}
	}
	// ההחלפה בין הנתונים של הווידג'ט לנתונים שאספנו מהרפיטר
	$widget->set_settings('icon_list',$slides);
	
});



/**
Insert dynamicly images from media library are tag with Acf field "select_page" to Elementor "media-carousel" widget
**/
// get testimonial from acf repeater
add_action( "elementor/widget/before_render_content", function($widget){	
	if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		return;
	}
	if("media-carousel" !== $widget->get_name()){
		return;
	}
	
	if("posts_carousel" !== $widget->get_settings('_element_id')){
		return;
	}
	
	$gallery = array();

      $ids = get_posts( 
          array(
              'post_type'      => 'attachment', 
              'post_mime_type' => 'image', 
              'post_status'    => 'inherit', 
              'posts_per_page' => 10,//-1,
              'fields'         => 'ids',
              'meta_query' => array(
                array(
                  'key' => 'select_page', // name of custom field
                  'value' => '"' . get_the_ID() . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                  'compare' => 'LIKE'
                )
              )
          ) 
      );

      $images = array();
      foreach ( $ids as $id ){
          $images[]= $id;
      }
		// default from acf gallery field if no images attach to the post
	  $images = !empty($images) ? $images : get_field('default_images', 'options') ;

	if( !empty($images) ){
		$counter = 0;
		foreach($images as $item){
			$gallery[] = array(
				'_id' => 'd34738'.$counter, 
				'image' => array(
            'id' => $item,
          )
			);
			$counter++;
		}
	}
	$widget->set_settings('slides',$gallery);
	
});
