<p>
<div class="row">
	<div class="col-75">
		<label for="<?php esc_attr_e($name); ?>" class="lfm_meta_box__label"><?php esc_attr_e($title); ?></label>
	</div>
	<div class="col-25">
		<input type="<?php esc_attr_e($type); ?>" name="<?php esc_attr_e($name); ?>" class="lfm_meta_box__input"
		       id="<?php esc_attr_e($name); ?>" value="<?php  esc_attr_e($value); ?>" />
	</div>
</div>
</p>