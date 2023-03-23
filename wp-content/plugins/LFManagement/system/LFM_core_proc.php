<?php

namespace system;

class LFM_core_proc {

	public static function render_template($template_name, $args) {

		extract($args);
		ob_start();
		if (file_exists( $template_name )) {
			require $template_name;
		} else {
			echo "Template $template_name not found!";
		}
		return ob_get_clean();
	}
}