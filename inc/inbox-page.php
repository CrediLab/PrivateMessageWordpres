<?php
//Inbox page

function cl_pmw_inbox()
{
	global $wpdb, $current_user;

// If view message
	if ( isset( $_GET['action'] ) && 'view' == $_GET['action'] && !empty( $_GET['id'] ) )
	{
		$id = $_GET['id'];

		check_admin_referer( "cl_pmw-view_inbox_msg_$id" );

		// Mark message as read
		$wpdb->update( $wpdb->prefix . 'pm', array( 'read' => 1 ), array( 'id' => $id ) );

		// Select message information
		$msg = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'pm WHERE `id` = "' . $id . '" LIMIT 1' );
		$msg->sender = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE user_login = '$msg->sender'" );
		?>
	<div class="wrap">
		<h2><?php _e( 'Inbox \ View Message', 'cl_pmw' ); ?></h2>

		<p><a href="?page=cl_pmw_inbox"><?php _e( 'Back to inbox', 'cl_pmw' ); ?></a></p>
		<table class="widefat fixed" cellspacing="0">
			<thead>
			<tr>
				<th class="manage-column" width="20%"><?php _e( 'Info', 'cl_pmw' ); ?></th>
				<th class="manage-column"><?php _e( 'Message', 'cl_pmw' ); ?></th>
				<th class="manage-column" width="15%"><?php _e( 'Action', 'cl_pmw' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><?php printf( __( '<b>Sender</b>: %s<br /><b>Date</b>: %s', 'cl_pmw' ), $msg->sender, $msg->date ); ?></td>
				<td><?php printf( __( '<p><b>Subject</b>: %s</p><p>%s</p>', 'cl_pmw' ), stripcslashes( $msg->subject ) , nl2br( stripcslashes( $msg->content ) ) ); ?></td>
				<td>
						<span class="delete">
							<a class="delete"
								href="<?php echo wp_nonce_url( "?page=cl_pmw_inbox&action=delete&id=$msg->id", 'cl_pmw-delete_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Delete', 'cl_pmw' ); ?></a>
						</span>
						<span class="reply">
							| <a class="reply"
							href="<?php echo wp_nonce_url( "?page=cl_pmw_send&recipient=$msg->sender&id=$msg->id&subject=Re: " . stripcslashes( $msg->subject ), 'cl_pmw-reply_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Reply', 'cl_pmw' ); ?></a>
						</span>
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<th class="manage-column" width="20%"><?php _e( 'Info', 'cl_pmw' ); ?></th>
				<th class="manage-column"><?php _e( 'Message', 'cl_pmw' ); ?></th>
				<th class="manage-column" width="15%"><?php _e( 'Action', 'cl_pmw' ); ?></th>
			</tr>
			</tfoot>
		</table>
	</div>
	<?php
// Doesn't need to do more!
		return;
	}

	// If mark messages as read
	if ( isset( $_GET['action'] ) && 'mar' == $_GET['action'] && !empty( $_GET['id'] ) )
	{
		$id = $_GET['id'];

		if ( !is_array( $id ) )
		{
			check_admin_referer( "cl_pmw-mar_inbox_msg_$id" );
			$id = array( $id );
		}
		else
		{
			check_admin_referer( "cl_pmw-bulk-action_inbox" );
		}
		$n = count( $id );
		$id = implode( ',', $id );
		if ( $wpdb->query( 'UPDATE ' . $wpdb->prefix . 'pm SET `read` = "1" WHERE `id` IN (' . $id . ')' ) )
		{
			$status = _n( 'Message marked as read.', 'Messages marked as read', $n, 'cl_pmw' );
		}
		else
		{
			$status = __( 'Error. Please try again.', 'cl_pmw' );
		}
	}

	// If delete message
	if ( isset( $_GET['action'] ) && 'delete' == $_GET['action'] && !empty( $_GET['id'] ) )
	{
		$id = $_GET['id'];

		if ( !is_array( $id ) )
		{
			check_admin_referer( "cl_pmw-delete_inbox_msg_$id" );
			$id = array( $id );
		}
		else
		{
			check_admin_referer( "cl_pmw-bulk-action_inbox" );
		}

		$error = false;
		foreach ( $id as $msg_id )
		{
			// Check if the sender has deleted this message
			$sender_deleted = $wpdb->get_var( 'SELECT `deleted` FROM ' . $wpdb->prefix . 'pm WHERE `id` = "' . $msg_id . '" LIMIT 1' );

			// Create corresponding query for deleting message
			if ( $sender_deleted == 1 )
			{
				$query = 'DELETE from ' . $wpdb->prefix . 'pm WHERE `id` = "' . $msg_id . '"';
			}
			else
			{
				$query = 'UPDATE ' . $wpdb->prefix . 'pm SET `deleted` = "2" WHERE `id` = "' . $msg_id . '"';
			}

			if ( !$wpdb->query( $query ) )
			{
				$error = true;
			}
		}
		if ( $error )
		{
			$status = __( 'Error. Please try again.', 'cl_pmw' );
		}
		else
		{
			$status = _n( 'Message deleted.', 'Messages deleted.', count( $id ), 'cl_pmw' );
		}
	}

	// Show all messages which have not been deleted by this user (deleted status != 2)
	$msgs = $wpdb->get_results( 'SELECT `id`, `sender`, `subject`, `read`, `date` FROM ' . $wpdb->prefix . 'pm WHERE `recipient` = "' . $current_user->user_login . '" AND `deleted` != "2" ORDER BY `date` DESC' );
	?>
<div class="wrap">
	<h2><?php _e( 'Inbox', 'cl_pmw' ); ?></h2>
	<?php
	if ( !empty( $status ) )
	{
		echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
	}
	if ( empty( $msgs ) )
	{
		echo '<p>', __( 'You have no items in inbox.', 'cl_pmw' ), '</p>';
	}
	else
	{
		$n = count( $msgs );
		$num_unread = 0;
		foreach ( $msgs as $msg )
		{
			if ( !( $msg->read ) )
			{
				$num_unread++;
			}
		}
		echo '<p>', sprintf( _n( 'You have %d private message (%d unread).', 'You have %d private messages (%d unread).', $n, 'cl_pmw' ), $n, $num_unread ), '</p>';
		?>
		<form action="" method="get">
			<?php wp_nonce_field( 'cl_pmw-bulk-action_inbox' ); ?>
			<input type="hidden" name="page" value="cl_pmw_inbox" />

			<div class="tablenav">
				<select name="action">
					<option value="-1" selected="selected"><?php _e( 'Bulk Action', 'cl_pmw' ); ?></option>
					<option value="delete"><?php _e( 'Delete', 'cl_pmw' ); ?></option>
					<option value="mar"><?php _e( 'Mark As Read', 'cl_pmw' ); ?></option>
				</select> <input type="submit" class="button-secondary" value="<?php _e( 'Apply', 'cl_pmw' ); ?>" />
			</div>

			<table class="widefat fixed" cellspacing="0">
				<thead>
				<tr>
					<th class="manage-column check-column"><input type="checkbox" /></th>
					<th class="manage-column" width="10%"><?php _e( 'Sender', 'cl_pmw' ); ?></th>
					<th class="manage-column"><?php _e( 'Subject', 'cl_pmw' ); ?></th>
					<th class="manage-column" width="20%"><?php _e( 'Date', 'cl_pmw' ); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $msgs as $msg )
					{
						$msg->sender = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE user_login = '$msg->sender'" );
						?>
					<tr>
						<th class="check-column"><input type="checkbox" name="id[]" value="<?php echo $msg->id; ?>" />
						</th>
						<td><?php echo $msg->sender; ?></td>
						<td>
							<?php
							if ( $msg->read )
							{
								echo '<a href="', wp_nonce_url( "?page=cl_pmw_inbox&action=view&id=$msg->id", 'cl_pmw-view_inbox_msg_' . $msg->id ), '">', stripcslashes( $msg->subject ), '</a>';
							}
							else
							{
								echo '<a href="', wp_nonce_url( "?page=cl_pmw_inbox&action=view&id=$msg->id", 'cl_pmw-view_inbox_msg_' . $msg->id ), '"><b>', stripcslashes( $msg->subject ), '</b></a>';
							}
							?>
							<div class="row-actions">
							<span>
								<a href="<?php echo wp_nonce_url( "?page=cl_pmw_inbox&action=view&id=$msg->id", 'cl_pmw-view_inbox_msg_' . $msg->id ); ?>"><?php _e( 'View', 'cl_pmw' ); ?></a>
							</span>
								<?php
								if ( !( $msg->read ) )
								{
									?>
									<span>
								| <a href="<?php echo wp_nonce_url( "?page=cl_pmw_inbox&action=mar&id=$msg->id", 'cl_pmw-mar_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Mark As Read', 'cl_pmw' ); ?></a>
							</span>
									<?php

								}
								?>
								<span class="delete">
								| <a class="delete"
									href="<?php echo wp_nonce_url( "?page=cl_pmw_inbox&action=delete&id=$msg->id", 'cl_pmw-delete_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Delete', 'cl_pmw' ); ?></a>
							</span>
							<span class="reply">
								| <a class="reply"
								href="<?php echo wp_nonce_url( "?page=cl_pmw_send&recipient=$msg->sender&id=$msg->id&subject=Re: " . stripcslashes( $msg->subject ), 'cl_pmw-reply_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Reply', 'cl_pmw' ); ?></a>
							</span>
							</div>
						</td>
						<td><?php echo $msg->date; ?></td>
					</tr>
						<?php

					}
					?>
				</tbody>
				<tfoot>
				<tr>
					<th class="manage-column check-column"><input type="checkbox" /></th>
					<th class="manage-column"><?php _e( 'Sender', 'cl_pmw' ); ?></th>
					<th class="manage-column"><?php _e( 'Subject', 'cl_pmw' ); ?></th>
					<th class="manage-column"><?php _e( 'Date', 'cl_pmw' ); ?></th>
				</tr>
				</tfoot>
			</table>
		</form>
		<?php

	}
	?>
</div>
<?php
}

?>