<?php
/**
 * Fields.
 *
 * @package WooCommerceTicketManager\Admin
 * @since   1.0.0
 *
 * @var array $types Types.
 * @var array $fields Fields.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="options_group">
	<div class="form-field">
		<label>
			<?php esc_html_e( 'Ticket Fields', 'wc-ticket-manager' ); ?>
		</label>
		<table class="widefat striped">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Label', 'wc-ticket-manager' ); ?></th>
				<th><?php esc_html_e( 'Placeholder', 'wc-ticket-manager' ); ?></th>
				<th><?php esc_html_e( 'Type', 'wc-ticket-manager' ); ?></th>
				<th><?php esc_html_e( 'Required', 'wc-ticket-manager' ); ?></th>
				<th><?php esc_html_e( 'Enabled', 'wc-ticket-manager' ); ?></th>
			</thead>
			<tbody>
			<?php foreach ( $fields as $key => $field ) : ?>
				<tr>
					<td>
						<input type="hidden" name="_wctm_ticket_fields[<?php echo esc_attr( $key ); ?>][name]" value="<?php echo esc_attr( empty( $field['name'] ) ? uniqid( 'wctm_' ) : $field['name'] ); ?>" />
						<input type="text" class="input-text" placeholder="<?php esc_attr_e( 'Name', 'wc-ticket-manager' ); ?>" name="_wctm_ticket_fields[<?php echo esc_attr( $key ); ?>][label]" value="<?php echo esc_attr( $field['label'] ); ?>">
					</td>
					<td>
						<input type="text" class="input-text" placeholder="<?php esc_attr_e( 'Placeholder', 'wc-ticket-manager' ); ?>" name="_wctm_ticket_fields[<?php echo esc_attr( $key ); ?>][placeholder]" value="<?php echo esc_attr( $field['placeholder'] ); ?>">
					</td>
					<td>
						<select name="_wctm_ticket_fields[<?php echo esc_attr( $key ); ?>][type]">
							<?php foreach ( $types as $type => $label ) : ?>
								<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $field['type'], $type ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>

						<input type="checkbox" class="input-checkbox" name="_wctm_ticket_fields[<?php echo esc_attr( $key ); ?>][required]" <?php checked( $field['required'], 'yes' ); ?> value="yes">
					</td>
					<td>
						<input type="checkbox" class="input-checkbox" name="_wctm_ticket_fields[<?php echo esc_attr( $key ); ?>][enabled]" <?php checked( $field['enabled'], 'yes' ); ?> value="yes">
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
