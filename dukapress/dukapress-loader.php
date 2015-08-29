<?php
if(!class_exists('DukaPress')) {
	
	/**
	 * Main DukaPress Class
	 *
	 * @class DukaPress
	 * @since 2.6
	 */
	class DukaPress{
	
		//Variables to be used
		var $version = '2.6';
		var $location;
		var $language = '';
		var $checkout_error = false;
		var $cart_cache = false;
		var $is_shop_page = false;
		var $global_cart = false;
		var $skip_shipping_notice = false;
		var $weight_printed	= false;
		
		var $defaults = array(
			'related_products'	 => array(
				'product_id'	 => NULL,
				'relate_by'		 => 'both',
				'echo'			 => false,
				'limit'			 => NULL,
				'simple_list'	 => NULL,
			),
		);
		
		var $form_elements = array('Text' => 'text' ,'Text Area' => 'textarea', 'Check Box' => 'checkbox','Paragraph' => 'paragraph');
		
		function __construct() {
			//setup our variables
			$this->init_dpsc();
			
			//Lets install
			add_action( 'plugins_loaded', array( &$this, 'install' ) );
			
			//Call action when admin installs a new site
			add_action( 'wpmu_new_blog', array( &$this, 'setup_new_blog' ), 10, 6 );
			
			//custom post type
			add_action( 'init', array( &$this, 'register_post_types' ), 0 ); //super high priority
			add_filter( 'request', array( &$this, 'handle_edit_screen_filter' ) );
			add_filter( 'post_updated_messages', array( &$this, 'post_updated_messages' ) );
	
			//edit products page
			add_filter( 'manage_duka_posts_columns', array( &$this, 'edit_duka_columns' ) );
			add_action( 'manage_duka_posts_custom_column', array( &$this, 'edit_duka_custom_columns' ) , 10, 2);
			add_action( 'restrict_manage_posts', array( &$this, 'edit_duka_filter' ) );
	
			add_filter( 'post_row_actions', array( &$this, 'edit_duka_custom_row_actions' ), 10, 2 );
			add_filter( 'admin_action_copy-duka', array( &$this, 'edit_products_copy_action' ) );
	
			//manage orders page
			add_filter( 'manage_duka_page_dukapress-orders_columns', array( &$this, 'manage_orders_columns' ) );
			add_action( 'manage_duka_order_posts_custom_column', array( &$this, 'manage_orders_custom_columns' ) );
			
			//Admin Pages
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'admin_print_styles', array( &$this, 'admin_css' ) );
			add_action( 'admin_print_scripts', array( &$this, 'admin_script_post' ) );
			add_action( 'admin_notices', array( &$this, 'admin_nopermalink_warning' ) );
			add_filter( 'plugin_action_links_' . DPSC_BASENAME, array( &$this, 'plugin_action_link' ), 10, 2 );
			
			
			//Meta boxes
			add_action( 'add_meta_boxes_duka', array( &$this, 'meta_boxes' ) );
			add_action( 'wp_insert_post', array( &$this, 'save_product_meta' ), 10, 2 );
			add_filter( 'enter_title_here', array( &$this, 'filter_title' ) );
			
			//Ajax Actions
			add_action('wp_ajax_save_variationdata', array( &$this,'varition_save_data'));
			add_action('wp_ajax_delete_variationdata', array( &$this,'varition_delete_data'));
			
			//Scripts and Styles
			add_action('wp_enqueue_scripts', array(&$this, 'set_up_styles'));
			add_action('wp_enqueue_scripts', array(&$this, 'set_up_js'));
		}
		
		/**
		 * Initialise the variables we need
		 *
		 * @since 2.6
		 */
		function init_dpsc(){
			//load data structures
			require_once( DPSC_DUKAPRESS_LIB_DIR . '/dukapress-data.php' );
			
			$this->start_session(); //Start the session
			
			//Load Classes
			require_once( DPSC_DUKAPRESS_CLASSES_DIR . '/dukapress-install.php' );
			
			//Load Functions
			require_once( DPSC_DUKAPRESS_DIR . '/dukapress-functions.php' );
		}
		
		
		/** 
		 * Set up new Multisite
		 * @since 2.6
		 */
		function setup_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
			//flag to run on next visit to blog
			update_blog_option( $blog_id, 'dukapress_do_install', 1 );
		}
		
		/** 
		 * Begin installing
		 * @since 2.6
		 */
		function install(){
			if ( !get_option( 'dukapress_do_install' ) ) {
				if ( get_option( 'dp_version' ) == $this->version ) {
					return;
				}
			}
			
			DukaPress_Install::init(); //Initialise the options
			
			//add action to flush rewrite rules after we've added them for the first time
		    update_option( 'dukapess_flush_rewrite', 1 );
			
			update_option( 'dp_version', $this->version );
			delete_option( 'dukapress_do_install' );
		}
		
		/*
		 * Get settings array without undefined indexes
		 * @param string $key A setting key, or -> separated list of keys to go multiple levels into an array
		 * @param mixed $default Returns when setting is not set
		 *
		 * @since 2.6
		 */
		function get_setting( $key, $default = null ) {
			$settings	 = get_option( 'dukapress_settings' );
			$keys		 = explode( '->', $key );
			array_map( 'trim', $keys );
			if ( count( $keys ) == 1 )
				$setting	 = isset( $settings[ $keys[ 0 ] ] ) ? $settings[ $keys[ 0 ] ] : $default;
			else if ( count( $keys ) == 2 )
				$setting	 = isset( $settings[ $keys[ 0 ] ][ $keys[ 1 ] ] ) ? $settings[ $keys[ 0 ] ][ $keys[ 1 ] ] : $default;
			else if ( count( $keys ) == 3 )
				$setting	 = isset( $settings[ $keys[ 0 ] ][ $keys[ 1 ] ][ $keys[ 2 ] ] ) ? $settings[ $keys[ 0 ] ][ $keys[ 1 ] ][ $keys[ 2 ] ] : $default;
			else if ( count( $keys ) == 4 )
				$setting	 = isset( $settings[ $keys[ 0 ] ][ $keys[ 1 ] ][ $keys[ 2 ] ][ $keys[ 3 ] ] ) ? $settings[ $keys[ 0 ] ][ $keys[ 1 ] ][ $keys[ 2 ] ][ $keys[ 3 ] ] : $default;
	
			return apply_filters( "dukapress_setting_" . implode( '', $keys ), $setting, $default );
		}
	
		/** 
		 * Update Settings
		 * @param string $key A setting key
		 * @param mixed $value 
		 *
		 * @since 2.6
		 */
		function update_setting( $key, $value ) {
			$settings		 = get_option( 'dukapress_settings' );
			$settings[ $key ]	 = $value;
			return update_option( 'dukapress_settings', $settings );
		}
		
		
		/**  
		 * Starts the Session
		 * @since 2.6
		 */
		function start_session() {
			$sess_id = session_id();
	
			if ( empty( $sess_id ) ) {
				session_start();
			}
		}
		
		/**  
		 * Format Currency
		 *
		 */
		function format_currency( $currency = '', $amount = false ) {

			if ( !$currency )
				$currency = $this->get_setting( 'currency', 'USD' );

			// get the currency symbol
			$symbol	 = $this->currencies[ $currency ][ 1 ];
			// if many symbols are found, rebuild the full symbol
			$symbols = explode( ', ', $symbol );
			if ( is_array( $symbols ) ) {
				$symbol = "";
				foreach ( $symbols as $temp ) {
					$symbol .= '&#x' . $temp . ';';
				}
			} else {
				$symbol = '&#x' . $symbol . ';';
			}

			//check decimal option
			if ( $this->get_setting( 'curr_decimal' ) === '0' ) {
				$decimal_place	 = 0;
				$zero			 = '0';
			} else {
				$decimal_place	 = 2;
				$zero			 = '0.00';
			}

			//format currency amount according to preference
			if ( $amount ) {

				if ( $this->get_setting( 'shop_currency_position' ) == 1 || !$this->get_setting( 'shop_currency_position' ) )
					return $symbol . number_format_i18n( $amount, $decimal_place );
				else if ( $this->get_setting( 'shop_currency_position' ) == 2 )
					return $symbol . ' ' . number_format_i18n( $amount, $decimal_place );
				else if ( $this->get_setting( 'shop_currency_position' ) == 3 )
					return number_format_i18n( $amount, $decimal_place ) . $symbol;
				else if ( $this->get_setting( 'shop_currency_position' ) == 4 )
					return number_format_i18n( $amount, $decimal_place ) . ' ' . $symbol;
			} else if ( $amount === false ) {
				return $symbol;
			} else {
				if ( $this->get_setting( 'shop_currency_position' ) == 1 || !$this->get_setting( 'shop_currency_position' ) )
					return $symbol . $zero;
				else if ( $this->get_setting( 'shop_currency_position' ) == 2 )
					return $symbol . ' ' . $zero;
				else if ( $this->get_setting( 'shop_currency_position' ) == 3 )
					return $zero . $symbol;
				else if ( $this->get_setting( 'shop_currency_position' ) == 4 )
					return $zero . ' ' . $symbol;
			}
		}
		
		
		/**
		 * Register the custom post types that we will be using
		 * @since 2.6
		 */
		function register_post_types(){
			global $wp_version;
			
			// Register product categories
			register_taxonomy( 'duka_category', 'duka', apply_filters( 'dukapress_register_product_category', array(
				'hierarchical'	 => true,
				'label'			 => __( 'Product Categories', 'dp-lang' ),
				'singular_label' => __( 'Product Category', 'dp-lang' ),
				'rewrite'		 => array(
					'with_front' => false,
					'slug'		 => $this->get_setting( 'slugs->products' ) . '/' . $this->get_setting( 'slugs->category' )
				),
			) ) );
	
			// Register product tags
			register_taxonomy( 'duka_tag', 'duka', apply_filters( 'dukapress_register_product_tag', array(
				'hierarchical'	 => false,
				'label'			 => __( 'Product Tags', 'dp-lang' ),
				'singular_label' => __( 'Product Tag', 'dp-lang' ),
				'rewrite'		 => array(
					'with_front' => false,
					'slug'		 => $this->get_setting( 'slugs->products' ) . '/' . $this->get_setting( 'slugs->tag' )
				),
			) ) );
			
			$icon = version_compare( $wp_version, '3.8', '>=' ) ? DPSC_DUKAPRESS_RESOURCEURL.'/img/dp_icon_white.png' : DPSC_DUKAPRESS_RESOURCEURL.'/img/dp_icon.png';
			
			// Register custom duka post type
			register_post_type( 'duka', apply_filters( 'dukapress_register_post_type', array(
				'labels'			 => array(
					'name'				 => __( 'Products', 'dp-lang' ),
					'singular_name'		 => __( 'Product', 'dp-lang' ),
					'menu_name'			 => __( 'Products', 'dp-lang' ),
					'all_items'			 => __( 'Products', 'dp-lang' ),
					'add_new'			 => __( 'Create New', 'dp-lang' ),
					'add_new_item'		 => __( 'Create New Product', 'dp-lang' ),
					'edit_item'			 => __( 'Edit Product', 'dp-lang' ),
					'edit'				 => __( 'Edit', 'dp-lang' ),
					'new_item'			 => __( 'New Product', 'dp-lang' ),
					'view_item'			 => __( 'View Product', 'dp-lang' ),
					'search_items'		 => __( 'Search Products', 'dp-lang' ),
					'not_found'			 => __( 'No Products Found', 'dp-lang' ),
					'not_found_in_trash' => __( 'No Products found in Trash', 'dp-lang' ),
					'view'				 => __( 'View Product', 'dp-lang' )
				),
				'description'		 => __( 'Products for your e-commerce store.', 'dp-lang' ),
				'public'			 => true,
				'show_ui'			 => true,
				'publicly_queryable' => true,
				'capability_type'	 => 'page',
				'hierarchical'		 => false,
				'menu_icon'			 => $icon,
				'rewrite'			 => array(
					'slug'		 =>  $this->get_setting( 'slugs->products' ),
					'with_front' => false
				),
				'query_var'			 => true,
				'supports'			 => array(
					'title',
					'editor',
					'author',
					'excerpt',
					'revisions',
					'thumbnail',
				),
				'taxonomies'		 => array(
					'duka_category',
					'duka_tag',
				),
			) ) );
			
			//Post status
			register_post_status( 'out_of_stock', array(
				'label'			 => __( 'Out of Stock', 'dp-lang' ),
				'label_count'	 => _n_noop( __( 'Out of Stock <span class="count">(%s)</span>', 'dp-lang' ), __( 'Out of Stock <span class="count">(%s)</span>', 'dp-lang' ) ),
				'post_type'		 => 'duka',
				'public'		 => false
			) );
			
			//register the orders post type
			register_post_type( 'duka_order', apply_filters( 'dukapress_register_post_type_duka_order', array(
				'labels'			 => array( 'name'			 => __( 'Orders', 'dp-lang' ),
					'singular_name'	 => __( 'Order', 'dp-lang' ),
					'edit'			 => __( 'Edit', 'dp-lang' ),
					'view_item'		 => __( 'View Order', 'dp-lang' ),
					'search_items'	 => __( 'Search Orders', 'dp-lang' ),
					'not_found'		 => __( 'No Orders Found', 'dp-lang' )
				),
				'description'		 => __( 'Orders from your e-commerce store.', 'dp-lang' ),
				'public'			 => false,
				'show_ui'			 => false,
				'capability_type'	 => 'page',
				'hierarchical'		 => false,
				'rewrite'			 => false,
				'query_var'			 => false,
				'supports'			 => array(),
			) ) );
	
			//register custom post statuses for our orders
			register_post_status( 'order_received', array(
				'label'			 => __( 'Received', 'dp-lang' ),
				'label_count'	 => array( __( 'Received <span class="count">(%s)</span>', 'dp-lang' ), __( 'Received <span class="count">(%s)</span>', 'dp-lang' ) ),
				'post_type'		 => 'duka_order',
				'public'		 => false
			) );
			register_post_status( 'order_paid', array(
				'label'			 => __( 'Paid', 'dp-lang' ),
				'label_count'	 => array( __( 'Paid <span class="count">(%s)</span>', 'dp-lang' ), __( 'Paid <span class="count">(%s)</span>', 'dp-lang' ) ),
				'post_type'		 => 'duka_order',
				'public'		 => false
			) );
			register_post_status( 'order_shipped', array(
				'label'			 => __( 'Shipped', 'dp-lang' ),
				'label_count'	 => array( __( 'Shipped <span class="count">(%s)</span>', 'dp-lang' ), __( 'Shipped <span class="count">(%s)</span>', 'dp-lang' ) ),
				'post_type'		 => 'duka_order',
				'public'		 => false
			) );
			register_post_status( 'order_closed', array(
				'label'			 => __( 'Closed', 'dp-lang' ),
				'label_count'	 => array( __( 'Closed <span class="count">(%s)</span>', 'dp-lang' ), __( 'Closed <span class="count">(%s)</span>', 'dp-lang' ) ),
				'post_type'		 => 'duka_order',
				'public'		 => false
			) );
			register_post_status( 'trash', array(
				'label'						 => _x( 'Trash', 'post' ),
				'label_count'				 => _n_noop( 'Trash <span class="count">(%s)</span>', 'Trash <span class="count">(%s)</span>' ),
				'show_in_admin_status_list'	 => true,
				'post_type'					 => 'duka_order',
				'public'					 => false
			) );
		}
		
		/** 
		 * Handle the Edit Screens
		 */
		function handle_edit_screen_filter($request){
			if ( is_admin() ) {
				global $current_screen;
	
				if ( $current_screen->id == 'edit-duka' ) {
					//Switches the product_category ids to slugs as you can't query custom taxonomys with ids
					if ( !empty( $request[ 'duka_category' ] ) ) {
						$cat = get_term_by( 'id', $request[ 'duka_category' ], 'duka_category' );
						$request[ 'duka_category' ] = $cat->slug;
					}
				} else if ( $current_screen->id == 'duka_page_dukapress-orders' && !isset( $_GET[ 'post_status' ] ) ) {
					//set the post status when on "All" to everything but closed
					$request[ 'post_status' ] = 'order_received,order_paid,order_shipped';
				}
			}
	
			return $request;
		}
		
		/**  
		 * Hanlde the Post updated messages
		 *
		 */
		function post_updated_messages($messages){
			global $post, $post_ID;

			$post_type = get_post_type( $post_ID );
	
			if ( $post_type != 'duka_order' && $post_type != 'duka' ) {
				return $messages;
			}
			$obj  = get_post_type_object( $post_type );
			$singular = $obj->labels->singular_name;
			
			$messages[ $post_type ] = array(
				0	 => '', // Unused. Messages start at index 1.
				1	 => sprintf( __( $singular . ' updated. <a href="%s">View ' . strtolower( $singular ) . '</a>' ), esc_url( get_permalink( $post_ID ) ) ),
				2	 => __( 'Custom field updated.' ),
				3	 => __( 'Custom field deleted.' ),
				4	 => __( $singular . ' updated.' ),
				5	 => isset( $_GET[ 'revision' ] ) ? sprintf( __( $singular . ' restored to revision from %s' ), wp_post_revision_title( (int) $_GET[ 'revision' ], false ) ) : false,
				6	 => sprintf( __( $singular . ' published. <a href="%s">View ' . strtolower( $singular ) . '</a>' ), esc_url( get_permalink( $post_ID ) ) ),
				7	 => __( 'Page saved.' ),
				8	 => sprintf( __( $singular . ' submitted. <a target="_blank" href="%s">Preview ' . strtolower( $singular ) . '</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
				9	 => sprintf( __( $singular . ' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . strtolower( $singular ) . '</a>' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
				10	 => sprintf( __( $singular . ' draft updated. <a target="_blank" href="%s">Preview ' . strtolower( $singular ) . '</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			);
	
			return $messages;
		}
		
		/** 
		 * Product View custom Columns
		 *
		 */
		function edit_duka_columns($old_columns){
			global $post_status;
			$columns[ 'cb' ]			 = '<input type="checkbox" />';
			$columns[ 'thumbnail' ]	 = __( 'Thumbnail', 'dp-lang' );
			$columns[ 'title' ]		 = __( 'Product Name', 'dp-lang' );
			$columns[ 'sku' ]			 = __( 'SKU', 'dp-lang' );
			$columns[ 'pricing' ]		 = __( 'Price', 'dp-lang' );
			if ( !$this->get_setting( 'disable_cart' ) ) {
				$columns[ 'stock' ]	 = __( 'Stock', 'dp-lang' );
				$columns[ 'sales' ]	 = __( 'Sales', 'dp-lang' );
			}
			$columns[ 'product_categories' ]	 = __( 'Product Categories', 'dp-lang' );
			$columns[ 'product_tags' ]		 = __( 'Product Tags', 'dp-lang' );
			return $columns;
		}
		
		/** 
		 * Loads Data into the custom columns
		 *
		 *
		 */
		function edit_duka_custom_columns($column, $id){
			global $post;
	
			$meta = get_post_custom();
			//unserialize
			foreach ( $meta as $key => $val ) {
				$meta[ $key ]	 = maybe_unserialize( $val[ 0 ] );
				if ( !is_array( $meta[ $key ] ) && $key != "duka_is_sale" && $key != "duka_track_inventory" && $key != "duka_product_link" )
					$meta[ $key ]	 = array( $meta[ $key ] );
			}
			
			switch ( $column ) {
				case "thumbnail":
					echo '<a href="' . get_edit_post_link() . '" title="' . __( 'Edit &raquo;' ) . '">';
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( array( 70, 70 ), array( 'title' => '' ) );
					} else {
						echo '<img width="70" height="70" src="' . DPSC_DUKAPRESS_RESOURCEURL . '/img/default_product_small.jpg">';
					}
					echo '</a>';
					break;
	
				case "sku":
					if ( isset( $meta[ "duka_sku" ] ) && is_array( $meta[ "duka_sku" ] ) ) {
						foreach ( (array) $meta[ "duka_sku" ] as $value ) {
							echo esc_attr( $value ) . '<br />';
						}
					} else {
						_e( 'N/A', 'dp-lang' );
					}
					break;
	
				case "pricing":
					if ( isset( $meta[ "price" ] ) && is_array( $meta[ "price" ] ) ) {
						foreach ( $meta[ "price" ] as $key => $value ) {
							if ( isset( $meta[ "new_price" ] ) && $meta[ "new_price" ] ) {
								echo '<del>' . $this->format_currency( '', $value ) . '</del> ';
								echo $this->format_currency( '', $meta[ "new_price" ][ $key ] ) . '<br />';
							} else {
								echo $this->format_currency( '', $value ) . '<br />';
							}
						}
					} else {
						echo $this->format_currency( '', 0 );
					}
					break;
	
				case "sales":
					echo number_format_i18n( isset( $meta[ "duka_sales_count" ][ 0 ] ) ? $meta[ "duka_sales_count" ][ 0 ] : 0 );
					break;
	
				case "stock":
					if ( isset( $meta[ "currently_in_stock" ] ) && $meta[ "currently_in_stock" ] ) {
						foreach ( (array) $meta[ "currently_in_stock" ] as $value ) {
							$inventory	 = ($value) ? $value : 0;
							if ( $inventory == 0 )
								$class		 = 'duka-inv-out';
							else if ( $inventory <= $this->get_setting( 'inventory_threshhold' ) )
								$class		 = 'duka-inv-warn';
							else
								$class		 = 'duka-inv-full';
	
							echo '<span class="' . $class . '">' . number_format_i18n( $inventory ) . '</span><br />';
						}
					} else {
						_e( 'N/A', 'dp-lang' );
					}
					break;
	
				case "product_categories":
					echo dukapress_category_list($id);
					break;
	
				case "product_tags":
					echo dukapress_tag_list($id);
					break;
			}
		}
		
		/** 
		 * Adds filter by Product Category
		 *
		 */
		function edit_duka_filter(){
			global $current_screen;
			if ( $current_screen->id == 'edit-duka' ) {
				$selected_category = !empty( $_GET[ 'duka_category' ] ) ? $_GET[ 'duka_category' ] : null;
				$dropdown_options = array( 'taxonomy'  => 'duka_category', 
											'show_option_all'=> __( 'View all categories' ), 
											'hide_empty' => 0, 
											'hierarchical' => 1,
											'show_count'  => 0, 
											'orderby'  => 'name', 
											'name' => 'duka_category',
											'selected'	=> $selected_category );
				wp_dropdown_categories( $dropdown_options );
			}
		}
		
		/** 
		 * Adds a custom row action for Copying/Cloning a Product
		 *
		 */
		function edit_duka_custom_row_actions($actions, $post){
			$action = 'copy-duka';

			if ( ($post->post_type == "duka") && (!isset( $actions[ $action ] )) ) {
	
				$post_type_object = get_post_type_object( $post->post_type );
				if ( $post_type_object ) {
					if ( current_user_can( 'edit_pages' ) ) {
						$copy_link			 = add_query_arg( 'action', $action );
						$copy_link			 = add_query_arg( 'post', $post->ID, $copy_link );
						$copy_link			 = wp_nonce_url( $copy_link, "{$action}-{$post->post_type}_{$post->ID}" );
						$actions[ $action ]	 = '<a href="' . $copy_link . '">' . __( 'Copy', 'dp-lang' ) . '</a>';
					}
				}
			}
			return $actions;
		}
		
		/** 
		 * Action for Copying/Cloning a Product
		 *
		 */
		function edit_products_copy_action(){
			$action = 'copy-duka';
			if ( (isset( $_GET[ 'action' ] )) && ($_GET[ 'action' ] == $action) ) {
				$sendback_href = remove_query_arg( array( '_wpnonce', 'duka-action', 'post', 'trashed', 'untrashed', 'deleted', 'ids' ), wp_get_referer() );
				if ( isset( $_GET[ 'post' ] ) )
					$product_id = intval( $_GET[ 'post' ] );
				else
					wp_redirect( $sendback_href );
	
				if ( isset( $_GET[ 'post_type' ] ) )
					$post_type = esc_attr( $_GET[ 'post_type' ] );
				else
					wp_redirect( $sendback_href );
	
				if ( (!isset( $_GET[ '_wpnonce' ] )) || !wp_verify_nonce( $_GET[ '_wpnonce' ], "{$action}-{$post_type}_{$product_id}" ) )
					wp_redirect( $sendback_href );
					
				$product  = (array) get_post_to_edit( $product_id );
				$product[ 'ID' ] = 0; // Zero out the Product ID to force insert of new item
				$product[ 'post_status' ]  = 'draft';
				$product[ 'post_author' ] = get_current_user_id();
				$new_product_id = wp_insert_post( $product );
				if ( ($new_product_id) && (!is_wp_error( $new_product_id )) ) {
					
					//Copy the meta
					$product_meta_keys = get_post_custom_keys( $product_id );
					if ( !empty( $product_meta_keys ) ) {
						foreach ( $product_meta_keys as $meta_key ) {
							$meta_values = get_post_custom_values( $meta_key, $product_id );
	
							foreach ( $meta_values as $meta_value ) {
								$meta_value = maybe_unserialize( $meta_value );
								add_post_meta( $new_product_id, $meta_key, $meta_value );
							}
						}
					}
					
					//Copy the taxonomy terms
					$product_taxonomies = get_object_taxonomies( $post_type );
					if ( !empty( $product_taxonomies ) ) {
						foreach ( $product_taxonomies as $product_taxonomy ) {
							$product_terms = wp_get_object_terms( $product_id, $product_taxonomy, array( 'orderby' => 'term_order' ) );
							if ( ($product_terms) && (count( $product_terms )) ) {
								$terms	 = array();
								foreach ( $product_terms as $product_term )
									$terms[] = $product_term->slug;
							}
							wp_set_object_terms( $new_product_id, $terms, $product_taxonomy );
						}
					}
				}
			}
			wp_redirect( $sendback_href );
			die();
		}
		
		/** 
		 * Columns for Orders
		 *
		 */
		function manage_orders_columns($old_columns){
			global $post_status;

			$columns[ 'cb' ] = '<input type="checkbox" />';
	
			$columns[ 'duka_orders_status' ]	 = __( 'Status', 'dp-lang' );
			$columns[ 'duka_orders_id' ]		 = __( 'Order ID', 'dp-lang' );
			$columns[ 'duka_orders_date' ]		 = __( 'Order Date', 'dp-lang' );
			$columns[ 'duka_orders_name' ]		 = __( 'From', 'dp-lang' );
			$columns[ 'duka_orders_items' ]		 = __( 'Items', 'dp-lang' );
			$columns[ 'duka_orders_shipping' ]	 = __( 'Shipping', 'dp-lang' );
			$columns[ 'duka_orders_tax' ]		 = __( 'Tax', 'dp-lang' );
			$columns[ 'duka_orders_discount' ]	 = __( 'Discount', 'dp-lang' );
			$columns[ 'duka_orders_total' ]		 = __( 'Total', 'dp-lang' );
	
			return $columns;
		}
		
		/** 
		 * Custom Column Content for Orders
		 *
		 */
		function manage_orders_custom_columns($column){
			global $post;
			$meta = get_post_custom();
			
			//unserialize
			foreach ( $meta as $key => $val )
				$meta[ $key ]  = array_map( 'maybe_unserialize', $val );
	
			switch ( $column ) {
	
				case "duka_orders_status":
					if ( $post->post_status == 'order_received' )
						$text	 = __( 'Received', 'dp-lang' );
					else if ( $post->post_status == 'order_paid' )
						$text	 = __( 'Paid', 'dp-lang' );
					else if ( $post->post_status == 'order_shipped' )
						$text	 = __( 'Shipped', 'dp-lang' );
					else if ( $post->post_status == 'order_closed' )
						$text	 = __( 'Closed', 'dp-lang' );
					else if ( $post->post_status == 'trash' )
						$text	 = __( 'Trashed', 'dp-lang' );
					?><a class="duka_orders_status" href="edit.php?post_type=duka&page=dukapress-orders&order_id=<?php echo $post->ID; ?>" title="<?php echo __( 'View Order Details', 'dp-lang' ); ?>"><?php echo $text ?></a><?php
					break;
	
				case "duka_orders_date":
					$t_time	 = get_the_time( __( 'Y/m/d g:i:s A' ) );
					$m_time	 = $post->post_date;
					$time	 = get_post_time( 'G', true, $post );
	
					$time_diff = time() - $time;
	
					if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 )
						$h_time	 = sprintf( __( '%s ago' ), human_time_diff( $time ) );
					else
						$h_time	 = mysql2date( __( 'Y/m/d' ), $m_time );
					echo '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
					break;
	
				case "duka_orders_id":
					$title	 = _draft_or_post_title();
					?>
					<strong><a class="row-title" href="edit.php?post_type=duka&page=dukapress-orders&order_id=<?php echo $post->ID; ?>" title="<?php echo esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'mp' ), $title ) ); ?>"><?php echo $title ?></a></strong>
					<?php
					$actions = array();
					if ( $post->post_status == 'order_received' ) {
						$actions[ 'paid' ]	 = "<a title='" . esc_attr( __( 'Mark as Paid', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=paid&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Paid', 'dp-lang' ) . "</a>";
						$actions[ 'shipped' ]	 = "<a title='" . esc_attr( __( 'Mark as Shipped', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=shipped&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Shipped', 'dp-lang' ) . "</a>";
						$actions[ 'closed' ]	 = "<a title='" . esc_attr( __( 'Mark as Closed', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=closed&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Closed', 'dp-lang' ) . "</a>";
					} else if ( $post->post_status == 'order_paid' ) {
						$actions[ 'shipped' ]	 = "<a title='" . esc_attr( __( 'Mark as Shipped', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=shipped&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Shipped', 'dp-lang' ) . "</a>";
						$actions[ 'closed' ]	 = "<a title='" . esc_attr( __( 'Mark as Closed', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=closed&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Closed', 'dp-lang' ) . "</a>";
					} else if ( $post->post_status == 'order_shipped' ) {
						$actions[ 'closed' ] = "<a title='" . esc_attr( __( 'Mark as Closed', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=closed&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Closed', 'dp-lang' ) . "</a>";
					} else if ( $post->post_status == 'order_closed' ) {
						$actions[ 'received' ] = "<a title='" . esc_attr( __( 'Mark as Received', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=received&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Received', 'dp-lang' ) . "</a>";
						$actions[ 'paid' ]	 = "<a title='" . esc_attr( __( 'Mark as Paid', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=paid&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Paid', 'dp-lang' ) . "</a>";
						$actions[ 'shipped' ]	 = "<a title='" . esc_attr( __( 'Mark as Shipped', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=shipped&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Shipped', 'dp-lang' ) . "</a>";
					}
	
					if ( (isset( $_GET[ 'post_status' ] )) && ($_GET[ 'post_status' ] == "trash") ) {
						$actions[ 'delete' ] = "<a title='" . esc_attr( __( 'Delete', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=delete&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Delete Permanently', 'dp-lang' ) . "</a>";
					} else {
						$actions[ 'trash' ] = "<a title='" . esc_attr( __( 'Trash', 'dp-lang' ) ) . "' href='" . wp_nonce_url( admin_url( 'edit.php?post_type=duka&amp;page=dukapress-orders&amp;action=trash&amp;post=' . $post->ID ), 'update-order-status' ) . "'>" . __( 'Trash', 'dp-lang' ) . "</a>";
					}
	
					$action_count	 = count( $actions );
					$i				 = 0;
					echo '<div class="row-actions">';
					foreach ( $actions as $action => $link ) {
						++$i;
						( $i == $action_count ) ? $sep = '' : $sep = ' | ';
						echo "<span class='$action'>$link$sep</span>";
					}
					echo '</div>';
					break;
	
				case "duka_orders_name":
					echo esc_attr( $meta[ "duka_shipping_info" ][ 0 ][ 'name' ] ) . ' (<a href="mailto:' . urlencode( $meta[ "duka_shipping_info" ][ 0 ][ 'name' ] ) . ' &lt;' . esc_attr( $meta[ "duka_shipping_info" ][ 0 ][ 'email' ] ) . '&gt;?subject=' . urlencode( sprintf( __( 'Regarding Your Order (%s)', 'dp-lang' ), $post->post_title ) ) . '">' . esc_attr( $meta[ "duka_shipping_info" ][ 0 ][ 'email' ] ) . '</a>)';
					break;
	
				case "duka_orders_items":
					echo number_format_i18n( $meta[ "duka_order_items" ][ 0 ] );
					break;
	
				case "duka_orders_shipping":
					echo $this->format_currency( '', $meta[ "duka_shipping_total" ][ 0 ] );
					break;
	
				case "duka_orders_tax":
					echo $this->format_currency( '', $meta[ "duka_tax_total" ][ 0 ] );
					break;
	
				case "duka_orders_discount":
					if ( isset( $meta[ "duka_discount_info" ][ 0 ] ) && $meta[ "duka_discount_info" ][ 0 ] ) {
						echo $meta[ "duka_discount_info" ][ 0 ][ 'discount' ] . ' (' . strtoupper( $meta[ "duka_discount_info" ][ 0 ][ 'code' ] ) . ')';
					} else {
						_e( 'N/A', 'dp-lang' );
					}
					break;
	
				case "duka_orders_total":
					echo $this->format_currency( '', $meta[ "duka_order_total" ][ 0 ] );
					break;
			}
		}
		
		/** 
		 * Create the Admin Menu
		 *
		 *
		 */
		function admin_menu(){
			$order_cap = apply_filters( 'duka_orders_cap', 'edit_others_posts' );
			if ( current_user_can( $order_cap ) && !$this->get_setting( 'disable_cart' ) ) {
				$num_posts= wp_count_posts( 'duka_order' ); //get pending order count
				$count = $num_posts->order_received + $num_posts->order_paid;
				if ( $count > 0 )
					$count_output = '&nbsp;<span class="update-plugins"><span class="updates-count count-' . $count . '">' . $count . '</span></span>';
				else
					$count_output = '';
				$orders_page	 = add_submenu_page( 'edit.php?post_type=duka', __( 'Manage Orders', 'dp-lang' ), __( 'Manage Orders', 'dp-lang' ) . $count_output, $order_cap, 'dukapress-orders', array( &$this, 'orders_page' ) );
			}
			$page = add_submenu_page( 'edit.php?post_type=duka', __( 'Store Settings', 'dp-lang' ), __( 'Store Settings', 'dp-lang' ), 'manage_options', 'dukapress', array( &$this, 'admin_page' ) );
			add_action( 'admin_print_scripts-' . $page, array( &$this, 'admin_script_settings' ) );
			add_action( 'admin_print_styles-' . $page, array( &$this, 'admin_css_settings' ) );
		}
		
		/** 
		 * Load Admin CSS
		 */
		function admin_css(){
			wp_register_style('dp_jquery_ui', DPSC_DUKAPRESS_RESOURCEURL . '/css/jquery-ui-1.8.5.custom.css');
			wp_enqueue_style('dp_admin_css', DPSC_DUKAPRESS_RESOURCEURL.'/css/dp-admin.css');
		}
		
		/**
		 * Javascript for the Edit product screen
		 */
		function admin_script_post(){
			global $current_screen;
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'dukapress-admin', DPSC_DUKAPRESS_RESOURCEURL . '/js/dukapress-admin.js', array( 'jquery' ), $this->version );
			if ( $current_screen->id == 'duka' )
				wp_enqueue_script( 'duka-post', DPSC_DUKAPRESS_RESOURCEURL . '/js/post-screen.js', array( 'jquery' ), $this->version );
		}
		
		/** 
		 * Admin Page
		 */
		function admin_page(){
			global $wpdb;
			if ( !current_user_can( 'manage_options' ) ) {
				echo "<p>" . __( 'Invalid Access', 'dp-lang' ) . "</p>";
				return;
			}
			?>
			<div class="wrap">
				<h2><?php _e("DukaPress Settings","dp-lang");?></h2>
				<h3 class="nav-tab-wrapper">
					<?php 
						
						$tab = (!empty( $_GET[ 'tab' ] ) ) ? $_GET[ 'tab' ] : 'main';
						if ( !$this->get_setting( 'disable_cart' ) ) {
							$tabs = array(
								'coupons'		 => __( 'Coupons', 'dp-lang' ),
								'email'		 => __( 'Email', 'dp-lang' ),
								'shipping'		 => __( 'Shipping', 'dp-lang' ),
								'gateways'		 => __( 'Payments', 'dp-lang' ),
								'checkout'		 => __( 'Checkout Setting', 'dp-lang' ),
								'shortcodes'	 => __( 'Shortcodes', 'dp-lang' ),
								'tools'		 => __( 'Tools', 'dp-lang' )
							);
						} else {
							$tabs = array(
								'shortcodes'	 => __( 'Shortcodes', 'dp-lang' ),
								'tools'		 => __( 'Tools', 'dp-lang' )
							);
						}
						$tabhtml = array();
						$class  = ( 'main' == $tab ) ? ' nav-tab-active' : '';
						$tabhtml[] = '<a href="' . admin_url( 'edit.php?post_type=duka&amp;page=dukapress' ) . '" class="nav-tab' . $class . '">' . __( 'General', 'dp-lang' ) . '</a>';
						
						foreach ( $tabs as $stub => $title ) {
							$class  = ( $stub == $tab ) ? ' nav-tab-active' : '';
							$tabhtml[] = ' <a href="' . admin_url( 'edit.php?post_type=duka&amp;page=dukapress&amp;tab=' . $stub ) . '" class="nav-tab' . $class . '">' . $title . '</a>';
						}
						echo implode( "\n", $tabhtml );
					?>
				</h3>
				<div class="clear"></div>
				<?php
					$settings = get_option( 'dukapress_settings' );
					//Save settings. General save for all settings
					if ( isset( $_POST[ 'dukapress_settings' ] ) ) {
						$settings = array_merge( $settings, apply_filters( 'dukapress_main_settings_filter', $_POST[ 'dpsc' ] ) );
						update_option( 'dukapress_settings', $settings );
					}
					switch ( $tab ) {
						case "main":
							?>
							<div id="dpsc_main">
								
							</div>
							<?php
						break;
						case "coupons":
						
						break;
						case "email":
						
						break;
						case "shipping":
						
						break;
						case "gateways":
						
						break;
						case "checkout":
							
							if ( isset( $_POST[ 'dpsc_checkout_settings' ] ) ) {
								update_option( 'dukapress_checkout_settings', $_POST[ 'dpsc' ]);
								echo '<div class="updated fade"><p>' . __( 'Checkout settings saved.', 'dp-lang' ) . '</p></div>';
							}
							$checkout_settings = get_option( 'dukapress_checkout_settings' );
							$count = 0;
							?>
							<div id="dpsc_checkout">
								<div class="dpsc_checkout_fields">
									<form method="POST" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
										<?php wp_nonce_field('dukapress_checkout_settings','dukapress_checkout_noncename'); ?>
										<input type="hidden" name="sort_order" id="sort_order" value=""/>
										<p class="submit">
											<a class="button button-secondary" onclick="add_checkout_element()"><?php _e('Add Checkout Field','dp-lang'); ?></a>&nbsp;&nbsp;&nbsp;<input class='button button-primary' type='submit' name='dpsc_checkout_settings' value='<?php _e('Save Options','dp-lang'); ?>'/><br/>
										</p>
										<table width="100%" border="0" class="widefat">
											<thead>
												<tr>
													<th width="1%" align="left" scope="col"></th>
													<th width="20%" align="left" scope="col"><?php _e('Name','dp-lang'); ?></th>
													<th width="10%" align="left" scope="col"><?php _e('Type','dp-lang'); ?></th>
													<th width="10%" align="left" scope="col"><?php _e('Unique Name','dp-lang'); ?></th>
													<th width="39%" align="left" scope="col"><?php _e('Initial Value','dp-lang'); ?></th>
													<th width="10%" align="left" scope="col"><?php _e('Mandatory','dp-lang'); ?></th>
													<th width="10%" align="left" scope="col"><?php _e('Visible','dp-lang'); ?></th>
													<th width="1%" align="left" scope="col"></th>
												</tr>
											</thead>
											
											<tfoot>
												<tr>
													<th align="left" scope="col"></th>
													<th align="left" scope="col"><?php _e('Name','dp-lang'); ?></th>
													<th align="left" scope="col"><?php _e('Type','dp-lang'); ?></th>
													<th align="left" scope="col"><?php _e('Unique Name','dp-lang'); ?></th>
													<th align="left" scope="col"><?php _e('Initial Value','dp-lang'); ?></th>
													<th align="left" scope="col"><?php _e('Mandatory','dp-lang'); ?></th>
													<th align="left" scope="col"><?php _e('Visible','dp-lang'); ?></th>
													<th align="left" scope="col"></th>
												</tr>
											</tfoot>
											<tbody class='sort-checkout ui-sortable'>
												<?php
												if (is_array($checkout_settings) && count($checkout_settings) > 0) {
													
													foreach ($checkout_settings as $checkout_row) {
														?>
														<tr id="<?php echo $checkout_row['name']; ?>">
															<td><span style="cursor:move" class="dashicons dashicons-sort"></span></td>
															<?php if(!isset($checkout_row['delete'])) { ?>
															<td><input type="text" name="dpsc[<?php echo $count; ?>][name]" value="<?php echo $checkout_row['name']; ?>"/></td>
															<td>
																<select name="dpsc[<?php echo $count; ?>][type]">
																	<?php
																		foreach ($this->form_elements as $forms => $form) {
																			$cont_selected = '';
																			if ($checkout_row['type'] === $form) {
																				$cont_selected = 'selected="selected"';
																			}
																			?>
																			<option value="<?php echo $form; ?>" <?php echo $cont_selected; ?> ><?php _e($forms); ?></option>
																			<?php
																		}
																	?>
																</select>
															</td>
															<td><input type="text" name="dpsc[<?php echo $count; ?>][uname]" value="<?php echo $checkout_row['uname']; ?>" /></td>
															<td><input type="text" name="dpsc[<?php echo $count; ?>][initial]" value="<?php echo @$checkout_row['initial']; ?>" style="width:100%"/></td>
															<?php } else { ?>
															<td><input type="hidden" name="dpsc[<?php echo $count; ?>][name]" value="<?php echo $checkout_row['name']; ?>" /><?php _e($checkout_row['name'],'dp-lang'); ?></td>
															<td><input type="hidden" name="dpsc[<?php echo $count; ?>][type]" value="<?php echo $checkout_row['type']; ?>" /><?php _e($checkout_row['type'],'dp-lang'); ?></td>
															<td><input type="hidden" name="dpsc[<?php echo $count; ?>][uname]" value="<?php echo $checkout_row['uname']; ?>" /><?php _e($checkout_row['uname']); ?></td>
															<td><input type="hidden" name="dpsc[<?php echo $count; ?>][initial]" value="<?php echo $checkout_row['initial']; ?>" /><?php _e($checkout_row['initial'],'dp-lang'); ?></td>
															<?php } ?>
															<td><input type="checkbox" value="checked" name="dpsc[<?php echo $count; ?>][mandatory]" <?php echo ($checkout_row['mandatory'] == 'checked') ? "checked='checked'": ""; ?> /></td>
															<td><input type="checkbox" value="checked" name="dpsc[<?php echo $count; ?>][visible]" <?php echo ($checkout_row['visible'] == 'checked') ? "checked='checked'": ""; ?> /></td>
															<?php if(!isset($checkout_row['delete'])) {?>
																<td><span style="cursor:pointer" class="dashicons dashicons-no-alt" onclick="delete_checkout_element(this)"></span></td>
															<?php } else { ?>
																<td><input type="hidden" name="dpsc[<?php echo $count; ?>][delete]" value="false" /></td>
															<?php } ?>
														</tr>
														<?php
														$count++;
													}	
												}
												?>
												
											</tbody>
										</table>
										<p class="submit">
											<a class="button button-secondary" onclick="add_checkout_element()"><?php _e('Add Checkout Field','dp-lang'); ?></a>&nbsp;&nbsp;&nbsp;<input class='button button-primary' type='submit' name='dpsc_checkout_settings' value='<?php _e('Save Options','dp-lang'); ?>'/><br/>
										</p>
									</form>
									<input type="hidden" id="dpsc_item_count" value="<?php echo $count; ?>" />
									<div style="display:none" class="dpsc_append_row">
										<script type="dpsc_checkout_row">
											<tr class="ui-sortable-handle">
												<td><span style="cursor:move" class="dashicons dashicons-sort"></span></td>
												<td><input type="text" name="dpsc[CURRENTCOUNT][name]" value=""/></td>
												<td>
													<select name="dpsc[CURRENTCOUNT][type]">
														<?php
															foreach ($this->form_elements as $forms => $form) {
																?>
																<option value="<?php echo $form; ?>"><?php _e($forms); ?></option>
																<?php
															}
														?>
													</select>
												</td>
												<td><input type="text" name="dpsc[CURRENTCOUNT][uname]" value="" /></td>
												<td><input type="text" name="dpsc[CURRENTCOUNT][initial]" value="" style="width:100%"/></td>
												<td><input type="checkbox" value="checked" name="dpsc[CURRENTCOUNT][manadatory]" /></td>
												<td><input type="checkbox" value="checked" name="dpsc[CURRENTCOUNT][visible]" /></td>
												<td><span style="cursor:pointer" class="dashicons dashicons-no-alt" onclick="delete_checkout_element(this)"></span></td>
											</tr>
										</script>
									</div>
									<script type="text/javascript">
										jQuery(document).ready(function() {
											var idsInOrder = [];
											jQuery("tbody.sort-checkout").sortable({
												update: function( event, ui ) {
													idsInOrder = [];
													jQuery('tbody.sort-checkout tr').each(function() {
														idsInOrder.push(jQuery(this).attr('id'));
													});
													jQuery('#sort_order').val(idsInOrder);
												}
											});
										});
									</script>
								</div>
							</div>
							<?php
						break;
						case "shortcodes":
						
						break;
						case "tools":
						
						break;
					}
				?>	
			</div>
			<?php
		}
		
		/** 
		 * Order Page
		 * Custom Page to show transactions
		 *
		 */
		function orders_page(){
			//load single order view if id is set
			if ( isset( $_GET[ 'order_id' ] ) ) {
				$this->single_order_page();
				return;
			}
			
			global $wpdb, $post_type, $wp_query, $wp_locale, $current_screen;
			$post_type = 'duka_order';
			$_GET[ 'post_type' ] = $post_type;
			$post_type_object = get_post_type_object( $post_type );
			
			if ( !current_user_can( $post_type_object->cap->edit_posts ) )
				wp_die( __( 'Invalid Access' ) );
				
			$pagenum = isset( $_GET[ 'paged' ] ) ? absint( $_GET[ 'paged' ] ) : 0;
			if ( empty( $pagenum ) )
				$pagenum = 1;
			$per_page = 'edit_' . $post_type . '_per_page';
			$per_page = (int) get_user_option( $per_page );
			if ( empty( $per_page ) || $per_page < 1 )
				$per_page	 = 20;
				
			$per_page = apply_filters( 'edit_' . $post_type . '_per_page', $per_page );
			
			// Handle bulk actions
			if ( isset( $_GET[ 'doaction' ] ) || isset( $_GET[ 'doaction2' ] ) || isset( $_GET[ 'bulk_edit' ] ) || isset( $_GET[ 'action' ] ) || (isset( $_GET[ 'delete_all' ] )) || (isset( $_GET[ 'delete_all2' ] )) ) {
				check_admin_referer( 'update-order-status' );
				$sendback = remove_query_arg( array( 'received', 'paid', 'shipped', 'closed', 'trash', 'delete', 'ids', 'delete_all', 'delete_all2' ), wp_get_referer() );

				if ( ( $_GET[ 'action' ] != -1 || $_GET[ 'action2' ] != -1 ) && ( isset( $_GET[ 'post' ] ) || isset( $_GET[ 'ids' ] ) ) ) {
					$post_ids	 = isset( $_GET[ 'post' ] ) ? array_map( 'intval', (array) $_GET[ 'post' ] ) : explode( ',', $_GET[ 'ids' ] );
					$doaction	 = ($_GET[ 'action' ] != -1) ? $_GET[ 'action' ] : $_GET[ 'action2' ];
				} else if ( isset( $_GET[ 'delete_all' ] ) || isset( $_GET[ 'delete_all2' ] ) )
					$doaction = 'delete_all';

				switch ( $doaction ) {
					case 'received':
						$received = 0;
						foreach ( (array) $post_ids as $post_id ) {
							$this->update_order_status( $post_id, 'received' );
							$received++;
						}
						$msg	 = sprintf( _n( '%s order marked as Received.', '%s orders marked as Received.', $received, 'dp-lang' ), number_format_i18n( $received ) );
						break;
					case 'paid':
						$paid	 = 0;
						foreach ( (array) $post_ids as $post_id ) {
							$this->update_order_status( $post_id, 'paid' );
							$paid++;
						}
						$msg	 = sprintf( _n( '%s order marked as Paid.', '%s orders marked as Paid.', $paid, 'dp-lang' ), number_format_i18n( $paid ) );
						break;
					case 'shipped':
						$shipped = 0;
						foreach ( (array) $post_ids as $post_id ) {
							$this->update_order_status( $post_id, 'shipped' );
							$shipped++;
						}
						$msg	 = sprintf( _n( '%s order marked as Shipped.', '%s orders marked as Shipped.', $shipped, 'dp-lang' ), number_format_i18n( $shipped ) );
						break;
					case 'closed':
						$closed	 = 0;
						foreach ( (array) $post_ids as $post_id ) {
							$this->update_order_status( $post_id, 'closed' );
							$closed++;
						}
						$msg = sprintf( _n( '%s order Closed.', '%s orders Closed.', $closed, 'dp-lang' ), number_format_i18n( $closed ) );
						break;

					case 'trash':
						$trashed = 0;
						foreach ( (array) $post_ids as $post_id ) {
							$this->update_order_status( $post_id, 'trash' );
							$trashed++;
						}
						$msg = sprintf( _n( '%s order moved to Trash.', '%s orders moved to Trash.', $trashed, 'dp-lang' ), number_format_i18n( $trashed ) );
						break;

					case 'delete':
						$deleted = 0;
						foreach ( (array) $post_ids as $post_id ) {
							$this->update_order_status( $post_id, 'delete' );
							$deleted++;
						}
						$msg = sprintf( _n( '%s order Deleted.', '%s orders Deleted.', $deleted, 'dp-lang' ), number_format_i18n( $deleted ) );
						break;

					case 'delete_all':
						$mp_orders = get_posts( 'post_type=duka_order&post_status=trash&numberposts=-1' );
						if ( $mp_orders ) {
							$deleted = 0;
							foreach ( $mp_orders as $mp_order ) {
								$this->update_order_status( $mp_order->ID, 'delete' );
								$deleted++;
							}
							$msg = sprintf( _n( '%s order Deleted.', '%s orders Deleted.', $deleted, 'mp' ), number_format_i18n( $deleted ) );
						}
						break;
				}
			}
			$avail_post_stati = wp_edit_posts_query();

			$num_pages = $wp_query->max_num_pages;

			$mode = 'list';
			?>
			<div class="wrap">
				<h2><?php _e( 'Manage Orders', 'dp-lang' ); if ( isset( $_GET[ 's' ] ) && $_GET[ 's' ] )  printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', get_search_query() ); ?></h2>
				<?php if ( isset( $msg ) ) { ?>
					<div class="updated fade">
						<p><?php echo $msg; ?></p>
					</div>
				<?php } ?>
				<form id="posts-filter" action="<?php echo admin_url( 'edit.php' ); ?>" method="get">
					<ul class="subsubsub">
						<?php 
							if ( empty( $locked_post_status ) ){
								$status_links = array();
								$num_posts = wp_count_posts( $post_type, 'readable' );
								$class = '';
								$allposts = '';
								
								$total_posts = array_sum( (array) $num_posts );

								// Subtract post types that are not included in the admin all list.
								foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state )
									$total_posts -= $num_posts->$state;
			
								$class  = empty( $class ) && empty( $_GET[ 'post_status' ] ) ? ' class="current"' : '';
								$status_links[]	 = "<li><a href='edit.php?post_type=duka{$allposts}&page=dukapress-orders'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) . '</a>';
								
								foreach ( get_post_stati( array(), 'objects' ) as $status_key => $status ) {
									$class = '';
			
									$status_name = $status->name;
			
									if ( !in_array( $status_name, $avail_post_stati ) )
										continue;
			
									if ( empty( $num_posts->$status_name ) )
										continue;
			
									if ( isset( $_GET[ 'post_status' ] ) && $status_name == $_GET[ 'post_status' ] )
										$class = ' class="current"';
			
									$status_links[ $status_key ] = "<li><a href='edit.php?post_type=duka&amp;page=dukapress-orders&amp;post_status=$status_name'$class>" . sprintf( _n( $status->label_count[ 0 ], $status->label_count[ 1 ], $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
								}
								
								if ( isset( $status_links[ 'trash' ] ) ) {
									$trash_item				 = $status_links[ 'trash' ];
									unset( $status_links[ 'trash' ] );
									$status_links[ 'trash' ]	 = $trash_item;
								}
								echo implode( " |</li>\n", $status_links ) . '</li>';
								unset( $status_links );
							}
						?>
					</ul>
					<p class="search-box">
						<label class="screen-reader-text" for="post-search-input"><?php _e( 'Search Orders', 'dp-lang' ); ?>:</label>
						<input type="text" id="post-search-input" name="s" value="<?php the_search_query(); ?>" />
						<input type="submit" value="<?php _e( 'Search Orders', 'dp-lang' ); ?>" class="button" />
					</p>
					<input type="hidden" name="post_type" class="post_status_page" value="duka" />
					<input type="hidden" name="page" class="post_status_page" value="dukapress-orders" />
					<?php if ( !empty( $_GET[ 'post_status' ] ) ) { ?>
					<input type="hidden" name="post_status" class="post_status_page" value="<?php echo esc_attr( $_GET[ 'post_status' ] ); ?>" />
					<?php } ?>
					<?php if ( have_posts() ) { ?>
						<div class="tablenav">
							<?php
							$page_links = paginate_links( array(
								'base'		 => add_query_arg( 'paged', '%#%' ),
								'format'	 => '',
								'prev_text'	 => __( '&laquo;' ),
								'next_text'	 => __( '&raquo;' ),
								'total'		 => $num_pages,
								'current'	 => $pagenum
							) );
							?>
							<div class="alignleft actions">
								<select name="action">
									<option value="-1" selected="selected"><?php _e( 'Change Status', 'dp-lang' ); ?></option>
									<option value="received"><?php _e( 'Received', 'dp-lang' ); ?></option>
									<option value="paid"><?php _e( 'Paid', 'dp-lang' ); ?></option>
									<option value="shipped"><?php _e( 'Shipped', 'dp-lang' ); ?></option>
									<option value="closed"><?php _e( 'Closed', 'dp-lang' ); ?></option>
									<?php if ( (isset( $_GET[ 'post_status' ] )) && ($_GET[ 'post_status' ] == 'trash') ) { ?>
									<option value="delete"><?php _e( 'Delete', 'dp-lang' ); ?></option>
									<?php } else { ?>
									<option value="trash"><?php _e( 'Trash', 'dp-lang' ); ?></option>
									<?php } ?>
								</select>
								<input type="submit" value="<?php esc_attr_e( 'Apply' ); ?>" name="doaction" id="doaction" class="button-secondary action" />
								<?php wp_nonce_field( 'update-order-status' ); ?>
								<?php 
									if ( !is_singular() ) {
										$year_query = $wpdb->prepare( "SELECT DISTINCT YEAR(post_date) AS yyear, MONTH(post_date) AS mmonth FROM $wpdb->posts WHERE post_type = %s ORDER BY post_date DESC", $post_type );
										$year_result = $wpdb->get_results( $year_query );

										$month_count = count( $year_result );
										if ( $month_count && !( 1 == $month_count && 0 == $year_result[ 0 ]->mmonth ) ) {
											$m = isset( $_GET[ 'm' ] ) ? (int) $_GET[ 'm' ] : 0;
											?>
											<select name='m'>
												<option <?php selected( $m, 0 ); ?> value='0'><?php _e( 'Show all dates' ); ?></option>
												<?php 
													foreach ( $year_result as $arc_row ) {
														if ( $arc_row->yyear == 0 )
															continue;
														$arc_row->mmonth = zeroise( $arc_row->mmonth, 2 );
				
														if ( $arc_row->yyear . $arc_row->mmonth == $m )
															$default = ' selected="selected"';
														else
															$default = '';
				
														echo "<option $default value='" . esc_attr( "$arc_row->yyear$arc_row->mmonth" ) . "'>";
														echo $wp_locale->get_month( $arc_row->mmonth ) . " $arc_row->yyear";
														echo "</option>\n";
													}
												?>
											</select>
											<?php
										}
										?><input type="submit" id="post-query-submit" value="<?php esc_attr_e( 'Filter' ); ?>" class="button-secondary" /><?php
									}
									if ( (isset( $_GET[ 'post_status' ] )) && ($_GET[ 'post_status' ] == 'trash') ) {
										submit_button( __( 'Empty Trash' ), 'button-secondary apply', 'delete_all', false );
									}
								?>
							</div>
							<?php if ( $page_links ) { ?>
								<div class="tablenav-pages">
									<?php
									$count_posts = $post_type_object->hierarchical ? $wp_query->post_count : $wp_query->found_posts;
									$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s', number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ), number_format_i18n( min( $pagenum * $per_page, $count_posts ) ), number_format_i18n( $count_posts ), $page_links);
									echo $page_links_text;
									?>
								</div>
							<?php } ?>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
						<table class="widefat <?php echo $post_type_object->hierarchical ? 'page' : 'post'; ?> fixed" cellspacing="0">
							<thead>
								<tr>
								<?php print_column_headers( $current_screen ); ?>
								</tr>
							</thead>
							<tfoot>
								<tr>
								<?php print_column_headers( $current_screen, false ); ?>
								</tr>
							</tfoot>
							<tbody>
								<?php
								if ( function_exists( 'post_rows' ) ) {
									post_rows();
								} else {
									$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
									$wp_list_table->display_rows();
								}
								?>
							</tbody>
						</table>
						<div class="tablenav">
							<?php
							if ( $page_links )
								echo "<div class='tablenav-pages'>$page_links_text</div>";
							?>
							<div class="alignleft actions">
								<select name="action2">
									<option value="-1" selected="selected"><?php _e( 'Change Status', 'dp-lang' ); ?></option>
									<option value="received"><?php _e( 'Received', 'dp-lang' ); ?></option>
									<option value="paid"><?php _e( 'Paid', 'dp-lang' ); ?></option>
									<option value="shipped"><?php _e( 'Shipped', 'dp-lang' ); ?></option>
									<option value="closed"><?php _e( 'Closed', 'dp-lang' ); ?></option>
									<?php if ( (isset( $_GET[ 'post_status' ] )) && ($_GET[ 'post_status' ] == 'trash') ) { ?>
									<option value="delete"><?php _e( 'Delete', 'dp-lang' ); ?></option>
									<?php } else { ?>
									<option value="trash"><?php _e( 'Trash', 'dp-lang' ); ?></option>
									<?php } ?>
								</select>
								<input type="submit" value="<?php esc_attr_e( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
								<?php
								if ( (isset( $_GET[ 'post_status' ] )) && ($_GET[ 'post_status' ] == 'trash') ) {
									submit_button( __( 'Empty Trash' ), 'button-secondary apply', 'delete_all2', false );
								}
								?>
								<div class="clear"></div>
							</div>
							<div class="clear"></div>
						</div>
					<?php } else{
						?>
							<div class="clear"></div>
							<p><?php _e( 'No Orders Found', 'dp-lang' ); ?></p>
						<?php
					} ?>
				</form>
			</div>
			<?php
		}
		
		/** 
		 * Single Order Page
		 *
		 */
		function single_order_page(){
			$order = $this->get_order( (int) $_GET[ 'order_id' ] );
			if ( !$order )
				wp_die( __( 'Requested order was not found', 'dp-lang' ) );
			$max_downloads = $this->get_setting( 'max_downloads', 5 );
			?>
			<div class="wrap">
				<h2><?php echo sprintf( __( 'Order Details (%s)', 'dp-lang' ), esc_attr( $order->post_title ) ); ?></h2>
			</div>
			<?php
		}
		
		/** 
		 * CSS used on theme
		 *
		 */
		function set_up_styles(){
	        wp_register_style('dpsc_basic_css', DPSC_DUKAPRESS_RESOURCEURL.'/css/dpsc-basic.css');
			wp_register_style('jquery.fancybox', DPSC_DUKAPRESS_RESOURCEURL .'/js/jquery.fancybox/jquery.fancybox.css', false, $this->version, 'screen');
			wp_register_style('dpsc_jqzoom', DPSC_DUKAPRESS_RESOURCEURL .'/css/jqzoom.css', false, $this->version, 'screen');
			wp_enqueue_style('dp_acc_style');
	        wp_enqueue_style('dpsc_basic_css');
		}
		
		/** 
		 * Javascript used on Theme
		 *
		 */
		function set_up_js(){
			add_theme_support('html5');
	        wp_register_script('dpsc_magiczoom', DPSC_DUKAPRESS_RESOURCEURL . '/js/magiczoom.js', array('jquery'), $this->version );
	        wp_register_script('dpsc_magiczoomplus', DPSC_DUKAPRESS_RESOURCEURL . '/js/magiczoomplus.js', array('jquery'), $this->version );
	        wp_register_script('dpsc_lightbox', DPSC_DUKAPRESS_RESOURCEURL . '/js/jquery.fancybox/jquery.fancybox-1.2.1.pack.js', array('jquery'), $this->version );
	        wp_register_script('dpsc_lightbox_call', DPSC_DUKAPRESS_RESOURCEURL . '/js/lightbox.js', array('jquery', 'dpsc_lightbox'), $this->version );
	        wp_register_script('dpsc_jqzoom', DPSC_DUKAPRESS_RESOURCEURL . '/js/jqzoom.pack.1.0.1.js', array('jquery'), $this->version );
	        wp_register_script('dpsc_js_file', DPSC_DUKAPRESS_RESOURCEURL . '/js/dukapress.js', array('jquery'),$this->version );

	
			
			$image_effect = $this->get_setting('image_effect');
	        switch ($image_effect) {
	            case 'mz_effect':
	                wp_enqueue_script('dpsc_magiczoom');
	                break;
	
	            case 'mzp_effect':
	                wp_enqueue_script('dpsc_magiczoomplus');
	                break;
	
	            case 'lightbox':
	                wp_enqueue_style('jquery.fancybox');
	                wp_enqueue_script('dpsc_lightbox');
	                wp_enqueue_script('dpsc_lightbox_call');
	                break;
	
	           case 'no_effect':
	                break;
	
	           case 'jqzoom_effect':
	                wp_enqueue_style('dpsc_jqzoom');
	                wp_enqueue_script('dpsc_jqzoom');
	                break;
	
	           default:
	                break;
	       }
		   $dpsc_site_url = get_bloginfo('url');
           wp_enqueue_script('dpsc_js_file');
		   wp_localize_script( 'dpsc_js_file', 'dpsc_js', array( 
				'dpsc_url' => $dpsc_site_url, 
				'ajaxurl' => admin_url('admin-ajax.php'),
				'text_error' => __("Please write only text here", "dp-lang"),
				'numbers_error' => __("Please write only numbers here", "dp-lang"),
				'email_error' => __("Please put the right email id", "dp-lang")
				) );
	        wp_enqueue_script('dpsc_livequery');
		}
		
		/**
		 * Enqeue js on product settings screen
		 */
		function admin_script_settings(){
			wp_enqueue_script( 'jquery-colorpicker', DPSC_DUKAPRESS_RESOURCEURL . '/colorpicker/js/colorpicker.js', array( 'jquery' ), $this->version );
			wp_enqueue_script( 'jquery-ui-datepicker' ); //use built in version
		}
		
		/**
		 * Enqeue css on product settings screen
		 */
		function admin_css_settings(){
			wp_enqueue_style( 'jquery-ui-css', DPSC_DUKAPRESS_RESOURCEURL . '/css/jquery-ui.1.11.4.css', false, $this->version );
			wp_enqueue_style( 'jquery-datepicker-css', DPSC_DUKAPRESS_RESOURCEURL . '/datepicker/css/smoothness/jquery-ui-1.10.3.custom.min.css', false, $this->version );
			wp_enqueue_style( 'jquery-colorpicker-css',DPSC_DUKAPRESS_RESOURCEURL . '/colorpicker/css/colorpicker.css', false, $this->version );
		}
		
		/** 
		 * Notify user to eneable proper permalinks
		 */
		function admin_nopermalink_warning(){
			if ( current_user_can( 'manage_options' ) && !get_option( 'permalink_structure' ) )
				echo '<div class="error"><p>' . __( 'You must enable Pretty Permalinks</a> to use DukaPress - <a href="options-permalink.php">Enable now &raquo;</a>', 'dp-lang' ) . '</p></div>';
		}
		
		/** 
		 * Add Settings option on the plugin list
		 */
		function plugin_action_link($links, $file){
			// the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page
			$settings_link = '<a href="' . admin_url( 'edit.php?post_type=duka&page=dukapress' ) . '">' . __( 'Settings', 'dp-lang' ) . '</a>';
			// add the link to the list
			array_unshift( $links, $settings_link );
			return $links;
		}
		
		//returns a new unique order id.
		function generate_order_id() {
			global $wpdb;
	
			$count = true;
			while ( $count ) { //make sure it's unique
				$order_id	 = substr( sha1( uniqid( '' ) ), rand( 1, 24 ), 12 );
				$count		 = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE post_title = %s AND post_type = 'duka_order'", $order_id ) );
			}
	
			$order_id = apply_filters( 'duka_order_id', $order_id ); //Very important to make sure order numbers are unique and not sequential if filtering
			//save it to session
			$_SESSION[ 'duka_order' ] = $order_id;
	
			return $order_id;
		}
	
		//called on checkout to create a new order
		function create_order( $order_id, $cart, $shipping_info, $payment_info, $paid, $user_id = false,
							$shipping_total = false, $tax_total = false, $coupon_code = false ) {
			
	
			return $order_id;
		}
		
		/** 
		 * Get Order by id
		 *
		 */
		function order_to_post_id($order_id){
			$order = get_page_by_title( $order_id, OBJECT, 'duka_order' );
			return $order->ID;
		}
		
		function update_order_status( $order_id, $new_status ) {
			
		}
	
		//returns the full order details as an object
		function get_order( $order_id ) {
			$id = (is_int( $order_id )) ? $order_id : $this->order_to_post_id( $order_id );
	
			if ( empty( $id ) )
				return false;
	
	
	
			$order = get_post( $id );
			if ( !$order )
				return false;
	
			$meta = get_post_custom( $id );
	
			//unserialize a and add to object
			foreach ( $meta as $key => $val )
				$order->$key = maybe_unserialize( $meta[ $key ][ 0 ] );
	
			return $order;
		}

		/** 
		 * Filter Title on Edit Product page
		 */
		function filter_title($post){
			global $post_type;

			if ( $post_type != 'duka' )
				return $post;
	
			return __( 'Enter Product title here', 'dp-lang' );
		}
		
		
		/** 
		 * Add Meta Box
		 *
		 */
		function meta_boxes(){
			global $wp_meta_boxes;
			add_meta_box( 'duka-meta-details', __( 'Product Details', 'dp-lang' ), array( &$this, 'meta_details' ), 'duka', 'normal', 'high' );
			if ( isset( $wp_meta_boxes[ 'duka' ][ 'side' ][ 'low' ][ 'postimagediv' ] ) ) {
				$imagediv  = $wp_meta_boxes[ 'duka' ][ 'side' ][ 'low' ][ 'postimagediv' ];
				unset( $wp_meta_boxes[ 'duka' ][ 'side' ][ 'low' ][ 'postimagediv' ] );
				$submitdiv = $wp_meta_boxes[ 'duka' ][ 'side' ][ 'core' ][ 'submitdiv' ];
				unset( $wp_meta_boxes[ 'duka' ][ 'side' ][ 'core' ][ 'submitdiv' ] );
				$new_core[ 'submitdiv' ] = $submitdiv;
				$new_core[ 'postimagediv' ] = $imagediv;
				$wp_meta_boxes[ 'duka' ][ 'side' ][ 'core' ] = array_merge( $new_core, $wp_meta_boxes[ 'duka' ][ 'side' ][ 'core' ] );
				//filter title
				$wp_meta_boxes[ 'duka' ][ 'side' ][ 'core' ][ 'postimagediv' ][ 'title' ]	 = __( 'Product Image', 'dp-lang' );
			}
		}
		
		//Save our post meta when a product is created or updated
		function save_product_meta( $post_id) {
			//skip quick edit
			if ( defined( 'DOING_AJAX' ) )
				return;
	
			if ( !wp_verify_nonce(@$_POST['dukapress_noncename'], plugin_basename( __FILE__ ) ) )
				return;
			
			// Check permissions
		    if ('duka' == $_POST['post_type']) {
		        if (!current_user_can('edit_page', $post_id))
		            return $post_id;
		    }else {
		        if (!current_user_can('edit_post', $post_id))
		            return $post_id;
		    }
		
		    // for price
		    if (NULL == $_POST['price']) {
		        //do nothing
		    } else {
		        $content_price = $_POST['price'];
		
		        update_post_meta($post_id, 'price', $content_price);
		    }
		
		    // for new price
		    if (NULL == $_POST['new_price']) {
		        //do nothing
		    } else {
		        $content_price = $_POST['new_price'];
		
		        update_post_meta($post_id, 'new_price', $content_price);
		    }
		
		    // for stocks
		    if (NULL == $_POST['currently_in_stock']) {
		        //do nothing
		    } else {
		        $content_stock = $_POST['currently_in_stock'];
		        update_post_meta($post_id, 'currently_in_stock', $content_stock);
		    }
		
		    // for weights
		    if (NULL == $_POST['item_weight']) {
		        //do nothing
		    } else {
		        $content_weight = $_POST['item_weight'];
		        update_post_meta($post_id, 'item_weight', $content_weight);
		    }
		
		    //for file
		    if (NULL == $_POST['digital_file']) {
		        //do nothing
		    } else {
		        $content_file = $_POST['digital_file'];
		        update_post_meta($post_id, 'digital_file', $content_file);
		    }
		
		    // SKU
		    if (NULL == $_POST['sku']) {
		        //do nothing
		    } else {
		        $sku = $_POST['sku'];
		        update_post_meta($post_id, 'sku', $sku);
		    }
			
			
			// for affiliate URL
		    if (NULL == $_POST['affiliate_url']) {
		        //do nothing
		    } else {
		        $affiliate_url = $_POST['affiliate_url'];
		        update_post_meta($post_id, 'affiliate_url', $affiliate_url);
		    }
		}
	

		function meta_details($post){
			wp_nonce_field( plugin_basename( __FILE__ ), 'dukapress_noncename' );
			$post_id = $post->ID;
			$content_price = get_post_meta($post_id, 'price', true);
		    $new_price = get_post_meta($post_id, 'new_price', true);
		    $content_stock = get_post_meta($post_id, 'currently_in_stock', true);
		    $content_weight = get_post_meta($post_id, 'item_weight', true);
		    $content_file = get_post_meta($post_id, 'digital_file', true);
			$affiliate_url = get_post_meta($post_id, 'affiliate_url', true);
			$sku = get_post_meta($post_id, 'sku', true);
			?>
			<table class="widefat">
				<tr>
					<td><?php _e('Price','dp-lang');?> :</td>
					<td><input type="text" value="<?php echo $content_price; ?>" name="price" id="price"></td>
				</tr>
				<tr>
					<td><?php _e('New Price','dp-lang');?> :</td>
					<td><input type="text" value="<?php echo $new_price; ?>" name="new_price" id="new_price"></td>
				</tr>
				<tr>
					<td><?php _e('Currently In Stock','dp-lang');?> :</td>
					<td><input type="text" value="<?php echo $content_stock; ?>" name="currently_in_stock" id="currently_in_stock"></td>
				</tr>
				<tr>
					<td><?php _e('Item Weight','dp-lang');?> :</td>
					<td><input type="text" value="<?php echo $content_weight; ?>" name="item_weight" id="item_weight"></td>
				</tr>
				<tr>
					<td><?php _e('SKU','dukagate');?> :</td>
					<td><input type="text" value="<?php echo $sku; ?>" name="sku" id="sku"></td>
				</tr>
				<tr>
					<td><?php _e('Affiliate URL','dp-lang');?> :</td>
					<td><input type="text" value="<?php echo $affiliate_url; ?>" name="affiliate_url" id="affiliate_url"></td>
				</tr>
				<tr>
					<td><?php _e('Digital File URL','dp-lang');?> :</td>
					<td>
						<input type="text" value="<?php echo $content_file; ?>" name="digital_file" id="digital_file"><br/>
						<input id="duka_upload_button" class="button-secondary" type="button" value="<?php _e( 'Upload File', 'dp-lang' ); ?>" />
					</td>
				</tr>
			</table>
			<div class="clear"></div>
			<h3><b><?php _e('Dropdown Options', "dp-lang"); ?></b></h3>
			<div class="variation_results">
			<?php
			echo $this->drop_down_meta($post_id);
			?>
			</div>
			<table class="widefat">
				<tr>
					<td><?php _e('Option Name', "dp-lang"); ?> :</td>
					<td>
						<input type="text" id="optionname" name="optionname" size="15" />
					</td>
				</tr>
			</table>
			<table class="widefat" id="variation_appends">
				<tr class="variation_name">
					<td><?php _e('Variation Name', "dp-lang"); ?> :</td>
					<td>
						<input type="text" id="vname1" name="vname1" size="15" />
					</td>
				</tr>
				<tr class="variation_price">
					<td><?php _e('Variation Price', "dp-lang"); ?> :</td>
					<td>
						<input type="text" id="vprice1" name="vprice1" size="15" />
					</td>
				</tr>
			</table>
			<table class="widefat">
				<tr>
					<td>
						<input type="button" id="dp_addVariation" value="+"/>
					</td>
					<td>
						<input type="hidden" name="varitaionnumber" id="varitaionnumber" value="1" />
						<input type="button" id="dp_save" value="<?php _e('Save', "dp-lang"); ?>"/>
					</td>
				</tr>
			</table>
			<?php
		}
		
		/** 
		 * Show Meta
		 */
		function drop_down_meta($post_id){
			$content_opname = get_post_meta($post_id, 'dropdown_option', true);
			$show_state_result = '';

		    if ($content_opname) {
		
		        $optionnames = explode("||", $content_opname);
		
		        foreach ($optionnames as $optionname) {
		            $j++;
		            $optionname1 = explode("|", $optionname);
		            $show_state_result.='<div class="variation_list">';
					$show_state_result.='
		                    <div id="dp_deletestring" class="variation_del">
								<a href="javascript:void()" id="' . $j . '">Delete</a>
		                        <input id="delete' . $j . '" name="delete' . $j . '" type="hidden" value="' . $optionname . '" />
		                     </div>
							 <p><b>' . ($optionname1[0]) . '</b></p>
							 <div style="clear:both"></div>
							 ';
					$show_state_result.='<table class="widefat">';
		            for ($i = 1; $optionname1[$i]; $i++) {
		                $show_state_result.='<tr>';
		                $optionname2 = explode(";", $optionname1[$i]);
		                foreach ($optionname2 as $value) {
		                    $show_state_result.= '<td>' . $value . '</td>';
		                }
		                $show_state_result.='</tr>';
		            }
					$show_state_result.='</table></div>';
		            
		        }
		    }
		    return $show_state_result;
		}
		
		/** 
		 * Save Variation Data
		 */
		function varition_save_data(){
			$counter = $_POST['counter'];
       	 	$postid = $_POST['postid'];
        	$prev_option = get_post_meta($postid, 'dropdown_option', true);
			
			// making || in each option name
			if ($prev_option) {
	            $prev_option_new = $prev_option;
	            $varition_type .= $prev_option_new . '||';
	        }
			
			// check for the validation that, option name should not be null
			if ($_POST['optionname']) {
	            $varition_type.=$_POST['optionname'] . '|';
	            for ($i = 1; $i <= $counter; $i++) {
	                if ($_POST['vname' . $i]) {
	                    if ($_POST['vprice' . $i] == null) {
	                        $varition_type.=$_POST['vname' . $i] . ';' . '0' . '|';
	                    } else {
	                        $varition_type.=$_POST['vname' . $i] . ';' . $_POST['vprice' . $i] . '|';
	                    }
	                }
	            }
	            $varition_type = substr($varition_type, 0, ($len - 1));
	            update_post_meta($postid, 'dropdown_option', $varition_type);
	        } else {
	            __('Enter the variation data', "dp-lang");
	        }
			$prev_option = get_post_meta($postid, 'dropdown_option');
			?>
			<div style="clear:both;width:250px;word-wrap: break-word">
				<?php echo $this->drop_down_meta($postid); ?>
			</div>
			<?php
		}
		
		/** 
		 * Delete Variation Data
		 */
		function varition_delete_data(){
	        $postid = $_POST['postid'];
	        $substr = $_POST['name'];
	        // echo $substr;
	
	        $delete_prev_option = get_post_meta($postid, 'dropdown_option', true);
	        $result_string = str_replace($substr, '', $delete_prev_option);
	        $result_string = str_replace("||||", "||", $result_string);
	        if ($result_string == "||") {
	            $result_string = '';
	        }
	        if ($result_string === '') {
	            delete_post_meta($postid, 'dropdown_option');
	        } else {
	            update_post_meta($postid, 'dropdown_option', $result_string);
	        }
	        echo $this->drop_down_meta($postid);
		    die();
		}

	}
	
	global $dukapress;
	$dukapress = new DukaPress();
}
?>