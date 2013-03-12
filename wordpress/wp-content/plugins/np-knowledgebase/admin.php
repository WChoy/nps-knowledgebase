<?php


/**
 *  Add a category metabox – http://shibashake.com/wordpress-theme/add-tags-and-categories-to-your-wordpress-page
 **/
add_action('admin_init', 'np_knowledgebase_admin_init');

function np_knowledgebase_admin_init() {
  global $wp_version;

  //  metaboxes automatically get added as part of the register_taxonomy_for_object_type function.
  // Saving Tags and Categories for WordPress Pages
  register_taxonomy_for_object_type('category', 'page');
}

/**  Add a category metabox to WordPress page. **/


/**
 *  CHANGE HOWDY – http://www.wpbeginner.com/wp-tutorials/how-to-change-the-howdy-text-in-wordpress-3-3-admin-bar/
 **/
add_action( 'admin_bar_menu', 'np_knowledgebase_admin_bar_my_custom_account_menu', 11 );

function np_knowledgebase_admin_bar_my_custom_account_menu( $wp_admin_bar ) {
  $user_id = get_current_user_id();
  $current_user = wp_get_current_user();
  $profile_url = get_edit_profile_url( $user_id );

  if ( 0 != $user_id ) {
    /* Add the "My Account" menu */
    $avatar = get_avatar( $user_id, 28 );
    $howdy = sprintf( __('Welcome %1$s'), $current_user->display_name );
    $class = empty( $avatar ) ? '' : 'with-avatar';

    $wp_admin_bar->add_menu( array(
      'id' => 'my-account',
      'parent' => 'top-secondary',
      'title' => $howdy . $avatar,
      'href' => $profile_url,
      'meta' => array('class' => $class),
    ));
  }
}

/** END CHANGE HOWDY **/

?>