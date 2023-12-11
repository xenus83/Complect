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
	public static function file_log( $value ) : void{

		if( is_array( $value ) || is_object( $value ) )
		{
			$value = print_r( $value,true );
		}
		elseif( is_bool( $value ) )
		{
			$value = ( $value ? 'true' : 'false' );
		}

		file_put_contents(__DIR__ . '/../log.log', $value . PHP_EOL, FILE_APPEND);
	}

	public static function read_json_file($file) {
		if(file_exists($file))
		{	$json = json_decode(file_get_contents($file), true);
			return $json;
		}
		else
		{
			self::file_log(["module" => self::class, "error" => "read_json_file", "message" => "json_file ".$file." don't exist"]);
			return 1;
		}
	}
}