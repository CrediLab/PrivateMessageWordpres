<?php
/*
Plugin Name: PrivateMessageWordpress
Plugin URI: https://github.com/CrediLab/PrivateMessageWordpress
Description: Allow members to send and receive private messages (PM)
Version: 
Author: CrediLab
Author URI: https://github.com/CrediLab
License: 
*/

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

define( 'CL_PMW_DIR', plugin_dir_path( __FILE__ ) );
define( 'CL_PMW_INC_DIR', trailingslashit( CL_PMW_DIR . 'inc' ) );

define( 'CL_PMW_URL', plugin_dir_url( __FILE__ ) );
define( 'CL_PMW_CSS_URL', trailingslashit( CL_PMW_URL . 'css' ) );
define( 'CL_PMW_JS_URL', trailingslashit( CL_PMW_URL . 'js' ) );

include_once CL_PMW_INC_DIR . 'inbox-page.php';
include_once CL_PMW_INC_DIR . 'send-page.php';
include_once CL_PMW_INC_DIR . 'outbox-page.php';

/*if ( is_admin() )
	{
		include_once CL_PMW_INC_DIR . 'options.php';
	}*/
	
class CL_PMW
{
	
	public function __construct()
	{
		register_activation_hook( __FILE__, array($this, 'cl_pmw_activate'));
		add_action( 'plugins_loaded', array($this,'cl_pmw_load_text_domain'));
		add_action( 'admin_notices', array($this, 'cl_pmw_notify'));
		add_action( 'admin_bar_menu', array($this, 'cl_pmw_adminbar'), 300);
		add_action( 'wp_ajax_cl_pmw_get_users', array($this, 'cl_pmw_get_users'));

		if(is_admin())
		{
			add_action( 'admin_init', array($this, 'cl_pmw_init') );
			add_action( 'admin_menu', array($this, 'cl_pmw_add_menu') );
		}
	}
	
	// Add Option page and PM Menu
 
	public function cl_pmw_add_menu()
	{
		global $wpdb, $current_user;

		// Get number of unread messages
		$num_unread = $wpdb->get_var( 'SELECT COUNT(`id`) FROM ' . $wpdb->prefix . 'pm WHERE `recipient` = "' . $current_user->user_login . '" AND `read` = 0 AND `deleted` != "2"' );

		if ( empty( $num_unread ) )
			$num_unread = 0;

		// Option page
		add_options_page( __( 'Private Messages Options', 'cl_pmw' ), __( 'Private Messages', 'cl_pmw' ), 'manage_options', 'cl_pmw_option', array($this,'cl_pmw_option_page') );

		// Add Private Messages Menu
		$icon_url = CL_PMW_URL . 'icon.png';
		add_menu_page( __( 'Private Messages', 'cl_pmw' ), __( 'Messages', 'cl_pmw' ) . "<span class='update-plugins count-$num_unread'><span class='plugin-count'>$num_unread</span></span>", 'read', 'cl_pmw_inbox', 'cl_pmw_inbox', $icon_url );

		// Inbox page
		$inbox_page = add_submenu_page( 'cl_pmw_inbox', __( 'Inbox', 'cl_pmw' ), __( 'Inbox', 'cl_pmw' ), 'read', 'cl_pmw_inbox', 'cl_pmw_inbox' );
		add_action( "admin_print_styles-{$inbox_page}", array($this,'cl_pmw_admin_print_styles_inbox') );

		// Outbox page
		$outbox_page = add_submenu_page( 'cl_pmw_inbox', __( 'Outbox', 'cl_pmw' ), __( 'Outbox', 'cl_pmw' ), 'read', 'cl_pmw_outbox', 'cl_pmw_outbox' );
		add_action( "admin_print_styles-{$outbox_page}", array($this,'cl_pmw_admin_print_styles_outbox') );

		// Send page
		$send_page = add_submenu_page( 'cl_pmw_inbox', __( 'Send Private Message', 'cl_pmw' ), __( 'Send', 'cl_pmw' ), 'read', 'cl_pmw_send', 'cl_pmw_send' );
		add_action( "admin_print_styles-{$send_page}", array($this,'cl_pmw_admin_print_styles_send') );
	}

	// Enqueue scripts and styles for send page
 
	public function cl_pmw_admin_print_styles_send()
	{
	    wp_enqueue_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
		wp_enqueue_style( 'cl_pmw_css', CL_PMW_CSS_URL . 'style.css' );
		wp_enqueue_script( 'cl_pmw_js', CL_PMW_JS_URL . 'script.js', array( 'jquery-ui-autocomplete' ) );

		do_action( 'cl_pmw_print_styles', 'send' );
	}

