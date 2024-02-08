<label><?php esc_attr_e($taxonomy->label) ?></label>
<br>
<?php
foreach ( $terms as $term ) {
	?>
	<label title='<?php esc_attr_e( $term->name ); ?>'>
		<input type="radio" name="<?php esc_attr_e($term->taxonomy) ?>" value="<?php esc_attr_e( $term->name ); ?>" <?php checked( $term->name, $current_name ); ?>>
		<span><?php esc_html_e( $term->name ); ?></span>
	</label><br>
	<?php
}
?>