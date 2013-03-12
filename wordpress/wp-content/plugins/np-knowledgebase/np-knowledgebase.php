<?php
/**
 * @package Akismet
 */
/*
Plugin Name: np-knownledgebase
Plugin URI: http://kb.northps.com/
Description: NorthPoint's Internal Operations Knowledgebase site plugin.
Version: 1.0.0
Author: William Choy
Author URI: mailto:William_choy@northps.com?Subject=np-knownledgebase
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define('NP_KNOWLEDGEBASE_VERSION', '2.5.6');
define('NP_KNOWLEDGEBASE_PLUGIN_URL', plugin_dir_url( __FILE__ ));

if ( is_admin() ) {
	require_once dirname( __FILE__ ) . '/admin.php';
}


add_filter('gettext', 'change_howdy', 10, 3);

function change_howdy($translated, $text, $domain) {
  if (!is_admin() || 'default' != $domain)
    return $translated;
  if (false !== strpos($translated, 'Howdy'))
    return str_replace('Howdy', 'Welcome back', $translated);
  return $translated;
}

/**
 *  Add a category metabox – http://shibashake.com/wordpress-theme/add-tags-and-categories-to-your-wordpress-page
 **/
// Retrieve Both Posts and Pages for Category Links
add_filter('request', 'np_knowledgebase_expanded_request');

function np_knowledgebase_expanded_request($q) {
  if (isset($q['tag']) || isset($q['category_name']) || isset($q['cat']))
    $q['post_type'] = array('post', 'page');
  return $q;
}
/**  Add a category metabox to WordPress page. **/


/**
 *  http://en.forums.wordpress.com/topic/how-to-create-submenus-of-a-category-please
 */

/**
 * Category page sort order - http://wordpress.org/support/topic/category-page-sort-order
 */
?>
