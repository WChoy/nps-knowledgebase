<?php
/**
 *
 */
class BackWPup_Help {

	/**
	 *
	 */
	public static function help() {

		if ( method_exists( get_current_screen(), 'add_help_tab' ) ) {
			get_current_screen()->add_help_tab( array(
													 'id'      => 'plugininfo',
													 'title'   => __( 'Plugin Info', 'backwpup' ),
													 'content' =>
													 '<p> '.sprintf( _x( '%1$s version %2$s. A project of <a href="http://inpsyde.com">Inpsyde GmbH</a>. Developed by <a href="http://danielhuesken.de">Daniel Hüsken</a>.','Plugin name and link; Plugin Version','backwpup' ), '<a href="' . translate( BackWPup::get_plugin_data( 'PluginURI' ), 'backwpup' ) . '">' . BackWPup::get_plugin_data( 'Name' ) . '</a>' , BackWPup::get_plugin_data( 'Version' ) ) . '</p>'
													 . '<p>' . __( 'BackWPup comes with ABSOLUTELY NO WARRANTY. This is free software, and you are welcome to redistribute it under certain conditions.', 'backwpup' ) . '</p>'
												) );

			$text_help_sidebar = '<p><strong>' . __( 'For more information:', 'backwpup' ) . '</strong></p>';
			$text_help_sidebar .= '<p><a href="' . translate( BackWPup::get_plugin_data( 'PluginURI' ), 'backwpup' ) . '">' . BackWPup::get_plugin_data( 'Name' ) . '</a></p>';
			$text_help_sidebar .= '<p><a href="http://wordpress.org/extend/plugins/backwpup/">' . __( 'Plugin on wordpress.org', 'backwpup' ) . '</a></p>';
			$text_help_sidebar .= '<p><a href="' . __( 'https://marketpress.com/news/', 'backwpup' ) . '">' . __( 'News', 'backwpup' ) . '</a></p>';
			if ( class_exists( 'BackWPup_Features', FALSE ) )
				$text_help_sidebar .= '<p><a href="' . __( 'https://marketpress.com/support/forum/plugins/backwpup-pro/', 'backwpup' ) . '">' . __( 'Pro Support', 'backwpup' ) . '</a></p>';
			else
				$text_help_sidebar .= '<p><a href="' . __( 'http://wordpress.org/support/plugin/backwpup/', 'backwpup' ) . '">' . __( 'Support', 'backwpup' ) . '</a></p>';
			$text_help_sidebar .= '<p><a href="' . __( 'https://marketpress.com/documentation/backwpup-pro/', 'backwpup' ) . '">' . __( 'Manual', 'backwpup' ) . '</a></p>';

			get_current_screen()->set_help_sidebar( $text_help_sidebar );
		}

	}

	/**
	 * @static
	 *
	 * @param array $tab
	 */
	public static function add_tab( $tab = array() ) {

		if ( method_exists( get_current_screen(), 'add_help_tab' ) )
			get_current_screen()->add_help_tab( $tab );

	}

	/**
	 * @param      $text
	 * @param bool $echo
	 * @return string
	 */
	public static function tip( $text, $echo = TRUE ) {

		if ( ! $echo )
			return '<img class="help_tip" title="' . esc_attr( $text ) . '" src="' . BackWPup::get_plugin_data( 'URL' ) . '/images/help.png" />';

		echo '<img class="help_tip" title="' . esc_attr( $text ) . '" src="' . BackWPup::get_plugin_data( 'URL' ) . '/images/help.png" />';
	}
}
