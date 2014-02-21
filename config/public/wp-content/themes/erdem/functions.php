<?php
/**
 * Register additional post types
 */
function register_post_format() 
{
    add_theme_support( 'post-formats', array( 'standard' ) );
}
add_action( 'after_setup_theme', 'register_post_format' );

/**
 * Create new post types
 */
function create_post_type() 
{
	register_post_type( 'collection',
		array(
			'labels' => array(
                                
				'name' => __( 'All Collections' ),
				'singular_name' => __( 'Collection' )
			),
		'public' => true,
		'has_archive' => true,
                'supports' => array( 'title', 'editor', 'excerpt', 'custom-fields', 'thumbnail' ),
                'rewrite' => array('slug' => 'collections')
		)
	);
	register_post_type( 'biography',
		array(
			'labels' => array(
                                
				'name' => __( 'Biography' ),
				'singular_name' => __( 'Biography' )
			),
		'public' => true,
		'has_archive' => true,
                'supports' => array( 'title', 'editor', 'excerpt', 'custom-fields', 'thumbnail' ),
                'rewrite' => array('slug' => 'biography')
		)
	);
    register_post_type( 'stockist',
		array(
			'labels' => array(
                                
				'name' => __( 'All Stockists' ),
				'singular_name' => __( 'Stockist' )
			), 	
		'public' => true,
		'has_archive' => true,
                'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'),
                'rewrite' => array('slug' => 'stockists')
		)
	);
    register_post_type( 'explore',
		array(
			'labels' => array(
                                
				'name' => __( 'All Explore' ),
				'singular_name' => __( 'Explore' )
			),
		'public' => true,
		'has_archive' => true,
                'supports' => array( 'title', 'editor', 'post-formats', 'thumbnail', 'custom-fields', 'excerpt'),
                'taxonomies' => array( 'disciplines', 'clients'),
                'rewrite' => array('slug' => 'Explore')
		)
	);
    register_post_type( 'contact',
		array(
			'labels' => array(
                                
				'name' => __( 'All Contacts' ),
				'singular_name' => __( 'Contact' )
			),
		// 'taxonomies' => array('category'), 
		'public' => true,
		'has_archive' => true,
                'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'),
                'rewrite' => array('slug' => 'contacts')
		)
	);
}
add_action( 'init', 'create_post_type' );

/**
 * Add Custom Taxonomies
 */
function tax_stockists() {
	// create a new taxonomy
	register_taxonomy(
		'stockists',
		'stockist',
		array(
			'label' => __( 'Catagories' ),
			'rewrite' => array( 'slug' => 'stockists' ),
			'hierarchical' => true
		)
	);
}
add_action( 'init', 'tax_stockists' );

function tax_contacts() {
	// create a new taxonomy
	register_taxonomy(
		'contacts',
		'contact',
		array(
			'label' => __( 'Catagories' ),
			'rewrite' => array( 'slug' => 'contacts' ),
			'hierarchical' => true
		)
	);
}
add_action( 'init', 'tax_contacts' );

/**
 * Make custom post types available to REST API
 */
function allow_my_post_types($allowed_post_types) {
	$allowed_post_types[] = 'collection';
	$allowed_post_types[] = 'stockist';
	$allowed_post_types[] = 'latest';
	$allowed_post_types[] = 'contact';

	return $allowed_post_types;
}
add_filter( 'rest_api_allowed_post_types', 'allow_my_post_types');

/**
 * Make custom post metadata available to Thermal API
 */
