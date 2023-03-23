<label for="<?php $taxonomy->name ?>" title='Выберите <?php esc_attr_e( $taxonomy->label ); ?>'>Выберите <?php esc_attr_e( $taxonomy->label ); ?>:</label>
<select id=""<?php $taxonomy->name ?>" name =""<?php $taxonomy->name ?>">
	<?php
	foreach ( $terms as $term ) {
		?>
		<option value="<?php esc_attr_e( $term->name ); ?>" <?php selected( $term->name, $current_name ); ?>><?php esc_attr_e( $term->name ); ?></option>
		<br>
		<?php
	}
	?>
</select>