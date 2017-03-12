<?php
/*
	Plugin Name: Delete Original Image
	Plugin URI: http://wordpress.org/plugins/delete-original-image
	Description: This plugin allows you to enable an option to automatically delete the original image after upload. It also has an option to do it for all your previously uploaded images. The plugin will only modify images with 'large' size, copying that large image to replace the original one. 
	Author: Carlos Minatti, updated by Marktime Media
	Version: 10
	Author URI: 
	Text Domain: delete-original-image
	Domain Path: 
 */

require_once(dirname(__FILE__).'/includes/image-management.php');


if ( is_admin() ){ // admin actions
    add_action( 'admin_menu', 'doi_admin_page' );
    add_action( 'admin_init', 'doi_settings_api_init' );
} else {
  // non-admin enqueues, actions, and filters
}


$optionDelete = get_option( 'doi_delete_on_upload' );
// add or remove filter only if option change
if ( $optionDelete == '1') {
    add_filter('wp_generate_attachment_metadata','doi_replace_uploaded_image');
} elseif ( $optionDelete != '1' ) {
    remove_filter('wp_generate_attachment_metadata','doi_replace_uploaded_image');
}

/*
 * Add the menu to the admin page
 */
function doi_admin_page() {
  add_management_page( 'Delete Original Images' , 'Delete Original Images', 'manage_options', 'delete_original_image', 'doi_admin_page_callback');
}    

/*
 * Admin page code
 */
function doi_admin_page_callback() {
    
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page. Cheating?' ) );
  }


  if (isset($_POST['delete-all-original-images']) && $_POST['delete-all-original-images'] == 'delete') {

      $doi_images = new Doi_Images();
      $doi_images->get_delete_all();

  }
    
  require(dirname(__FILE__).'/views/admin-page.php');
}

/*
 *  Settings link on plugins page
 */
// Add settings link on plugin page
function doi_plugin_settings_link($links) { 
  $settings_link = '<a href="tools.php?page=delete_original_image">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'doi_plugin_settings_link' );



/**
 * Receive the IMAGE metadata and updates the original ('full') file 
 * if there is a 'bef_large_gallery' image generated.
 * Modified for BEF, used to be 'large' image size
 * 
 * @param type $image_data
 * @return type
 */
  function doi_replace_uploaded_image($image_data) {

    // if there is no large image : return
    if (!isset($image_data['sizes']['large'])) return $image_data; // replace large with your custom image size

    // paths to the uploaded image and the large gallery image
    $upload_dir = wp_upload_dir();
    $uploaded_image_location = $upload_dir['basedir'] . '/' .$image_data['file'];
    $large_image_location = $upload_dir['path'] . '/'.$image_data['sizes']['large']['file']; // replace large with your custom image size

    // delete the uploaded image
    unlink($uploaded_image_location);

    // rename the large gallery image
    copy($large_image_location,$uploaded_image_location);

    // update image metadata and return them
    $image_data['width'] = $image_data['sizes']['large']['width']; // replace large with your custom image size
    $image_data['height'] = $image_data['sizes']['large']['height']; // replace large with your custom image size
//    unset($image_data['sizes']['large']);

    rename($large_image_location,$uploaded_image_location);
    return $image_data;
  }


/*
 * Plugin Setings initialization
 */
function doi_settings_api_init(){
  
   // Add the section to reading settings so we can add our fields to it
    add_settings_section(
            'doi_options_section',      //section slug
            'Options',  //section name to show 
            'doi_options_section_callback_function',
            'doi_admin_page' //page for this section
    );
    
    // Add the field with the names and function to use for our new settings, put it in our new section
    add_settings_field(
            'doi_delete_on_upload', //setting slug
            'Delete original image on upload',  //setting name to show
            'doi_delete_on_upload_callback_function',
            'doi_admin_page', //page for this field
            'doi_options_section'   //section on that page, for this field
    );
    
  register_setting( 'doi_options_group', 'doi_delete_on_upload', 'doi_delete_on_upload_callback_sanitize' );
  
}

// ------------------------------------------------------------------
 // Settings section callback function
 // ------------------------------------------------------------------
 //
 // This function is needed if we added a new section. This function 
 // will be run at the start of our section
 //
 
 function doi_options_section_callback_function() {
 }
 

 // ------------------------------------------------------------------
 // Callback function for our example setting
 // ------------------------------------------------------------------
 //
 // creates a checkbox true/false option. Other types are surely possible
 //
 
 function doi_delete_on_upload_callback_function() {
     echo '<input name="doi_delete_on_upload" id="doi_delete_on_upload" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'doi_delete_on_upload' ), false ) . ' /> ';
 }
 
 function doi_delete_on_upload_callback_sanitize($value) {
         
    return $value;
 }