function setmetaData($data, $post, $field_name) {
	$media_ids = get_post_meta($post->ID, $field_name, true);

	if(is_array($media_ids)) {
		foreach ($media_ids as $media_id) {
			$item = wp_get_attachment_url($media_id);
			$thumbnail = wp_get_attachment_thumb_url($media_id);
			$alt = get_post_meta($media_id, '_wp_attachment_image_alt', true);
			
			$media[] = (object) array('original_size'=>$item, 'alt_text'=>$alt, 'custom_size'=>$thumbnail);
			// $media = [];
			// $media[] = $mediaItem;


		}
	} else {
		$item = wp_get_attachment_url($media_ids);
		$media[] = $item;
	}


	$data->meta->${field_name} = $media;
}
add_filter( 'thermal_post_entity',  function($data, $post, $state) {
	global $post;

  if( $state === 'read' ) {

    $get = $_GET["post_type"];

    if ( $get === "collection" )
    {

			$data->meta->collection_copy = get_post_meta( $post->ID, 'collection_copy');
			$data->meta->collection_featured = get_post_meta( $post->ID, 'collection_featured');
			$data->meta->collection_runway_active = get_post_meta( $post->ID, 'collection_runway_active');
			$data->meta->collection_lookbook_active = get_post_meta( $post->ID, 'collection_lookbook_active');
			$data->meta->collection_backstage_active = get_post_meta( $post->ID, 'collection_backstage_active');
			$data->meta->collection_video_active = get_post_meta( $post->ID, 'collection_video_active');
			
			setmetaData($data, $post, 'collection_featured_image');
			setmetaData($data, $post, 'collection_runway_featured');
			setmetaData($data, $post, 'collection_lookbook_featured');
			setmetaData($data, $post, 'collection_backstage_featured');
			setmetaData($data, $post, 'collection_video_featured');
			setmetaData($data, $post, 'collection_video_poster');
			
			setmetaData($data, $post, 'collection_runway');
			setmetaData($data, $post, 'collection_lookbook');
			setmetaData($data, $post, 'collection_backstage');
			$data->meta->collection_video = get_post_meta( $post->ID, 'collection_video');

		} else if ( $get === "stockists" ) {

			// Stockists fields

			$data->meta->stockist_url = get_post_meta( $post->ID, 'stockist_url', true);

		} else if ( $get === "biography" ) {

			// Biography fields

			$data->meta->biography_title = get_post_meta( $post->ID, 'biography_title');
			$data->meta->biography_copy = get_post_meta( $post->ID, 'biography_copy');
			setmetaData($data, $post, 'biography_featured_image');


		} else if ( $get === "explore" ) {

			// Explore fields
			
			$data->meta->explore_type = get_post_meta( $post->ID, 'explore_type');
			$data->meta->explore_copy = get_post_meta( $post->ID, 'explore_copy');
			$data->meta->explore_url = get_post_meta( $post->ID, 'explore_url');
			$data->meta->explore_video = get_post_meta( $post->ID, 'explore_video');
			$data->meta->waiting_list = get_post_meta( $post->ID, 'waiting_list');
			setmetaData($data, $post, 'explore_image');
			$data->meta->explore_share = get_post_meta( $post->ID, 'explore_share', true);


		} else if ( $get === "contact" ) {
			// Contact Fields
			
			$data->meta->contact_catagory = get_post_meta( $post->ID, 'contact_catagory', true);
			$data->meta->contact_name = get_post_meta( $post->ID, 'contact_name', true);
			$data->meta->contact_orginization = get_post_meta( $post->ID, 'contact_orginization', true);
			$data->meta->contact_address_one = get_post_meta( $post->ID, 'contact_address_one', true);
			$data->meta->contact_address_two = get_post_meta( $post->ID, 'contact_address_two', true);
			$data->meta->contact_city = get_post_meta( $post->ID, 'contact_city', true);
			$data->meta->contact_postcode = get_post_meta( $post->ID, 'contact_postcode', true);
			$data->meta->contact_telephone = get_post_meta( $post->ID, 'contact_telephone', true);
			$data->meta->contact_email = get_post_meta( $post->ID, 'contact_email', true);

		}

	}

    return $data;
}, 10, 3);

// print_r(get_post_meta( $post->ID, 'collection_video_featured'), true);

/**
 * Remove Unwanted Admin Menu Items
 */
function remove_admin_menu_items() {
	$remove_menu_items = array(__('Posts'), ('Pages'), ('Dashboard'), ('jetpack'), ('Comments'), ('Feedback'), ('Profile'), ('Tools'), ('Settings'));
	global $menu;
	end ($menu);

	if( !current_user_can( 'manage_options' ) ) {
		while (prev($menu)){
			$item = explode(' ',$menu[key($menu)][0]);
			if(in_array($item[0] != NULL?$item[0]:"" , $remove_menu_items)){
			unset($menu[key($menu)]);}
		}
	}
}
add_action('admin_menu', 'remove_admin_menu_items');

function remove_jetpack_admin_menu_item() {
	if( class_exists( 'Jetpack' ) && !current_user_can( 'manage_options' ) ) {
		remove_menu_page( 'jetpack' );
	}
}
add_action( 'admin_init', 'remove_jetpack_admin_menu_item' );
