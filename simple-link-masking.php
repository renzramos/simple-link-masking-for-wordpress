<?php
/*
Plugin Name: Simple Link Masking
Plugin URI: http://www.renzramos.com
Description: a simple page to mask all links
Version: 1.0
Author: Renz R.
Author URI: http://www.renzramos.com
License: GPL2
*/


define('SLM_KEY','visit');
define('SLM_VERSION','1.0.0');
define('SLM_NAME','Simple Link Masking v.' . SLM_VERSION);

add_action( 'add_meta_boxes', 'simple_link_masking_meta_box' );

function simple_link_masking_meta_box(){
    add_meta_box( 'simple-link-masking-meta-box', SLM_NAME , 'simple_link_masking_box', '' , 'normal', 'high' );
}


function simple_link_masking_box(){
	
	global $post;
    $post_slug = $post->post_name;

    $original_link  = get_post_meta( $post->ID, 'slm_original_link', true );


    ?>
    <div class="wrap wrap-simple-link-masking">
    	<div class="form-group">
    		<label>Original Link</label>
    		<input name="slm_original_link" type="text" placeholder="Original Link" value="<?php echo $original_link; ?>"/>
    	</div>
    	<div class="form-group">
    		<label>Generated Link</label>
    		<div class="masked-link-container">
    			<a href="<?php echo simple_link_masking_generate($post->ID); ?>" target="_blank"><?php echo simple_link_masking_generate($post->ID); ?></a>
    		<div>
    	</div>
    	<small class="author">Developed by <a href="http://www.renzramos.com/">Renz Ramos</a></small>
    </div>
    <?php
}

function simple_link_masking_box_save( $post_id ) {
	if ( !current_user_can( 'edit_post', $post_id ))
	return;

	if ( isset($_POST['slm_original_link']) ) {        
		update_post_meta($post_id, 'slm_original_link', sanitize_text_field( $_POST['slm_original_link']));      
	}  
}
add_action('save_post', 'simple_link_masking_box_save');


// generate masked link
function simple_link_masking_generate($id){
	$slug = basename(get_permalink($id));
	return home_url(SLM_KEY . '/' . $slug);
}


// redirect
function spl_redirect(){

	global $wp;
	$current_url = home_url(add_query_arg(array(),$wp->request));
	$url_parse = wp_parse_url( $current_url );
	$path_purse = explode('/',$url_parse['path']);
	$key = array_search( SLM_KEY, $path_purse); // $key = 2;
	$tracking_code = $path_purse[$key + 1];

	if ( SLM_KEY  <> '' && $tracking_code <> ''){
		echo 'cont..';

		$post_types = array('post','page');

		$page = get_page_by_path($tracking_code, OBJECT , $post_types);

		if (!empty($page)){
			$original_link  = get_post_meta( $page->ID, 'slm_original_link', true );
			wp_redirect($original_link);
			exit;
		}
	}
}
add_action( 'template_redirect', 'spl_redirect' );









// admin enqueue
function simple_link_masking_enqueue() {
    wp_enqueue_style('simple-link-masking', plugins_url('/assets/css/style.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'simple_link_masking_enqueue');
add_action('login_enqueue_scripts', 'simple_link_masking_enqueue');

// filter
remove_filter('template_redirect','redirect_canonical');


?>