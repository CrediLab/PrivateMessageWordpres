<div class="wrap">
	<h2><?php _e( 'Private Messages Options', 'pmw' ); ?></h2>

	<div style="width:600px;float:left">
		<form method="post" action="options.php">

			<?php
			settings_fields( 'option_group' );
			$option = get_option( 'option' );

			echo '<h3>', __( 'Please set numbers of private messages for each user role:', 'pmw' ), '</h3>';
			echo '<p>', __( '<b><i>0</i></b> means <b><i>unlimited</i></b>', 'pmw' ), '</p>';
			echo '<p>', __( '<b><i>-1</i></b> means <b><i>not allowed</i></b> to send PM', 'pmw' ), '</p>';


			?>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Administrator', 'pmw' ); ?></th>
					<td>
						<input type="text" name="option[administrator]" value="<?php echo $option['administrator']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Editor', 'pmw' ); ?></th>
					<td><input type="text" name="option[editor]" value="<?php echo $option['editor']; ?>"/></td>
				</tr>
				<tr>
					<th><?php _e( 'Author', 'pmw' ); ?></th>
					<td><input type="text" name="option[author]" value="<?php echo $option['author']; ?>"/></td>
				</tr>
				<tr>
					<th><?php _e( 'Contributor', 'pmw' ); ?></th>
					<td>
						<input type="text" name="option[contributor]" value="<?php echo $option['contributor']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Subscriber', 'pmw' ); ?></th>
					<td><input type="text" name="option[subscriber]" value="<?php echo $option['subscriber']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'How do you want to choose recipient?', 'pmw' ); ?></th>
					<td>
						<input type="radio" name="option[type]" value="dropdown" <?php if ( $option['type'] == 'dropdown' )
							echo 'checked="checked"'; ?> /><?php _e( 'Dropdown list', 'pmw' ); ?>
						<input type="radio" name="option[type]" value="autosuggest" <?php if ( $option['type'] == 'autosuggest' )
							echo 'checked="checked"'; ?> /><?php _e( 'Auto suggest from user input', 'pmw' ); ?>
					</td>
				</tr>
			</table>

			
			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save Changes', 'pmw' ) ?>"/>
			</p>

		</form>

	</div>
	
</div>