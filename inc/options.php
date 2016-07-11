<?php
add_action( 'admin_init', 'cl_pmw_init' );
add_action( 'admin_menu', 'cl_pmw_add_menu' );

// Register plugin option

function cl_pmw_init()
{
	register_setting( 'cl_pmw_option_group', 'cl_pmw_option' );
}

// Add Option page and PM Menu
 
function cl_pmw_add_menu()
{
	global $wpdb, $current_user;

	// Get number of unread messages
	$num_unread = $wpdb->get_var( 'SELECT COUNT(`id`) FROM ' . $wpdb->prefix . 'pm WHERE `recipient` = "' . $current_user->user_login . '" AND `read` = 0 AND `deleted` != "2"' );

	if ( empty( $num_unread ) )
		$num_unread = 0;

	// Option page
	add_options_page( __( 'Private Messages Options', 'cl_pmw' ), __( 'Private Messages', 'cl_pmw' ), 'manage_options', 'cl_pmw_option', 'cl_pmw_option_page' );

	// Add Private Messages Menu
	$icon_url = CL_PMW_URL . 'icon.png';
	add_menu_page( __( 'Private Messages', 'cl_pmw' ), __( 'Messages', 'cl_pmw' ) . "<span class='update-plugins count-$num_unread'><span class='plugin-count'>$num_unread</span></span>", 'read', 'cl_pmw_inbox', 'cl_pmw_inbox', $icon_url );

	// Inbox page
	$inbox_page = add_submenu_page( 'cl_pmw_inbox', __( 'Inbox', 'cl_pmw' ), __( 'Inbox', 'cl_pmw' ), 'read', 'cl_pmw_inbox', 'cl_pmw_inbox' );
	add_action( "admin_print_styles-{$inbox_page}", 'cl_pmw_admin_print_styles_inbox' );

	// Outbox page
	$outbox_page = add_submenu_page( 'cl_pmw_inbox', __( 'Outbox', 'cl_pmw' ), __( 'Outbox', 'cl_pmw' ), 'read', 'cl_pmw_outbox', 'cl_pmw_outbox' );
	add_action( "admin_print_styles-{$outbox_page}", 'cl_pmw_admin_print_styles_outbox' );

	// Send page
	$send_page = add_submenu_page( 'cl_pmw_inbox', __( 'Send Private Message', 'cl_pmw' ), __( 'Send', 'cl_pmw' ), 'read', 'cl_pmw_send', 'cl_pmw_send' );
	add_action( "admin_print_styles-{$send_page}", 'cl_pmw_admin_print_styles_send' );
}

// Enqueue scripts and styles for inbox page

function cl_pmw_admin_print_styles_inbox()
{
	do_action( 'cl_pmw_print_styles', 'inbox' );
}

// Enqueue scripts and styles for outbox page
 
function cl_pmw_admin_print_styles_outbox()
{
	do_action( 'cl_pmw_print_styles', 'outbox' );
}

// Enqueue scripts and styles for send page
 
function cl_pmw_admin_print_styles_send()
{
    wp_enqueue_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
	wp_enqueue_style( 'cl_pmw_css', CL_PMW_CSS_URL . 'style.css' );
	wp_enqueue_script( 'cl_pmw_js', CL_PMW_JS_URL . 'script.js', array( 'jquery-ui-autocomplete' ) );

	do_action( 'cl_pmw_print_styles', 'send' );
}

// Option page: Change number of PMs for each group

function cl_pmw_option_page() {
	?>
<div class="wrap">
	<h2><?php _e( 'Private Messages Options', 'cl_pmw' ); ?></h2>

	<div style="width:600px;float:left">
		<form method="post" action="options.php">

			<?php
			settings_fields( 'cl_pmw_option_group' );
			$option = get_option( 'cl_pmw_option' );

			echo '<h3>', __( 'Please set numbers of private messages for each user role:', 'cl_pmw' ), '</h3>';
			echo '<p>', __( '<b><i>0</i></b> means <b><i>unlimited</i></b>', 'cl_pmw' ), '</p>';
			echo '<p>', __( '<b><i>-1</i></b> means <b><i>not allowed</i></b> to send PM', 'cl_pmw' ), '</p>';


			?>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Administrator', 'cl_pmw' ); ?></th>
					<td>
						<input type="text" name="cl_pmw_option[administrator]" value="<?php echo $option['administrator']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Editor', 'cl_pmw' ); ?></th>
					<td><input type="text" name="cl_pmw_option[editor]" value="<?php echo $option['editor']; ?>"/></td>
				</tr>
				<tr>
					<th><?php _e( 'Author', 'cl_pmw' ); ?></th>
					<td><input type="text" name="cl_pmw_option[author]" value="<?php echo $option['author']; ?>"/></td>
				</tr>
				<tr>
					<th><?php _e( 'Contributor', 'cl_pmw' ); ?></th>
					<td>
						<input type="text" name="cl_pmw_option[contributor]" value="<?php echo $option['contributor']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Subscriber', 'cl_pmw' ); ?></th>
					<td><input type="text" name="cl_pmw_option[subscriber]" value="<?php echo $option['subscriber']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'How do you want to choose recipient?', 'cl_pmw' ); ?></th>
					<td>
						<input type="radio" name="cl_pmw_option[type]" value="dropdown" <?php if ( $option['type'] == 'dropdown' )
							echo 'checked="checked"'; ?> /><?php _e( 'Dropdown list', 'cl_pmw' ); ?>
						<input type="radio" name="cl_pmw_option[type]" value="autosuggest" <?php if ( $option['type'] == 'autosuggest' )
							echo 'checked="checked"'; ?> /><?php _e( 'Auto suggest from user input', 'cl_pmw' ); ?>
					</td>
				</tr>
			</table>

			
			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save Changes', 'cl_pmw' ) ?>"/>
			</p>

		</form>

	</div>
	
</div>
	<?php
}
