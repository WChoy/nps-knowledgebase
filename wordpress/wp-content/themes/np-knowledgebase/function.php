<?php
/* 
 * Unlike style.css, the functions.php of a child theme does not override its
 * counterpart from the parent. Instead, it is loaded in addition to the parent’s
 * functions.php. (Specifically, it is loaded right before the parent’s file.)
 */


/************* ACTIVE SIDEBARS ********************/

// adding additional sidebars to Wordpress (these are created in functions.php)
add_action( 'widgets_init', 'np_knowledgebase_register_sidebars' );

// Sidebars & Widgetizes Areas
function np_knowledgebase_register_sidebars ()  {

  /*
  to add more sidebars or widgetized areas, just copy
  and edit the above sidebar code. In order to call
  your new sidebar just use the following code:

  Just change the name to whatever your new
  sidebar's id is, for example:



  To call the sidebar in your template, you can just copy
  the sidebar.php file and rename it to your sidebar's name.
  So using the above example, it would be:
  sidebar-sidebar2.php

  */
  register_sidebar(array(
    'id' => 'header',
    'name' => 'Header',
    'before_widget' => '<div id="%1$s" class="widget span4 %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h4 class="widgettitle">',
    'after_title' => '</h4>',
  ));

  register_sidebar(array(
    'id' => 'footer_homepage',
    'name' => 'Homepage Footer',
    'description' => 'Used only on the homepage page template.',
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h4 class="widgettitle">',
    'after_title' => '</h4>',
  ));
} // np_knowledgebase_register_sidebars - END


?>
