<?php
/**
 * Class WPCPC file.
 * 
 * @package WPCPC
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'WPCPC', false ) ) :

  class WPCPC {

  	/**
  	 * Holds the values to be used in the fields callbacks
  	 */
  	private $options;

    /**
     * Initialize the actions . 
     */
    public function __construct() { 
      //Creating a custom post type and register the post type      
      add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
      add_action( 'admin_init', array( $this, 'page_init' ) );
      add_action( 'init', array( $this, 'custom_post_type_register' ) );

      //Enqueue the js and css in wordpress admin
      add_action( 'admin_enqueue_scripts', array( $this, 'wp_cp_enqueue_scripts' ));

      //Adding metabox to add custom fields
      add_action( 'add_meta_boxes', array( $this, 'add_custom_field_metaboxes' ) );
      add_action( 'save_post', array( $this, 'save_custom_field_meta' ) );

      //Adding custom field and its value in post of custom post types
      add_action( 'admin_init', array( $this, 'option_page_init' ) );

      // Edit the post type
      add_action( 'wp_ajax_wp_cp_comparison_editPost', array( $this, 'wp_cp_comparison_editPost' ) );
      add_action( 'wp_ajax_nopriv_wp_cp_comparison_editPost', array( $this, 'wp_cp_comparison_editPost' ) );

      //Delete the post type
      add_action( 'wp_ajax_wp_cp_comparison_deletepost', array( $this, 'wp_cp_comparison_deletepost' ) );
      add_action( 'wp_ajax_nopriv_wp_cp_comparison_deletepost', array( $this, 'wp_cp_comparison_deletepost' ) );

      //Load plugin textdomain.
      add_action( 'plugins_loaded', array( $this, 'wp_cp_plugin_load_textdomain' ) );

      //getting all the customfields and sending to js file
      add_action( 'wp_ajax_wp_cp_get_customfield', array( $this, 'wp_cp_get_customfield' ) );
      add_action( 'wp_ajax_noprivwp__cp_get_customfield', array( $this, 'wp_cp_get_customfield' ) );
    }

    /**
     * Load plugin textdomain.
     *
     * @since 0.0.1
     */
    public function wp_cp_plugin_load_textdomain() {
      load_plugin_textdomain( 'wpcp_comparison', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
    }

    /**
      * Enqueue a script in the WordPress admin.
      *
      * Enque a css in the wordpress admin
      *
      * Localize the script and create the nonce field 
      *
      * @param int $hook Hook suffix for the current admin page.
      */
    public function wp_cp_enqueue_scripts( $hook ) {

      //Register and Enque the JS file in WordPress Admin
      wp_register_script( 'wp_custom_post_script', plugins_url( 'assets/js/wp_cp_comparison_js.js', WPCPC_PLUGIN_FILE ) );
      wp_enqueue_script( 'wp_custom_post_script' );

      //Register and Enque the CSS file in WordPress Admin
      wp_register_style( 'wp_custom_post_css', plugins_url( 'assets/css/wp_cp_comparison_css.css', WPCPC_PLUGIN_FILE ) );
      wp_enqueue_style( 'wp_custom_post_css' );

      // Localize the script
      wp_localize_script(
        'wp_custom_post_script',
        'wp_cp_comparison_ajax_obj',
        array( 
          'ajaxurl' => admin_url( 'admin-ajax.php' ),
          'nonce'   => wp_create_nonce( 'ajax-nonce' ) )
      );
    }

    /**
     * Getting all post type from option page using get_option()
     * 
     * @since 0.0.1
     *
     * Register a custom post type 
     */
    public function custom_post_type_register() {
  		$get_custom_post_types = get_option( 'custom_post_type_option' );
      $supports_arr = array( 'title', 'editor' );
      $taxonomies_arr = array( 'category', 'post_tag' );
      if ( is_array( $get_custom_post_types ) ) {
        foreach ( $get_custom_post_types as $key => $cpts ) {
          if ( sizeof( $cpts ) == 1) {
            foreach ( $cpts as $cpts ) {
              if( ! empty( $cpts ) ) {
                $labels = array(
                  'name'                  => _x( $cpts['post_type'], 'Post Type General Name' ),
                  'singular_name'         => _x( $cpts['singular_label'], 'Post Type Singular Name' ),
                  'menu_name'             => $cpts['menu_name'] ? $cpts['menu_name'] : $cpts['plural_label'],
                  'name_admin_bar'        => __( $cpts['plural_label'], 'wpcp_comparison' ),
                  'archives'              => __( $cpts['archives'], 'wpcp_comparison' ),
                  'parent_item_colon'     => __( $cpts['parent_item_colon'], 'wpcp_comparison' ),
                  'all_items'             => __( $cpts['all_items'] ? $cpts['all_items'] : "All ".$cpts['plural_label'] ),
                  'add_new_item'          => __( $cpts['add_new_item'], 'wpcp_comparison' ),
                  'add_new'               => _x( $cpts['add_new'] ? $cpts['add_new'] : 'Add New', strtolower( $cpts['singular_label'] ) ),
                  'new_item'              => __( $cpts['new_item'], 'wpcp_comparison' ),
                  'edit_item'             => __( $cpts['edit_item'], 'wpcp_comparison' ),
                  'view_items'            => __( !empty( $cpts['view_item'] ) ? $cpts['view_item'] : "View ".$cpts['singular_label'] ),
                  'search_items'          => __( $cpts['search_items'], 'wpcp_comparison' ),
                  'not_found'             => __( $cpts['not_found'], 'wpcp_comparison' ),
                  'not_found_in_trash'    => __( $cpts['not_found_in_trash'], 'wpcp_comparison' ),
                  'featured_image'        => __( $cpts['featured_image'], 'wpcp_comparison' ),
                  'set_featured_image'    => __( $cpts['set_featured_image'], 'wpcp_comparison' ),
                  'remove_featured_image' => __( $cpts['remove_featured_image'], 'wpcp_comparison' ),
                  'use_featured_image'    => __( $cpts['use_featured_image'], 'wpcp_comparison' ),
                  'insert_into_item'      => __( $cpts['insert_into_item'], 'wpcp_comparison' ),
                  'uploaded_to_this_item' => __( $cpts['uploaded_to_this_item'], 'wpcp_comparison' ),
                  'items_list'            => __( $cpts['items_list'], 'wpcp_comparison' ),
                  'items_list_navigation' => __( $cpts['items_list_navigation'], 'wpcp_comparison' ),
                  'filter_items_list'     => __( $cpts['filter_items_list'], 'wpcp_comparison' ),
                );
                $args = array(
                  'label'                 => __( $cpts['post_type'], 'wpcp_comparison' ),
                  'description'           => __( $cpts['post_description'], 'wpcp_comparison' ),
                  'labels'                => $labels,
                  'supports'              => !empty( $cpts['supports'] ) ? $cpts['supports'] : $supports_arr ,
                  'taxonomies'            => !empty( $cpts['taxonomies'] ) ? $cpts['taxonomies'] : $taxonomies_arr,
                  'hierarchical'          => $cpts['hierarchical'] == 'false' ? false : true,
                  'public'                => $cpts['public'] == 'false' ? false : true,
                  'show_ui'               => $cpts['show_ui'] == 'false' ? false : true,
                  'show_in_menu'          => $cpts['show_in_menu'] == 'false' ? false : true,
                  'menu_position'         => __( $cpts['menu_position'], 'wpcp_comparison' ),
                  'has_archive'           => $cpts['has_archive'] == 'false' ? false : true,
                  'capability_type'       => 'post',
                  'show_in_rest_api'      => !empty( $cpts['show_in_rest_api'] ) ? $cpts['show_in_rest_api'] : false,
                );

                //Register the post type
                register_post_type( $cpts['post_type'], $args );
              }
            }
          }
          else {
            if( ! empty( $cpts ) ) {
              $labels = array(
                'name'                  => _x( $cpts['post_type'], 'Post Type General Name' ),
                'singular_name'         => _x( $cpts['singular_label'], 'Post Type Singular Name' ),
                'menu_name'             => $cpts['menu_name'] ? $cpts['menu_name'] : $cpts['plural_label'],
                'name_admin_bar'        => __( $cpts['plural_label'], 'wpcp_comparison' ),
                'archives'              => __( $cpts['archives'], 'wpcp_comparison' ),
                'parent_item_colon'     => __( $cpts['parent_item_colon'], 'wpcp_comparison' ),
                'all_items'             => __( $cpts['all_items'] ? $cpts['all_items'] : "All ".$cpts['plural_label'] ),
                'add_new_item'          => __( $cpts['add_new_item'], 'wpcp_comparison' ),
                'add_new'               => _x( $cpts['add_new'] ? $cpts['add_new'] : 'Add New', strtolower( $cpts['singular_label'] ) ),
                'new_item'              => __( $cpts['new_item'], 'wpcp_comparison' ),
                'edit_item'             => __( $cpts['edit_item'], 'wpcp_comparison' ),
                'view_items'            => __( !empty( $cpts['view_item'] ) ? $cpts['view_item'] : "View ".$cpts['singular_label'] ),
                'search_items'          => __( $cpts['search_items'], 'wpcp_comparison' ),
                'not_found'             => __( $cpts['not_found'], 'wpcp_comparison' ),
                'not_found_in_trash'    => __( $cpts['not_found_in_trash'], 'wpcp_comparison' ),
                'featured_image'        => __( $cpts['featured_image'], 'wpcp_comparison' ),
                'set_featured_image'    => __( $cpts['set_featured_image'], 'wpcp_comparison' ),
                'remove_featured_image' => __( $cpts['remove_featured_image'], 'wpcp_comparison' ),
                'use_featured_image'    => __( $cpts['use_featured_image'], 'wpcp_comparison' ),
                'insert_into_item'      => __( $cpts['insert_into_item'], 'wpcp_comparison' ),
                'uploaded_to_this_item' => __( $cpts['uploaded_to_this_item'], 'wpcp_comparison' ),
                'items_list'            => __( $cpts['items_list'], 'wpcp_comparison' ),
                'items_list_navigation' => __( $cpts['items_list_navigation'], 'wpcp_comparison' ),
                'filter_items_list'     => __( $cpts['filter_items_list'], 'wpcp_comparison' ),
              );
              $args = array(
                'label'                 => __( $cpts['post_type'], 'wpcp_comparison' ),
                'description'           => __( $cpts['post_description'], 'wpcp_comparison' ),
                'labels'                => $labels,
                'supports'              => !empty( $cpts['supports'] ) ? $cpts['supports'] : $supports_arr,
                'taxonomies'            => !empty( $cpts['taxonomies'] ) ? $cpts['taxonomies'] : $taxonomies_arr,
                'hierarchical'          => $cpts['hierarchical'] == 'false' ? false : true,
                'public'                => $cpts['public'] == 'false' ? false : true,
                'show_ui'               => $cpts['show_ui'] == 'false' ? false : true,
                'show_in_menu'          => $cpts['show_in_menu'] == 'false' ? false : true,
                'menu_position'         => __( $cpts['menu_position'], 'wpcp_comparison' ),
                'has_archive'           => $cpts['has_archive'] == 'false' ? false : true,
                'capability_type'       => 'post',
                'show_in_rest_api'      => !empty( $cpts['show_in_rest_api'] ) ? $cpts['show_in_rest_api'] : false,
              );

              //Register the post type
              register_post_type( $cpts['post_type'], $args );
            }  
          }     
        }
      }           
    }

  	/**
     * Registering a new admin page for Custom Post Comparison.
     *
     * @since 0.0.1
     */
  	public function add_plugin_page() {
      add_menu_page(
        __( 'Custom Post Comparison', 'wpcp_comparison' ),
        __( 'CP Comparison', 'wpcp_comparison' ),
        'manage_options', 
        'custom-post-comparison', 
        array( $this, 'create_admin_home_page' ),
        'data:image/svg+xml;base64,'.$this->wp_cp_comparison_svg_icon(),
        '30'
      );

      // Adding submenu Custom Post Type to CP Comparison menu 
      add_submenu_page(
        'custom-post-comparison', 
          __( 'CP Custom Post', 'wpcp_comparison' ),
          __( 'Custom Post Type', 'wpcp_comparison' ),
        'manage_options', 
        'custom-post',
         array( $this, 'create_admin_page_custom_post' )
      );

      //Adding submenu Custom fields to CP Comparison menu
      add_submenu_page(
        'custom-post-comparison', 
          __( 'CP Custom Fields', 'wpcp_comparison' ),
          __( 'Custom Fields', 'wpcp_comparison' ),
        'manage_options', 
        'cp-custom-field',
         array( $this, 'create_admin_page_custom_field' )
      );
    }

    /**
     * SVG Icon for menu page
     * 
     * @since 0.0.1
     * @return string|html
     */
    public function wp_cp_comparison_svg_icon( $color = false ) {
      $color = ($color) ? $color : '#FFF';
      return base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52.03 55"><defs><style>.cls-1{fill:'.$color.';}</style></defs><title>Asset 1</title><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="m 28.733162,52.310099 c -2.29574,-0.33749 -4.44505,-1.91756 -5.47672,-4.02623 -0.4755,-0.97189 -0.63911,-1.69244 -0.71573,-3.15212 -0.0546,-1.04073 -0.21702,-1.98992 -0.36089,-2.10933 -0.15109,-0.12539 -3.46992,-0.24704 -7.85699,-0.288 -7.2247996,-0.0674 -7.6471596,-0.0948 -8.6563696,-0.56123 -1.55837,-0.72022 -3.00662,-2.17311 -3.86103,-3.87342 l -0.75081,-1.49414 V 22.716139 8.6266486 l 0.62291,-1.30862 c 0.8586,-1.80377 2.20022,-3.17902 3.96775,-4.06721 l 1.49415,-0.75081 h 5.2454796 c 4.32107,0 5.32093,0.06 5.67365,0.34037 0.2355,0.1872 2.53827,2.40811 5.11727,4.93535 l 4.68908,4.5949704 h 5.56473 5.56473 l 0.88497,0.65524 c 1.73945,1.2879 10.71617,10.55098 10.90965,11.25767 0.10251,0.3744 0.18638,5.47994 0.18638,11.34562 0,12.24061 0.0156,12.12483 -1.92212,14.24503 -1.27485,1.39488 -2.64367,2.14155 -4.47621,2.44168 -1.51851,0.2487 -14.14407,0.24405 -15.84388,-0.006 z m 16.77969,-3.86172 c 2.17416,-1.11963 2.04939,-0.40969 2.12622,-12.09808 0.0371,-5.63826 0.005,-10.59466 -0.0718,-11.01421 -0.0765,-0.41955 -0.38302,-1.00669 -0.68108,-1.30475 -0.50797,-0.50797 -0.70417,-0.54192 -3.13114,-0.54192 -2.27583,0 -2.66156,-0.0569 -3.18708,-0.4703 -0.55701,-0.43814 -0.59856,-0.59715 -0.60777,-2.32585 -0.0139,-2.61475 -0.28074,-3.78082 -1.00831,-4.40666 -0.5928,-0.50989 -0.70603,-0.52066 -4.8432,-0.46043 l -4.23621,0.0617 -0.0595,8.72205 c -0.0365,5.35702 0.0246,8.80613 0.15848,8.93999 0.13933,0.13932 1.47053,-0.73371 3.69007,-2.42003 l 3.47212,-2.63798 0.0567,1.98029 c 0.0924,3.2292 -0.16083,2.97716 3.06594,3.05116 l 2.7771,0.0637 0.0667,1.64512 0.0667,1.64511 h -2.60323 c -3.33326,0 -3.31638,-0.0147 -3.31638,2.87689 0,1.65488 -0.0677,2.13776 -0.28791,2.05326 -0.15835,-0.0608 -1.78588,-1.24596 -3.61674,-2.63377 -1.83086,-1.38781 -3.379,-2.52329 -3.44032,-2.52329 -0.0613,0 -0.47752,0.76105 -0.92492,1.69122 -0.59256,1.23199 -1.12592,1.9547 -1.96422,2.66157 l -1.15078,0.97035 0.0764,2.17776 c 0.0654,1.86653 0.15595,2.29379 0.63327,2.9897 0.60959,0.88875 1.52464,1.51501 2.60413,1.78228 0.38306,0.0948 3.96708,0.15476 7.96449,0.13315 l 7.26802,-0.0393 z m -21.54007,-9.52145 c 1.07648,-0.51757 1.47672,-0.83633 1.9959,-1.58961 0.35078,-0.50896 0.38668,-1.54553 0.38668,-11.16477 0,-7.73784 -0.0733,-10.76452 -0.27109,-11.19869 -0.44966,-0.98692 -1.26168,-1.24168 -3.95762,-1.24168 -2.18875,0 -2.48925,-0.0501 -2.96113,-0.49338 -0.48008,-0.45101 -0.52518,-0.68547 -0.52518,-2.73024 0,-4.2842604 -0.29444,-4.4914104 -6.3841,-4.4914104 -4.8987796,0 -5.7028596,0.19473 -6.7903996,1.64447 l -0.55371,0.73811 -0.17481,10.3245204 c -0.0961,5.67849 -0.1472,12.01725 -0.11346,14.08614 0.0595,3.64702 0.0798,3.78947 0.66683,4.67555 0.35385,0.53413 1.01393,1.11611 1.58838,1.40046 0.94653,0.46852 1.26438,0.48635 8.5844896,0.48173 7.0287,-0.004 7.66997,-0.0377 8.50922,-0.4412 z m -9.46575,-9.01094 -0.0646,-2.45561 -2.77968,-0.0637 -2.7796796,-0.0637 v -1.69486 -1.69486 h 2.5641096 c 1.69176,0 2.65675,-0.0926 2.83641,-0.27229 0.17474,-0.17474 0.27229,-1.06901 0.27229,-2.49604 0,-1.23532 0.0947,-2.22374 0.21313,-2.22374 0.35339,0 8.63726,6.41021 8.62626,6.67516 -0.009,0.22037 -8.11154,6.49486 -8.61602,6.67224 -0.11486,0.0404 -0.23654,-1.02448 -0.27225,-2.38258 z"/></g></g></svg>' );
    }

    /**
     * Options page callback
     */
    public function create_admin_home_page() {
      ?>
      <h1> <?php esc_html_e( 'WP Custom Post Comparison', 'wpcp_comparison' );?> </h1>
      <div class="wrap about-wrap" >
        <div class="home_page_welcome_div">
          <span class="welcome_span_msg">
          <?php 
            esc_html_e( 'Welcome to CP Comparison - Custom Fields Creator, Custom Post Types Creator and Custom Post Comparison', 'wpcp_comparison' );?></span><br><br>
          <span class="about_cp_span">
            <?php 
            esc_html_e( 'CP Comparison helps you create custom fields, custom post types in just a couple of clicks, directly from the WordPress admin interface. CP Comparison will also compare two custom post and dispaly the comparison details in atable. CP Comparison content types will improve the usability of the sites you build, making them easy to manage by your clients.', 'wpcp_comparison' );?>
          </span>
        </div>
        <div>
        </div>
        <div class="changelog">
          <h2><?php esc_html_e( 'Quick Start-Up Guide', 'wpcp_comparison' ); ?></h2>

          <div class="feature-section">

            <h4><?php esc_html_e( 'Custom Fields Creator', 'wpcp_comparison' ); ?></h4>
            <p><?php esc_html_e( 'Create & manage all your custom fields.', 'wpcp_comparison' ); ?></p>

            <h4><?php esc_html_e( 'Post Type Creator', 'wpcp_comparison' ); ?></h4>
            <p><?php esc_html_e( 'Create & manage all your custom content types.', 'wpcp_comparison' ); ?></p>

            <h4><?php esc_html_e( 'Custom Post Comparison', 'wpcp_comparison' ); ?></h4>
            <p><?php esc_html_e( 'Compare the fields of two custom post and display the comparison table in any page using Shortcode. ', 'wpcp_comparison' ); ?></p>
          </div>
        </div>
      </div>
      <?php
    }

  	/**
  	 * Options page callback
  	 */
  	public function create_admin_page_custom_post() {
  		// Set class property
      $this->options = get_option( 'custom_post_type_option' ); ?>
      <h1><?php esc_html_e( 'Custom Post Type Creator', 'wpcp_comparison' ); ?></h1>
      <div class="custom_post_type_div" >
        <form method="post" action="options.php">
          <?php settings_fields( 'custom_post_option_group' ); 
          do_settings_sections( 'custom_post_option_group' ); ?>
          <ul id="dynamic-post-type">
            <li id="">
              <table cellspacing="20" width="900px">
                <tr>
                  <th colspan="2">
                    <?php esc_html_e( 'Add Custom Post Type', 'wpcp_comparison' ); ?>
                  </th>
                </tr>
                <tr>
                  <td valign="top" width="200px"><?php esc_html_e( 'Post Type:', 'wpcp_comparison' ); ?><span class="post_type_details_span">*</span></td>
                  <td>
                    <input type="text" name="custom_post_type_option[post_type]" id="post_type" required="required" maxlength="20" pattern="[a-z]{}"><br>
                    <?php esc_html_e( 'Max. 20 characters, can not contain capital letters, hyphens, or spaces', 'wpcp_comparison' ); ?>
                  </td>                  
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Description:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <textarea name="custom_post_type_option[post_description]" id="post_description"></textarea><br>
                    <?php esc_html_e( 'A short descriptive summary of what the post type is.', 'wpcp_comparison' ); ?>  
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Singular Label:', 'wpcp_comparison' ); ?><span class="post_type_details_span">*</span></td>
                  <td><input type="text" name="custom_post_type_option[singular_label]" id="singular_label" required="required"><br>
                    <?php esc_html_e( 'Ex:Book', 'wpcp_comparison' ); ?>  
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Plural Label:', 'wpcp_comparison' ); ?><span class="post_type_details_span">*</span></td>
                  <td><input type="text" name="custom_post_type_option[plural_label]" id="plural_label" required="required"><br>
                    <?php esc_html_e( 'Ex:Books', 'wpcp_comparison' ); ?>  
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Hierarchical:', 'wpcp_comparison' ); ?></td>
                  <td>  
                    <select name="custom_post_type_option[hierarchical]" id="hierarchical">
                      <option value="false" selected="selected">false</option>
                      <option value="true">true</option>
                    </select><br>
                    <?php esc_html_e( 'Whether the post type is hierarchical. Allows Parent to be specified.', 'wpcp_comparison' ); ?>  
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Has Archive:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <select name="custom_post_type_option[has_archive]" id="has_archive">
                      <option value="false" >false</option>
                      <option value="true" selected="selected">true</option>
                    </select><br>
                    <?php esc_html_e( 'Enables post type archives. Will use string as archive slug. Will generate the proper rewrite rules if rewrite is enabled.', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Supports:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <input type="checkbox" name="custom_post_type_option[supports][]" id="title" value="title" checked="checked"><?php esc_html_e( 'Title', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[supports][]" id="editor" value="editor" checked="checked"><?php esc_html_e( 'Editor', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[supports][]" id="author" value="author"><?php esc_html_e( 'Author', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[supports][]" id="thumbnail" value="thumbnail"><?php esc_html_e( 'Thumbnail', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[supports][]" id="trackbacks" value="trackbacks"><?php esc_html_e( 'Trackbacks', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[supports][]" id="custom-fields" value="custom-fields"><?php esc_html_e( 'Custom-fields', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[supports][]" id="comments" value="comments"><?php esc_html_e( 'Comments', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[supports][]" id="revisions" value="revisions"><?php esc_html_e( 'Revisions', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[supports][]" id="page-attributes" value="page-attributes"><?php esc_html_e( 'Page-attributes', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[supports][]" id="post-formats" value="post-formats"><?php esc_html_e( 'Post-formats', 'wpcp_comparison' ); ?><br>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Add New:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[add_new]" id="add_new"><br>
                    <?php esc_html_e( 'ex. Add New', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Add New Item:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[add_new_item]" id="add_new_item"><br>
                    <?php esc_html_e( 'ex. Add New Book', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Edit Item:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[edit_item]" id="edit_item"><br>
                    <?php esc_html_e( 'ex. Edit Book' ); ?>
                  </td>
                </tr>           
                <tr>
                  <td valign="top"><?php esc_html_e( 'New Item:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[new_item]" id="new_item"><br>
                    <?php esc_html_e( 'ex. New Book', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'All Items:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[all_items]" id="all_items"><br>
                    <?php esc_html_e( 'ex. All Books', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'View Items:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[view_items]" id="view_items"><br>
                    <?php esc_html_e( 'ex. View Books', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Search Items:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[search_items]" id="search_items"><br>
                    <?php esc_html_e( 'ex. Search Items', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Not Found:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[not_found]" id="not_found"><br>
                    <?php esc_html_e( 'ex. No Books Found', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Not Found In Trash:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[not_found_in_trash]" id="not_found_in_trash"><br>
                    <?php esc_html_e( 'ex. No Books Found in Trash', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Parent Item Colon:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[parent_item_colon]" id="parent_item_colon"><br>
                    <?php esc_html_e( 'the parent text. This string is not used on non-hierarchical types. In hierarchical ones the default is Parent Page', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Menu Name:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[menu_name]" id="menu_name"><br>
                    <?php esc_html_e( 'ex. books', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Featured Image:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[featured_image]" id="featured_image"><br>
                    <?php esc_html_e( 'ex. Featured Image', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Set Featured Image:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[set_featured_image]" id="set_featured_image"><br>
                    <?php esc_html_e( 'ex. Set Featured Image', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Remove Featured Image:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[remove_featured_image]" id="remove_featured_image"><br>
                    <?php esc_html_e( 'ex. Remove Featured Image', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Use Featured Image:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[use_featured_image]" id="use_featured_image"><br>
                    <?php esc_html_e( 'ex. Use Featured Image', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Archives:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[archives]" id="archives"><br>
                    <?php esc_html_e( 'ex. Archives', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Insert Into Item:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[insert_into_item]" id="insert_into_item"><br>
                    <?php esc_html_e( 'ex. Insert Into Item', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Uploaded to this Item:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[uploaded_to_this_item]" id="uploaded_to_this_item"><br>

                    <?php esc_html_e( 'ex. Uploaded to this Item', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Filter Items List:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[filter_items_list]" id="filter_items_list"><br>
                    <?php esc_html_e( 'ex. Filter Item List', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Items List Navigation:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[items_list_navigation]" id="items_list_navigation"><br>
                    <?php esc_html_e( 'ex. Items List Navigation', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Items List:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[items_list]" id="items_list"><br>
                    <?php esc_html_e( 'ex. Items List', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Public:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <select name="custom_post_type_option[public]" id="public">
                      <option value="false">false</option>
                      <option value="true" selected="selected">true</option>
                    </select><br>
                    <?php esc_html_e( 'Meta argument used to define default values for publicly_queriable, show_ui, show_in_nav_menus and exclude_from_search', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Show UI:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <select name="custom_post_type_option[show_ui]" id="show_ui">
                      <option value="false">false</option>
                      <option value="true" selected="selected">true</option>
                    </select><br>
                    <?php esc_html_e( 'Whether to generate a default UI for managing this post type.', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Show In Nav Menus:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <select name="custom_post_type_option[show_in_nav_menus]" id="show_in_nav_menus">
                      <option value="false">false</option>
                      <option value="true" selected="selected">true</option>
                    </select><br>
                    <?php esc_html_e( 'Whether post_type is available for selection in navigation menus.', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Show In Menu:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <select name="custom_post_type_option[show_in_menu]" id="show_in_menu">
                      <option value="false">false</option>
                      <option value="true" selected="selected">true</option>
                    </select><br>
                    <?php esc_html_e( 'Whether to show the post type in the admin menu. show_ui must be true. "false" - do not display in the admin menu, "true" - display as a top level menu, "some string" - If an existing top level page such as "tools.php" or "edit.php?post_type=page", the post type will be placed as a sub menu of that.', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Menu Position:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[menu_position]" id="menu_position"><br>
                    <?php esc_html_e( 'The position in the menu order the post type should appear.', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Menu Icon:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[menu_icon]" id="menu_icon"><br>
                    <?php esc_html_e( 'The url to the icon to be used for this menu.', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Capability Type:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[capability_type]" value="post" id="capability_type"><br>
                    <?php esc_html_e( 'The string to use to build the read, edit, and delete capabilities.', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Taxonomies:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <input type="checkbox" name="custom_post_type_option[taxonomies][]" id="category" value="category"><?php esc_html_e( 'Category', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[taxonomies][]" id="post_tag" value="post_tag"><?php esc_html_e( 'Post_tag', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[taxonomies][]]" id="product_cat" value="product_cat"><?php esc_html_e( 'Product_Cat', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[taxonomies][]" id="product_tag" value="product_tag"><?php esc_html_e( 'Product_tag', 'wpcp_comparison' ); ?><br>
                    <input type="checkbox" name="custom_post_type_option[taxonomies][]" id="product_shipping_class" value="product_shipping_class"><?php esc_html_e(' Product_Shipping_Class', 'wpcp_comparison' ); ?><br>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Rewrite:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <select name="custom_post_type_option[rewrite]" id="rewrite">
                      <option value="false">false</option>
                      <option value="true" selected="selected">true</option>
                    </select><br>
                    <?php esc_html_e( 'Rewrite permalinks.', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'With Front:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <select name="custom_post_type_option[with_front]" id="with_front">
                      <option value="false">false</option>
                      <option value="true" selected="selected">true</option>
                    </select><br>
                    <?php esc_html_e( 'Use the defined base for permalinks.', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Rewrite Slug:', 'wpcp_comparison' ); ?></td>
                  <td><input type="text" name="custom_post_type_option[rewrite_slug]" id="rewrite_slug"><br>
                    <?php esc_html_e( 'Defaults to post type name.', 'wpcp_comparison' ); ?>
                  </td>
                </tr>
                <tr>
                  <td valign="top"><?php esc_html_e( 'Show In REST API:', 'wpcp_comparison' ); ?></td>
                  <td>
                    <select name="custom_post_type_option[show_in_rest_api]" id="show_in_rest_api">
                      <option value="false" selected="selected">false</option>
                      <option value="true">true</option>
                    </select><br>
                    <?php esc_html_e( 'Make this post type available via WP REST API.', 'wpcp_comparison' ); ?>
                  </td>
                </tr> 
                <tr>
                  <th colspan="2">
                    <?php submit_button( $name = 'Submit' ); ?>
                  </th>
                </tr>
              </table>
            </li>
            <h1 align="center"><?php esc_html_e( 'Available Custom Post Types', 'wpcp_comparison' ); ?></h1><br><br>
            <table class="show_post_details_table" border="1">
              <tr>
                <th width="50px">#</th>
                <th><?php esc_html_e( 'Content', 'wpcp_comparison' ); ?></th>
                <th><?php esc_html_e( 'Delete', 'wpcp_comparison' ); ?></th>
                <th><?php esc_html_e( 'Edit', 'wpcp_comparison' ); ?></th>
              </tr> <?php
              if( !empty( $this->options ) ) {
                foreach ( $this->options as $key => $value ) {
                  if ( sizeof( $value ) == 1 ) {
                    foreach ( $value as $k => $val ) { ?>
                      <tr>
                        <th><?php echo $k+1; ?></th>
                        <td>
                          <table class="show_post_details_inner_table" cellspacing="20px">
                            <tr>
                              <td valign="top" width="150px"><b><?php esc_html_e( 'Post Type:', 'wpcp_comparison' ); ?></b></td>
                              <td><?php echo $val['post_type']; ?></td>
                            </tr>
                            <tr>
                              <td valign="top"><b><?php esc_html_e( 'Post Description:', 'wpcp_comparison' );?></b></td>
                              <td><?php echo $val['post_description']; ?></td>
                            </tr>
                            <tr>
                              <td valign="top"><b><?php esc_html_e( 'Singular Label:', 'wpcp_comparison' ); ?></b></td>
                              <td><?php echo $val['singular_label']; ?></td>
                            </tr>
                            <tr>
                              <td valign="top"><b><?php esc_html_e( 'Plural Label:', 'wpcp_comparison' ); ?></b></td>
                              <td><?php echo $val['plural_label']; ?></td>
                            </tr>
                            <tr>
                              <td valign="top"><b><?php esc_html_e( 'Hierarchical:', 'wpcp_comparison' ); ?></b></td>
                              <td><?php echo $val['hierarchical']; ?></td>
                            </tr>
                            <tr>
                              <td valign="top"><b><?php esc_html_e( 'Has Archives:', 'wpcp_comparison' ); ?></b></td>
                              <td><?php echo $val['has_archive']; ?></td>
                            </tr>
                            <tr>
                              <td valign="top"><b><?php esc_html_e( 'Supports:', 'wpcp_comparison' ); ?></b></td>
                              <td> <?php
                              foreach ( $val as $index => $data ) {
                                if ( $index === 'supports' ) {
                                  for ( $i=0; $i < sizeof( $val['supports'] ); $i++ ) { 
                                    echo $val['supports'][$i] . ' , ';
                                  } 
                                }
                              } ?>
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td>
                          <input type="button" style="color:red" name="delete" value="Delete" onclick="deletePostType( <?php echo $k;?> )">
                        </td>
                        <td>
                          <input type="button" name="edit" value="Edit" onclick="editPostType( <?php echo $k;?> )">
                        </td>
                      </tr> <?php
                    }
                  }
                  else { ?>
                    <tr>
                      <th><?php echo $key+1; ?></th>
                      <td>
                        <table class="show_post_details_inner_table" cellspacing="20px" >
                          <tr>
                            <td valign="top" width="150px"><b><?php esc_html_e( 'Post Type:', 'wpcp_comparison' ); ?></b></td>
                            <td><?php echo $value['post_type']; ?></td>
                          </tr>
                          <tr>
                            <td valign="top"><b><?php esc_html_e( 'Post Description:', 'wpcp_comparison' ); ?></b></td>
                            <td><?php echo $value['post_description']; ?></td>
                          </tr>
                          <tr>
                            <td valign="top"><b><?php esc_html_e( 'Singular Label:', 'wpcp_comparison' ); ?></b></td>
                            <td><?php echo $value['singular_label']; ?></td>
                          </tr>
                          <tr>
                            <td valign="top"><b><?php esc_html_e( 'Plural Label:', 'wpcp_comparison' ); ?></b></td>
                            <td><?php echo $value['plural_label']; ?></td>
                          </tr>
                          <tr>
                            <td valign="top"><b><?php esc_html_e( 'Hierarchical:', 'wpcp_comparison' ); ?></b></td>
                            <td><?php echo $value['hierarchical']; ?></td>
                          </tr>
                          <tr>
                            <td valign="top"><b><?php esc_html_e( 'Has Archives:', 'wpcp_comparison' ); ?></b></td>
                            <td><?php echo $value['has_archive']; ?></td>
                          </tr>
                          <tr>
                            <td valign="top"><b><?php esc_html_e( 'Supports:', 'wpcp_comparison' ); ?></b></td>
                            <td> 
                              <?php 
                              foreach ( $value as $index => $data ) {
                                if ( $index === 'supports' ) {
                                  for ( $i=0; $i < sizeof( $value['supports'] ); $i++ ) { 
                                    echo $value['supports'][$i] . ' , ';
                                  } 
                                }
                              } 
                              ?>
                            </td>
                          </tr>
                        </table>
                      </td>
                      <td>
                        <input type="button" style="color:red" name="delete" value="Delete" onclick="deletePostType( <?php echo $key;?> )">
                      </td>
                      <td>
                        <input type="button" name="edit" value="Edit" onclick="editPostType( <?php echo $key;?> )">
                      </td>
                    </tr> 
                    <?php  
                  }              
                }              
              }
              else { 
                ?>
                <tr>
                  <td colspan="4" style="text-align: center"><?php esc_html_e( 'No Custom Post Type available .', 'wpcp_comparison' ); ?></td>
                </tr>
                <?php
              } 
              ?>
            </table><br>
          </ul>
        </form>
      </div> 
      <?php
    }

    /**
     * Registers a text field setting for Wordpress .
     */
    public function page_init() {        
      register_setting(
  			'custom_post_option_group',  // Option group
  			'custom_post_type_option',   // Option name
  			array( $this, 'sanitize' )   // Sanitize
      );
    }

  	/**
     * 
  	 * Sanitize each setting field as needed
  	 *
  	 * @param array $input Contains all settings fields as array keys
     * @return array
  	 */
  	public function sanitize( $input )
  	{
      $new_input = array();
      if ( get_option( 'custom_post_type_option' ) != '' ) {
        $option_data = get_option( 'custom_post_type_option' );
        if ( $option_data[0][0] != '' || $option_data[0]['post_type'] != '' ) {    
          foreach ( $option_data as $k => $val ) {
            array_push( $new_input, $val );
          }        
        }
      }
      $arr = array();
      // for sanitize all the field
      foreach ( $input as $key => $value ) {
        if ( is_array( $value ) ) {
          $arr[$key] = $value;
        }
        else{ 
          $arr[$key] = sanitize_text_field( $value ); 
        }      
      }
      //Checking the post type is present or not
      foreach ( $new_input as $key => $value ) {
        if ( sizeof( $value ) == 1 ) {
          foreach ( $value as $k => $val ) {
            if ( in_array( $arr['post_type'], $val ) && in_array( $arr['singular_label'], $val ) && in_array( $arr['plural_label'], $val ) ) { 
              array_splice( $new_input, $k, 1 );  
            } 
          }
        }
        else {
          if ( in_array( $arr['post_type'], $value ) && in_array( $arr['singular_label'], $value ) && in_array( $arr['plural_label'], $value ) ) {
            array_splice( $new_input, $key, 1 );
          }  
        }      
      }
      array_push( $new_input, $arr );  
      return $new_input;
    }

    /**
     * This function adds a meta box with a callback function of custom_field_callback()
     */
    public function add_custom_field_metaboxes() {
      $post_array = array();
      $get_all_custom_post_type = get_option( 'custom_post_type_option' );
      foreach ( $get_all_custom_post_type as $key => $value ) {
        if ( sizeof( $value ) == 1 ) {
          foreach ( $value as $val ) {
            array_push( $post_array, $val['post_type'] );
          }
        }
        else {
          array_push( $post_array, $value['post_type'] );  
        }      
      }
      foreach ( $post_array as $value ) {
        add_meta_box(
          'wpt_events_location',
          __( 'Add Fields', 'wpcp_comparison' ),
          array( $this, 'custom_field_callback' ),
          $value,
          'advanced',
          'default'
        );
      }    
    }

    /**
     * Output the HTML for the metabox.
     */
    public function custom_field_callback() {
      global $post;
      $meta = get_post_meta( $post->ID, 'custom_fields', true );
      $fields = get_option( 'option_page_name' );
      $fields_custom_array = json_encode( $fields );
      if ( is_array( $meta ) ) {
        if ( sizeof( $meta ) != 1 ) {
          // Check if the custom field is available or not
          foreach ( $meta as $key => $value ) {
            if ( is_int( $key ) ) {
              if ( !in_array( $value, $fields ) ) {
                unset( $meta[$key] );
                unset( $meta["value".$key] );
              }
            }
          }
          update_post_meta( $post->ID, 'custom_fields', $meta );
          $count = 1; 
          ?>  
          <input type="hidden" name="meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">
          <table width="400">
            <tr>
              <td><?php esc_html_e( 'Field Name', 'wpcp_comparison' ); ?></td>
              <td><?php esc_html_e( 'Field Value', 'wpcp_comparison' ); ?></td>
            </tr>
          </table>
          <ul id="dynamic-list"> 
            <?php
            foreach ( $meta as $key => $value ) {
              if ( $value !== '' ) {        
                if ( is_int( $key ) ) {
                  if( $meta['value'.$key] !== '' ) { 
                    ?>
                    <li id="custom_fields[<?php echo $key; ?>]">
                      <select name="custom_fields[<?php echo $key; ?>]" id="custom_fields[<?php echo $key; ?>]"> 
                        <?php                        
                        if ( sizeof($fields) !== 0 ) {
                          foreach ( $fields as $key_field => $value_field ) {
                            if ( $value_field === $meta[$key] ) { 
                              ?>
                              <option value="<?php echo $meta[$key]; ?>" selected="selected">
                                <?php echo $meta[$key]; ?>    
                              </option> 
                              <?php    
                            }
                            else { 
                              ?>
                              <option value="<?php echo $value_field; ?>"> <?php echo $value_field; ?> </option> <?php
                            }    
                          }  
                        }
                        else { 
                          ?>
                          <option value="<?php echo $meta[$key]; ?>" selected="selected">
                            <?php echo $meta[$key]; ?>    
                          </option> 
                          <?php 
                        }                      
                        $fields_custom_array = json_encode( $fields ); ?>                  
                      </select> 
                      <?php
                    $count = $key;
                  } 
                }
                else { ?>
                  <input type="text" name="custom_fields[<?php echo $key; ?>]" id="custom_fields[<?php echo $key; ?>]" placeholder="Value" value="<?php echo $meta[$key]; ?>">

                  <input type="button" value="X" id="delete" onclick="deleteField('custom_fields[<?php echo $count; ?>]')" />
                </li>
                <br> <?php 
              }
            }
          }
          $count = $count + 1; ?>
          </ul>
          <?php
          if ( is_array($fields) && (sizeof($fields) > 0 ) ) {
            ?>
          <input type="button" value="Add New" onclick='addField( <?php echo $count; ?>,<?php echo $fields_custom_array; ?> )'/> <?php         
          } else {
            echo "Please add custom fields first ."; 
            ?>
              <a href="<?php echo get_admin_url(); ?>admin.php?page=cp-custom-field"><?php esc_html_e( 'Click Here', 'wpcp_comparison' ); ?></a>
            <?php 
          }
        }      
      } else { 
        ?>
        <table width="400">
          <tr>
            <td><?php esc_html_e( 'Field Name', 'wpcp_comparison' ); ?></td>
            <td><?php esc_html_e( 'Field Value', 'wpcp_comparison' ); ?></td>
          </tr>
        </table>
        <?php
        if ( is_array($fields) && (sizeof($fields) > 0) ) { 
          ?>
          <ul id="dynamic-list">
            <li id="custom_fields[0]">
              <select name="custom_fields[0]" id="custom_fields[0]"> 
                <?php
                $fields = get_option( 'option_page_name' );
                foreach ( $fields as $key => $value ) { ?>
                  <option value="<?php echo $value; ?>"><?php echo $value; ?></option> <?php    
                }
                $fields_custom_array = json_encode( $fields ); ?>                  
              </select>
              <input type="text" name="custom_fields[value0]" id="custom_fields[value0]" value="" placeholder="Value">
              <input type="button" value="X" id="delete" onclick="deleteField( 'custom_fields[0]' )" />
              <br>
            </li> 
          </ul>
          <input type="button" value="Add New" id="add_fields_of_post"/> <?php
        } else {
          esc_html_e( 'Please add custom fields first .', 'wpcp_comparison' ); 
          ?>
            <a href="<?php echo get_admin_url(); ?>admin.php?page=cp-custom-field"><?php esc_html_e( 'Click Here', 'wpcp_comparison' ); ?></a>
          <?php 
        }
      } 
    }

    /**
     * Save the metabox data
     */
    public function save_custom_field_meta( $post_id ) {
      // verify nonce
      if ( isset( $_POST['meta_box_nonce'] ) 
        && !wp_verify_nonce( $_POST['meta_box_nonce'], basename(__FILE__) ) ) {
        return $post_id; 
      }
      // check autosave
      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
      }
      // check permissions
      if ( isset( $_POST['post_type'] ) ) { 
        if ( 'page' === $_POST['post_type'] ) {
          if ( !current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
          } 
          elseif ( !current_user_can( 'edit_post', $post_id ) ) 
          {
            return $post_id;
          }  
        }
      }
      $old = get_post_meta( $post_id, 'custom_fields', true );
      $new = array();
      if ( isset( $_POST['custom_fields'] ) ) {
        foreach ($_POST['custom_fields'] as $key => $value) {
          $new[$key] = sanitize_text_field($value);
        }
        // removing duplicate fields
        $new_arr = array();
        foreach ( $new as $key => $value ) {
          if ( is_int($key) ) {
            if ( in_array( $value, $new_arr ) ) {
              unset( $new[$key] );
              unset( $new["value".$key] );
            } else {
              $new_arr[$key] = $value;
            }
          } else{
            $new_arr[$key] = $value;
          }
        }
        if ( $new && $new !== $old ) {
          update_post_meta( $post_id, 'custom_fields', $new );
        } 
        elseif ( '' === $new && $old ) {
          delete_post_meta( $post_id, 'custom_fields', $old );
        }
      } else {
        delete_post_meta( $post_id, 'custom_fields', $old );      
      }    
    }


    /**
     * Add option page callback for custom fields.
     */
    public function create_admin_page_custom_field() {
      // Set class property
      $this->options = get_option( 'option_page_name' ); ?>
      <div class="wrap">
        <form method="post" action="options.php">
          <?php settings_fields( 'option_page_group' );
          do_settings_sections( 'option_page_group' ); ?>
          <table>
            <tr>
              <th colspan="2"><h1><?php esc_html_e( 'Create Custom Field', 'wpcp_comparison' ); ?></h1></th>
            </tr>
            <tr>
              <td><?php esc_html_e( 'Field Name:', 'wpcp_comparison' ); ?></td>
            </tr>
          </table> <?php
          if ( empty( $this->options ) ) { 
            ?>
            <ul id="dynamic-custom-field-list">
              <li id="custom-field-list[0]">
                <input type="text" name="option_page_name[field_name0]" id="field_name0" required="required">
               <input type="button" value="X" id="deletefield" onclick="deleteCustomField( 'custom-field-list[0]' )" />  
              </li>
            </ul>
            <input type="button" id = "add_custom_field" value="Add More" /> <?php
          } else { 
            ?>
            <ul id="dynamic-custom-field-list"> <?php
              foreach ( $this->options as $key => $value ) {
                if ( $value !== '' ) { ?>
                  <li id="custom-field-list[<?php echo $key; ?>]">
                    <input type="text" name="option_page_name[field_name <?php echo $key; ?>]" id="field_name<?php echo $key; ?>" required="required" value="<?php echo $value; ?>"/>
                    <input type="button" value="X" id="deletefield" onclick="deleteCustomField( 'custom-field-list[<?php echo $key; ?>]' )" />  
                  </li> 
                  <?php
                }                           
              } 
              ?>
            </ul>
            <input type="button" id = "add_custom_field" value="Add More"  /> <?php
          } ?>     
          <?php submit_button( $name = 'Save' ); ?>
        </form>
      </div> <?php
    }

    /**
     * Registers a text field setting for Wordpress .
     */
    public function option_page_init() {        
      register_setting( 'option_page_group', 'field_name' );
      register_setting(
        'option_page_group', //option group
        'option_page_name', //option name
        array( $this, 'sanitizefield' )
      );      
    }

    /**
     * 
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * @return array
     */
    public function sanitizefield( $input )
    {
      $new_input = array();
      foreach ( $input as $key => $value ) {
        if( isset( $input[$key] ) ) {
          if ( !(in_array( $input[$key], $new_input)) ) {
            array_push( $new_input, sanitize_text_field($value) ); 
          }          
        }            
      }        
      return $new_input;
    }

    /**
     * Verify ajax nonce
     * 
     * Getting all the custom fields for displaying in a dropdown list.   * 
     */
    public function wp_cp_get_customfield() {
      check_ajax_referer( 'ajax-nonce', 'security' );
      $get_all_fields = get_option( 'option_page_name' );
      wp_send_json( $get_all_fields );
      wp_die();
    }

    /**
     * Verify ajax nonce
     * 
     * Delete the custom post type
     * 
     * Update the option page custom_post_type_option
     */
    public function wp_cp_comparison_deletepost() {
      check_ajax_referer( 'ajax-nonce', 'security' );
      $id = intval( $_POST['id'] );
      $new_option_value = array();
      $get_all_post = get_option( 'custom_post_type_option' );
      
      if ( $id == 0 ) {
        array_splice( $get_all_post, $id, 1 );
      } else {
        array_splice( $get_all_post, $id, 1 );  
      }
      delete_option( 'custom_post_type_option' );
      foreach ( $get_all_post as $key => $value ) {
        if ( sizeof( $value ) == 1 ) {
          foreach ( $value as $k => $val ) {
            update_option( 'custom_post_type_option', $val ); 
          }
        }
        else {
          update_option( 'custom_post_type_option', $value );  
        }    
      }
      wp_die();
    }

    /**
     * Verify ajax nonce field
     *
     * Getting the selected custom post type details and send to the ajax success
     *
     * @return array as responce using wp_send_json_success()
     */
    public function wp_cp_comparison_editPost() {
      check_ajax_referer( 'ajax-nonce', 'security' );
      $id = intval( $_POST['id'] );
      if ( $id == 0 ) {
        $get_post_details = get_option( 'custom_post_type_option' );
        if ( sizeof( $get_post_details[0] ) == 1 ) {
          $get_post = $get_post_details[0];
        } else {
          $get_post = get_option( 'custom_post_type_option' );
        }       
      } else {
        $get_post = get_option( 'custom_post_type_option' ); 
      }
      $response = $get_post[$id];
      wp_send_json_success( $response );
      wp_die();  
    }    
  }


  /**
   * Checking the user is admin or not and creating object to the class
   */
  if( is_admin() )
    $post_type_option_page_ = new WPCPC();


  /**
   * Shortcode for comparison of two post of custom post type and display the comparison table on pages.
   *
   *@param array of ids of post 
   *
   */
  function wp_post_comparison_shortcode( $atts ) {

    $extract_data = shortcode_atts( array( 'ids' => '' ), $atts );
    $arr_ids = explode( ',', $extract_data['ids'] );
    
    if ( sizeof( $arr_ids ) == 2 ) {
      $all_fields = get_post_meta( $arr_ids[0], 'custom_fields', true );
      $x = array();
      $y = array();
      $z = array();
      foreach ( $all_fields as $key => $val ) {
        if ( is_int( $key ) ) {
          array_push( $x, $val );
        } else {
          array_push( $y, $val );
        }      
      }

      for ( $i=0; $i < sizeof( $x ); $i++ ) { 
        $z[$x[$i]] = $y[$i];
      }

      $all_field = get_post_meta( $arr_ids[1], 'custom_fields', true );
      $m = array();
      $n = array();
      $k = array();
      foreach ( $all_field as $key => $val ) {
        if ( is_int( $key ) ) {
          array_push( $m, $val );
        } else {
          array_push( $n, $val );
        }      
      }
      for ( $i=0; $i < sizeof( $m ); $i++ ) { 
        $k[$m[$i]] = $n[$i];
      } 
      ?>    
      <table style="width: 600px;">
        <tr>
          <td style="width: 200px;"></td>
          <th style="width: 200px;">
            <h2><?php esc_html_e( get_the_title( $arr_ids[0] ), 'zetapay' ); ?></h2>
          </th>
          <th style="width: 200px;">
            <h2><?php esc_html_e( get_the_title( $arr_ids[1] ), 'zetapay' ); ?></h2>
          </th>
        </tr>
        <?php
        $option_page_fields = get_option( 'option_page_name' );
        foreach ( $option_page_fields as $value ) { 
          ?>
          <tr>
            <th>
              <?php echo $value; ?>
            </th>
            <td style="text-align: center;"> <?php
              if ( in_array( $value, $x ) ) {
                echo $z[$value];
              } else {
                ?>
                <span style="color: red"><?php esc_html_e( ' X ', 'wpcp_comparison' );?></span> <?php
              } 
              ?>
            </td>
            <td style="text-align: center;"> <?php
              if ( in_array( $value, $m ) ) {
                echo $k[$value];
              } else {
                ?>
                <span style="color: red"><?php esc_html_e( ' X ', 'wpcp_comparison' );?></span> <?php
              } 
              ?>
            </td>
          </tr> 
          <?php
        }
        ?>
      </table> 
      <?php
    } else {
      return __( 'Provide ids for Comparison', 'wpcp_comparison' );
    }
  }

  //Shortcode for Comparison of two post
  add_shortcode( 'wp_custom_post_comparison', 'wp_post_comparison_shortcode' );

endif;

