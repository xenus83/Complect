<tr class="form-field">
	<th scope="row"><label for="<?php esc_attr_e($name); ?>" class="lfm_meta_box__label"><?php esc_attr_e($title); ?></label></th>
	<td>
		<input type="hidden" name="<?php esc_attr_e($name); ?>" value="" />
		<input type="<?php esc_attr_e($type); ?>" name="<?php esc_attr_e($name); ?>" class="lfm_meta_box__input"
		       id="<?php esc_attr_e($name); ?>" value="<?php  esc_attr_e($value); ?>" <?php esc_attr_e($checked);?> /><br />
		<span class="description"><?php esc_attr_e($descr); ?></span>

	</td>
</tr>
