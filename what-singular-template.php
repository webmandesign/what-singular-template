<?php
/**
 * Plugin Name:  What Singular Template
 * Plugin URI:   https://www.webmandesign.eu/portfolio/what-singular-template-wordpress-plugin/
 * Description:  Displays name of template assigned to a post, page or public custom post type in admin posts list table.
 * Version:      1.0.0
 * Author:       WebMan Design, Oliver Juhas
 * Author URI:   https://www.webmandesign.eu/
 * License:      GPL-3.0-or-later
 * License URI:  http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:  what-singular-template
 * Domain Path:  /languages
 *
 * Requires PHP:       7.0
 * Requires at least:  6.0
 *
 * @copyright  WebMan Design, Oliver Juhas
 * @license    GPL-3.0, https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @link  https://github.com/webmandesign/what-singular-template
 * @link  https://www.webmandesign.eu
 *
 * @package  What Singular Template
 */

namespace WebManDesign\WST;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Plugin functionality class.
 */
class Load {

	/**
	 * ID/name of templates column.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     string
	 */
	public static $column_name = 'wst_template';

	/**
	 * List of template paths with their names.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     array
	 */
	public static $templates = array();

	/**
	 * Initialization.
	 *
	 * @since  1.0.0
	 *
	 * @return  void
	 */
	public static function init() {

		// Requirements check

			if ( ! is_admin() ) {
				return;
			}


		// Variables

			self::$templates  = wp_get_theme()->get_page_templates();

			$post_types = get_post_types( array( 'public' => true ) );

			unset( $post_types['attachment'] );


		// Processing

			// Actions

				add_action( 'manage_pages_columns', __CLASS__ . '::columns' );
				add_action( 'manage_posts_columns', __CLASS__ . '::columns' );

				add_action( 'manage_pages_custom_column', __CLASS__ . '::content', 10, 2 );
				add_action( 'manage_posts_custom_column', __CLASS__ . '::content', 10, 2 );

			// Filters

				foreach ( $post_types as $post_type ) {
					add_filter( 'manage_edit-' . $post_type . '_sortable_columns', __CLASS__ . '::register_sortable' );
				}

				add_filter( 'request', __CLASS__ . '::do_sort' );

	} // /init

	/**
	 * Add new columns to posts list table.
	 *
	 * @since  1.0.0
	 *
	 * @param array  $post_columns  An associative array of column headings.
	 *
	 * @return  array
	 */
	public static function columns( array $post_columns ): array {

		// Processing

			$post_columns[ self::$column_name ] = esc_html_x( 'Template', 'Page/post template', 'what-singular-template' );


		// Output

			return $post_columns;

	} // /columns

	/**
	 * Outputs posts list table column content.
	 *
	 * @since  1.0.0
	 *
	 * @param string $column_name  The name of the column to display.
	 * @param int    $post_id      The current post ID.
	 *
	 * @return  void
	 */
	public static function content( string $column_name, int $post_id ) {

		// Requirements check

			if ( self::$column_name !== $column_name ) {
				return;
			}


		// Variables

			$template = get_post_meta( $post_id, '_wp_page_template', true );


		// Output

			if (
				$template
				&& 'default' !== $template
				&& isset( self::$templates[ $template ] )
			) {
				echo '<strong title="' . esc_attr_x( 'Template file: ', 'Page/post template', 'what-singular-template' ) . esc_attr( $template ) . '">' . esc_html( self::$templates[ $template ] ) . '</strong>';
			} else {
				echo '<em>' . esc_html_x( 'Default template', 'Page/post template', 'what-singular-template' ) . '</em>';
			}

	} // /content

	/**
	 * Register posts list table column as sortable.
	 *
	 * @since  1.0.0
	 *
	 * @param array $sortable_columns An array of sortable columns.
	 *
	 * @return  array
	 */
	public static function register_sortable( array $sortable_columns ): array {

		// Processing

			$sortable_columns[ self::$column_name ] = self::$column_name;


		// Output

			return $sortable_columns;

	} // /register_sortable

	/**
	 * Handle the query variables for sorting.
	 *
	 * @since  1.0.0
	 *
	 * @param array $query_vars  The array of requested query variables.
	 *
	 * @return  array
	 */
	public static function do_sort( array $query_vars ): array {

		// Processing

			if (
				isset( $query_vars['orderby'] )
				&& self::$column_name === $query_vars['orderby']
			) {

				$query_vars = array_merge( $query_vars, array(
					'meta_key' => '_wp_page_template',
					'orderby'  => 'meta_value',
				) );
			}


 		// Output

			return $query_vars;

	} // /do_sort

}

add_action( 'admin_init', 'WebManDesign\WST\Load::init', 101 );
