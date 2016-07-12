<?php
/*
Plugin Name: PrivMessages
Plugin URI: 
Description: Allow members to send and receive private messages (PM)
Version: 
Author: 
Author URI:
License: 
*/

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

define( 'CL_PMW_DIR', plugin_dir_path( __FILE__ ) );
define( 'CL_PMW_INC_DIR', trailingslashit( CL_PMW_DIR . 'inc' ) );

define( 'CL_PMW_URL', plugin_dir_url( __FILE__ ) );
define( 'CL_PMW_CSS_URL', trailingslashit( CL_PMW_URL . 'css' ) );
define( 'CL_PMW_JS_URL', trailingslashit( CL_PMW_URL . 'js' ) );

class CL_PMW
{
	
	function _construct()
	{
		register_activation_hook( __FILE__, array($this, 'cl_pmw_activate'));
		add_action( 'plugins_loaded', array($this,'cl_pmw_load_text_domain'));
		add_action( 'admin_notices', array($this, 'cl_pmw_notify'));
		add_action( 'admin_bar_menu', array($this, 'cl_pmw_adminbar', 300));
		add_action( 'wp_ajax_cl_pmw_get_users', array($this, 'cl_pmw_get_users'));
	}
	
	function cl_pmw_load_text_domain()
	{
		load_plugin_textdomain( 'cl_pmw', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
	
	public static function init(){
		include_once CL_PMW_INC_DIR . 'inbox-page.php';
		include_once CL_PMW_INC_DIR . 'send-page.php';
		include_once CL_PMW_INC_DIR . 'outbox-page.php';

		if ( is_admin() )
		{
			include_once CL_PMW_INC_DIR . 'options.php';
		}
	
		return new self;
	}
	
	//Create table and register an option when activate

	function cl_pmw_activate()
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

	// Show notification of new PM

	function cl_pmw_notify()
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

	function cl_pmw_adminbar()
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

	function cl_pmw_get_users()
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
}


CL_PMW::init();
//Load plugin text domain
 
