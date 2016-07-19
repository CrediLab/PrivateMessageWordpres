<div class="wrap">
	<h2><?php _e( 'Private Messages Options', 'cl_pmw' ); ?></h2>

	<div style="width:600px;float:left">
		<form method="post" action="options.php">

			<?php
			settings_fields( 'option_group' );
			$option = get_option( 'option' );

			echo '<h3>', __( 'Please set numbers of private messages for each user role:', 'cl_pmw' ), '</h3>';
			echo '<p>', __( '<b><i>0</i></b> means <b><i>unlimited</i></b>', 'cl_pmw' ), '</p>';
			echo '<p>', __( '<b><i>-1</i></b> means <b><i>not allowed</i></b> to send PM', 'cl_pmw' ), '</p>';
			?>

			<table class="form-table">
				<?php foreach (get_editable_roles() as $role_name => $role_info): ?>
				<tr>			
					<th><?php _e($role_name, 'cl_pmw' ); ?></th>				
					<td>
						<?php $val = 'role_'.$role_name; ?>
						<input type="text" name="option['.$val.']" value="<?php echo isset($option[$val]) ? $option[$val] : $default_option[$val]; ?>"/>
					</td>	
				</tr>
				<?php endforeach; ?>
				<tr>
					<th><?php _e( 'How do you want to choose recipient?', 'cl_pmw' ); ?></th>
					<td>
						<input type="radio" name="option[type]" value="dropdown" <?php if ( $option['type'] == 'dropdown' )
							echo 'checked="checked"'; ?> /><?php _e( 'Dropdown list', 'cl_pmw' ); ?>
						<input type="radio" name="option[type]" value="autosuggest" <?php if ( $option['type'] == 'autosuggest' )
							echo 'checked="checked"'; ?> /><?php _e( 'Auto suggest from user input', 'cl_pmw' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Delete messages older than (days)', 'cl_pmw' ); ?></th>
					<td><input type="text" name="option[expires]" value="<?php echo $option['expires']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Shortest time interval for sending a message (seconds)', 'cl_pmw' ); ?></th>
					<td><input type="text" name="option[interval]" value="<?php echo $option['interval']; ?>"/>
					</td>
				</tr>
			</table>
		
			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save Changes', 'cl_pmw' ) ?>"/>
			</p>

		</form>

	</div>
	
</div>