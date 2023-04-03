<?php
?>
<tr class="form-field">
	<th scope="row"><label>$term-></label></th>
	<td>
		<input type="text" name="extra[title]" value="<?php echo esc_attr( get_term_meta( $term->term_id, 'title', 1 ) ) ?>"><br />
		<span class="description">SEO заголовок (title)</span>
	</td>
</tr>
