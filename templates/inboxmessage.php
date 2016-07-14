<div class="wrap">
		<h2><?php _e( 'Inbox \ View Message', 'pmw' ); ?></h2>

		<p><a href="?page=inbox"><?php _e( 'Back to inbox', 'pmw' ); ?></a></p>
		<table class="widefat fixed" cellspacing="0">
			<thead>
			<tr>
				<th class="manage-column" width="20%"><?php _e( 'Info', 'pmw' ); ?></th>
				<th class="manage-column"><?php _e( 'Message', 'pmw' ); ?></th>
				<th class="manage-column" width="15%"><?php _e( 'Action', 'pmw' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><?php printf( __( '<b>Sender</b>: %s<br /><b>Date</b>: %s', 'pmw' ), $msg->sender, $msg->date ); ?></td>
				<td><?php printf( __( '<p><b>Subject</b>: %s</p><p>%s</p>', 'pmw' ), stripcslashes( $msg->subject ) , nl2br( stripcslashes( $msg->content ) ) ); ?></td>
				<td>
						<span class="delete">
							<a class="delete"
								href="<?php echo wp_nonce_url( "?page=inbox&action=delete&id=$msg->id", 'pmw-delete_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Delete', 'pmw' ); ?></a>
						</span>
						<span class="reply">
							| <a class="reply"
							href="<?php echo wp_nonce_url( "?page=send&recipient=$msg->sender&id=$msg->id&subject=Re: " . stripcslashes( $msg->subject ), 'pmw-reply_inbox_msg_' . $msg->id ); ?>"><?php _e( 'Reply', 'pmw' ); ?></a>
						</span>
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<th class="manage-column" width="20%"><?php _e( 'Info', 'pmw' ); ?></th>
				<th class="manage-column"><?php _e( 'Message', 'pmw' ); ?></th>
				<th class="manage-column" width="15%"><?php _e( 'Action', 'pmw' ); ?></th>
			</tr>
			</tfoot>
		</table>
	</div>