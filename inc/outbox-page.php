<?php
// Outbox page
 
function cl_pmw_outbox()
{
    global $wpdb, $current_user;

    // If view message
    if (isset($_GET['action']) && 'view' == $_GET['action'] && !empty($_GET['id'])) {
        $id = $_GET['id'];

        check_admin_referer("cl_pmw-view_outbox_msg_$id");

        // Select message information
        $msg = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'pm WHERE `id` = "' . $id . '" LIMIT 1');
        $msg->recipient = $wpdb->get_var("SELECT display_name FROM $wpdb->users WHERE user_login = '$msg->recipient'");
        ?>
    <div class="wrap">
        <h2><?php _e('Outbox \ View Message', 'cl_pmw'); ?></h2>

        <p><a href="?page=cl_pmw_outbox"><?php _e('Back to outbox', 'cl_pmw'); ?></a></p>
        <table class="widefat fixed" cellspacing="0">
            <thead>
            <tr>
                <th class="manage-column" width="20%"><?php _e('Info', 'cl_pmw'); ?></th>
                <th class="manage-column"><?php _e('Message', 'cl_pmw'); ?></th>
                <th class="manage-column" width="15%"><?php _e('Action', 'cl_pmw'); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php printf(__('<b>Recipient</b>: %s<br /><b>Date</b>: %s', 'cl_pmw'), $msg->recipient, $msg->date); ?></td>
                <td><?php printf(__('<p><b>Subject</b>: %s</p><p>%s</p>', 'cl_pmw'), stripcslashes($msg->subject), nl2br(stripcslashes($msg->content))); ?></td>
                <td>
						<span class="delete">
							<a class="delete"
                               href="<?php echo wp_nonce_url("?page=cl_pmw_outbox&action=delete&id=$msg->id", 'cl_pmw-delete_outbox_msg_' . $msg->id); ?>"><?php _e('Delete', 'cl_pmw'); ?></a>
						</span>
                </td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <th class="manage-column" width="20%"><?php _e('Info', 'cl_pmw'); ?></th>
                <th class="manage-column"><?php _e('Message', 'cl_pmw'); ?></th>
                <th class="manage-column" width="15%"><?php _e('Action', 'cl_pmw'); ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
    <?php
        // Doesn't need to do more!
        return;
    }

    // If delete message
    if (isset($_GET['action']) && 'delete' == $_GET['action'] && !empty($_GET['id'])) {
        $id = $_GET['id'];

        if (!is_array($id)) {
            check_admin_referer("cl_pmw-delete_outbox_msg_$id");
            $id = array($id);
        } else {
            check_admin_referer("cl_pmw-bulk-action_outbox");
        }
        $error = false;
        foreach ($id as $msg_id) {
            // Check if the recipient has deleted this message
            $recipient_deleted = $wpdb->get_var('SELECT `deleted` FROM ' . $wpdb->prefix . 'pm WHERE `id` = "' . $msg_id . '" LIMIT 1');
            // Create corresponding query for deleting message
            if ($recipient_deleted == 2) {
                $query = 'DELETE from ' . $wpdb->prefix . 'pm WHERE `id` = "' . $msg_id . '"';
            } else {
                $query = 'UPDATE ' . $wpdb->prefix . 'pm SET `deleted` = "1" WHERE `id` = "' . $msg_id . '"';
            }

            if (!$wpdb->query($query)) {
                $error = true;
            }
        }
        if ($error) {
            $status = __('Error. Please try again.', 'cl_pmw');
        } else {
            $status = _n('Message deleted.', 'Messages deleted.', count($id), 'cl_pmw');
        }
    }

    // Show all messages
    $msgs = $wpdb->get_results('SELECT `id`, `recipient`, `subject`, `date` FROM ' . $wpdb->prefix . 'pm WHERE `sender` = "' . $current_user->user_login . '" AND `deleted` != 1 ORDER BY `date` DESC');
    ?>
<div class="wrap">
    <h2><?php _e('Outbox', 'cl_pmw'); ?></h2>
    <?php
    if (!empty($status)) {
        echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
    }
    if (empty($msgs)) {
        echo '<p>', __('You have no items in outbox.', 'cl_pmw'), '</p>';
    } else {
        $n = count($msgs);
        echo '<p>', sprintf(_n('You wrote %d private message.', 'You wrote %d private messages.', $n, 'cl_pmw'), $n), '</p>';
        ?>
        <form action="" method="get">
            <?php wp_nonce_field('cl_pmw-bulk-action_outbox'); ?>
            <input type="hidden" name="action" value="delete"/> <input type="hidden" name="page" value="cl_pmw_outbox"/>

            <div class="tablenav">
                <input type="submit" class="button-secondary" value="<?php _e('Delete Selected', 'cl_pmw'); ?>"/>
            </div>

            <table class="widefat fixed" cellspacing="0">
                <thead>
                <tr>
                    <th class="manage-column check-column"><input type="checkbox"/></th>
                    <th class="manage-column" width="10%"><?php _e('Recipient', 'cl_pmw'); ?></th>
                    <th class="manage-column"><?php _e('Subject', 'cl_pmw'); ?></th>
                    <th class="manage-column" width="20%"><?php _e('Date', 'cl_pmw'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($msgs as $msg) {
                        $msg->recipient = $wpdb->get_var("SELECT display_name FROM $wpdb->users WHERE user_login = '$msg->recipient'");
                        ?>
                    <tr>
                        <th class="check-column"><input type="checkbox" name="id[]" value="<?php echo $msg->id; ?>"/>
                        </th>
                        <td><?php echo $msg->recipient; ?></td>
                        <td>
                            <?php
                            echo '<a href="', wp_nonce_url("?page=cl_pmw_outbox&action=view&id=$msg->id", 'cl_pmw-view_outbox_msg_' . $msg->id), '">', stripcslashes($msg->subject), '</a>';
                            ?>
                            <div class="row-actions">
							<span>
								<a href="<?php echo wp_nonce_url("?page=cl_pmw_outbox&action=view&id=$msg->id", 'cl_pmw-view_outbox_msg_' . $msg->id); ?>"><?php _e('View', 'cl_pmw'); ?></a>
							</span>
							<span class="delete">
								| <a class="delete"
                                     href="<?php echo wp_nonce_url("?page=cl_pmw_outbox&action=delete&id=$msg->id", 'cl_pmw-delete_outbox_msg_' . $msg->id); ?>"><?php _e('Delete', 'cl_pmw'); ?></a>
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
                    <th class="manage-column check-column"><input type="checkbox"/></th>
                    <th class="manage-column"><?php _e('Recipient', 'cl_pmw'); ?></th>
                    <th class="manage-column"><?php _e('Subject', 'cl_pmw'); ?></th>
                    <th class="manage-column"><?php _e('Date', 'cl_pmw'); ?></th>
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
