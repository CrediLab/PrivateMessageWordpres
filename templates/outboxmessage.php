<div class="wrap">
        <h2><?php _e('Outbox \ View Message', 'pmw'); ?></h2>

        <p><a href="?page=outbox"><?php _e('Back to outbox', 'pmw'); ?></a></p>
        <table class="widefat fixed" cellspacing="0">
            <thead>
            <tr>
                <th class="manage-column" width="20%"><?php _e('Info', 'pmw'); ?></th>
                <th class="manage-column"><?php _e('Message', 'pmw'); ?></th>
                <th class="manage-column" width="15%"><?php _e('Action', 'pmw'); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php printf(__('<b>Recipient</b>: %s<br /><b>Date</b>: %s', 'pmw'), $msg->recipient, $msg->date); ?></td>
                <td><?php printf(__('<p><b>Subject</b>: %s</p><p>%s</p>', 'pmw'), stripcslashes($msg->subject), nl2br(stripcslashes($msg->content))); ?></td>
                <td>
						<span class="delete">
							<a class="delete"
                               href="<?php echo wp_nonce_url("?page=outbox&action=delete&id=$msg->id", 'pmw-delete_outbox_msg_' . $msg->id); ?>"><?php _e('Delete', 'pmw'); ?></a>
						</span>
                </td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <th class="manage-column" width="20%"><?php _e('Info', 'pmw'); ?></th>
                <th class="manage-column"><?php _e('Message', 'pmw'); ?></th>
                <th class="manage-column" width="15%"><?php _e('Action', 'pmw'); ?></th>
            </tr>
            </tfoot>
        </table>
    </div>