	// Enqueue scripts and styles for outbox page
 
	public function cl_pmw_admin_print_styles_outbox()
	{
		do_action( 'cl_pmw_print_styles', 'outbox' );
	}

	// Enqueue scripts and styles for inbox page

	public function cl_pmw_admin_print_styles_inbox()
	{
		do_action( 'cl_pmw_print_styles', 'inbox' );
	}


	public function cl_pmw_load_text_domain()
	{
		load_plugin_textdomain( 'cl_pmw', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
	
	public static function init(){
		return new self;
	}
	
	//Create table and register an option when activate

	public function cl_pmw_activate()
	{
		global $wpdb;

		// Create table
		$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'pm (
			`id` bigint(20) NOT NULL auto_increment,
			`subject` varchar(255) NOT NULL,
			`content` longtext NOT NULL,
			`sender` varchar(60) NOT NULL,
			`recipient` varchar(60) NOT NULL,
			`date` datetime NOT NULL,
			`read` tinyint(1) NOT NULL,
			`deleted` tinyint(1) NOT NULL,
			PRIMARY KEY (`id`)
		) COLLATE utf8_general_ci;';

		// Note: deleted = 1 if message is deleted by sender, = 2 if it is deleted by recipient

		$wpdb->query( $query );

		// Default numbers of PM for each group
		$default_option = array(
			'administrator' => 0,
			'editor'        => 50,
			'author'        => 20,
			'contributor'   => 10,
			'subscriber'    => 5,
			'type'          => 'dropdown', // How to choose recipient: dropdown list or autocomplete based on user input
			'email_enable'  => 1,
			'email_name'    => '%BLOG_NAME%',
			'email_address' => '%BLOG_ADDRESS%',
			'email_subject' => __( 'New PM at %BLOG_NAME%', 'cl_pmw' ),
			'email_body'    => __( "You have new private message from <b>%SENDER%</b> at <b>%BLOG_NAME%</b>.\n\n<a href=\"%INBOX_URL%\">Click here</a> to go to your inbox.\n\nThis email is sent automatically. Please don't reply.", 'cl_pmw' )
		);
		add_option( 'cl_pmw_option', $default_option, '', 'no' );
	}

	// Option page: Change number of PMs for each group

	function cl_pmw_option_page() {
		//TODO: include templates dir -> options.php
	}

	// Show notification of new PM

	public function cl_pmw_notify()
	{
		global $wpdb, $current_user;

		// Get number of unread messages
		$num_unread = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'pm WHERE `recipient` = "' . $current_user->user_login . '" AND `read` = 0 AND `deleted` != "2"' );

		if ( !$num_unread )
			return;

		printf(
			'<div id="message" class="error"><p><b>%s</b> <a href="%s">%s</a></p></div>',
			sprintf( _n( 'You have %d new message!', 'You have %d new messages!', $num_unread, 'cl_pmw' ), $num_unread ),
			admin_url( 'admin.php?page=cl_pmw_inbox' ),
			__( 'Click here to go to inbox', 'cl_pmw' )
		);
	}

	//Show number of unread messages in admin bar

	public function cl_pmw_adminbar()
	{
		global $wp_admin_bar;
		global $wpdb, $current_user;

		// Get number of unread messages
		$num_unread = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'pm WHERE `recipient` = "' . $current_user->user_login . '" AND `read` = 0 AND `deleted` != "2"' );

		if ( $num_unread && is_admin_bar_showing() )
		{
			$wp_admin_bar->add_menu( array(
				'id'    => 'cl_pmw',
				'title' => sprintf( _n( 'You have %d new message!', 'You have %d new messages!', $num_unread, 'cl_pmw' ), $num_unread ),
				'href'  => admin_url( 'admin.php?page=cl_pmw_inbox' ),
				'meta'  => array( 'class' => "cl_pmw_newmessages" ),
			) );
		}
	}

	// Ajax callback function to get list of users

	public function cl_pmw_get_users()
	{
		$keyword = trim( strip_tags( $_POST['term'] ) );
		$values = array();
		$args = array( 'search' => '*' . $keyword . '*',
					   'fields' => 'all_with_meta' );
		$results_search_users = get_users( $args );
		$results_search_users = apply_filters( 'cl_pmw_recipients', $results_search_users );
		if ( !empty( $results_search_users ) )
		{
			foreach ( $results_search_users as $result )
			{
				$values[] = $result->display_name;
			}
		}
		die( json_encode( $values ) );
	}
	
	// Register plugin option

	public function function cl_pmw_init()
	{
		register_setting( 'cl_pmw_option_group', 'cl_pmw_option' );
	}

}


CL_PMW::init();
//Load plugin text domain
 
