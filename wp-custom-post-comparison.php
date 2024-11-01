<?php
/**
 * Plugin Name: WP Custom Post Comparison
 * Plugin URI:  https://zetamatic.com/shop
 * Description: This plugin allow user to create custom post type, Custom fields and compare the posts
 * Version:     0.0.1
 * Author:      ZetaMatic
 * Author URI:  https://zetamatic.com
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpcp_comparison
 * Domain Path: /languages/
 *
 * @package WPCPC
 */


if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

if ( ! defined( 'WPCPC_PLUGIN_FILE' ) ) {
  define( 'WPCPC_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WPCPC_ROOT' ) ) {
  define( 'WPCPC_ROOT', dirname( plugin_basename( WPCPC_PLUGIN_FILE ) ) );
}

// Define plugin version
define( 'WPCPC_PLUGIN_VERSION', '0.0.1' );


if ( ! version_compare( PHP_VERSION, '5.6', '>=' ) ) {
  add_action( 'admin_notices', 'WPCPC_fail_php_version' );
} else {
  // Include the WPCPC class.
  require_once dirname( __FILE__ ) . '/inc/class-wp-custom-post-comparison.php';
}


/**
 * Admin notice for minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @since 0.0.1
 *
 * @return void
 */
function WPCPC_fail_php_version() {

  if ( isset( $_GET['activate'] ) ) {
    unset( $_GET['activate'] );
  }

  /* translators: %s: PHP version */
  $message      = sprintf( esc_html__( 'WP Custom Post Comparison requires PHP version %s+, plugin is currently NOT RUNNING.', 'wpcp_comparison' ), '5.6' );
  $html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
  echo wp_kses_post( $html_message );
}


?>