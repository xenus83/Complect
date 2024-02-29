<?php
/*
  Plugin Name: Library Fund Management
Description: Плагин ООФиК.
Version: 1.0
Author: Cherkashin Pavel Victorovich
Text Domain: lfmanagement
 * */
?>
<?php
/*  Copyright ГОД  ИМЯ_АВТОРА_ПЛАГИНА  (email: E-MAIL_АВТОРА)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
if(!function_exists('add_action')){
	die;
};
?>
<?php
require_once dirname( __FILE__ ) . '/system/LFM_core_proc.php';
use system\LFM_core_proc;
class LFManagement
{
	function __construct() {
		add_action( 'init', [$this,'lfm_register_script'] );
		add_action( 'init',[$this, 'create_data_types'] );
		add_action( 'wp_enqueue_scripts', [$this, 'lfm_enqueue_css'] );
		add_action( 'admin_enqueue_scripts', [$this, 'lfm_enqueue_css'] );
		add_action( 'admin_menu', [$this,'add_lfm_menu_page'] );
		// add_action( 'init', [$this,'lfm_card_taxonomies'] );
		add_action( 'add_meta_boxes_lfm_card', [$this, 'add_card_meta_box'] );
		add_action( 'add_meta_boxes_lfm_author', [$this, 'add_author_meta_box'] );
		add_action( 'save_post', [$this, 'lfm_save_post_meta_data'] );
		add_action( 'lfm_card_item_type_edit_form_fields',[$this, 'lfm_render_term_meta_fields__tr'] );
		add_action( 'lfm_card_item_type_add_form_fields',[$this, 'lfm_render_term_meta_fields__div'] );
		add_action( "create_lfm_card_item_type", [$this, 'lfm_save_term_meta_data' ] );
		add_action( "edited_lfm_card_item_type", [$this, 'lfm_save_term_meta_data' ] );
	}


	protected array $default_data_structure; 

	protected array $data_structure = [];

	static function activation() : void {
		self::create_lfm_roles();
		flush_rewrite_rules();
	}

	static function deactivation() : void{
		self::remove_lfm_roles();
		flush_rewrite_rules();
	}
	function lfm_register_script() : void{
		wp_register_style('lfm_styles',plugins_url('/css/lfm_styles.css',__FILE__));
	}
	function lfm_enqueue_css() : void{
	//	$log = date('Y-m-d H:i:s') . ' Запись в лог lfm_enqueue_css script';
		//file_put_contents(__DIR__ . '/log.log', $log . PHP_EOL, FILE_APPEND);
		wp_enqueue_style('lfm_styles');
	}
	static function remove_lfm_roles() : void{
		remove_role('complect_admin');
		remove_role('complect_user');
	}
	private static function create_lfm_roles(){
		$result = add_role( 'complect_admin', 'Администратор комплектования',
			array(
				'read'         => true,  // true разрешает эту возможность
				'edit_posts'   => true,  // true разрешает редактировать посты
				'upload_files' => true,  //  может загружать файлы!
			)
		);

		$result = add_role( 'complect_user', 'Сотрудник комплектования',
			array(
				'read'         => true,  // true разрешает эту возможность
				'edit_posts'   => true,  // true разрешает редактировать посты
				'upload_files' => false,  //  может загружать файлы!
			)
		);
	}

	private function comparing_merging_fields(array $def_arr, array &$arr) : int{

		$f=0;
		$ff = 0;
		foreach($def_arr as $def_key => $def_val)
		{					
			if(is_array($def_val))
			{

				if(isset($arr[$def_key]))
				{			
					$ff = $this->comparing_merging_fields($def_val, $arr[$def_key]);
				}
				else
				{
					$arr[$def_key] = $def_val;
					$ff = $this->comparing_merging_fields($def_val, $arr[$def_key]);
				}

				if ($ff > 0) 
					$f = 1; //TODO что тогда длеать то?
			}
			else
			{
						
				if(isset($arr[$def_key])){
					if($arr[$def_key] === "_obligatory"){
						$ff = 1;							
						//TODO сделать какую-то обработку... дополнительно
					}
				}				
				else{
					
					if($def_val === "_obligatory"){
						// LFM_core_proc::file_log("key:".$def_key." val:".$def_val); //для отладки
						$ff = 1;
							//TODO сделать какую-то обработку... дополнительно
					}	
					else{
						$arr[$def_key] = $def_val;
					}						
				
				}
				$f = $ff;
			}
		}

		foreach($arr AS $dse_key => &$dse_val)
		{
			if(is_array($dse_val) && ($dse_key == 'post' || $dse_key == 'taxonomy' || $dse_key == 'meta' )) {
				foreach($dse_val AS $dse_e_key => &$dse_e_val){
					$ff = $this->comparing_merging_fields($this->default_data_structure[$dse_key], $dse_e_val);
				}
			}
		}
		$f = $ff;

		return $f; 
	}


	private function register_metafields($meta_fields_arr)
	{
		foreach($meta_fields_arr AS $meta_key => $meta_val){
			$ret = register_meta($meta_val['object_type'], $meta_val['meta_key'], $meta_val['meta_args']);
			if(is_object($ret))
			if('WP_Error' == get_class($ret)){
				LFM_core_proc::file_log($ret);
			}
		}
	}

	private function register_taxonomies($taxonomies_fields)
	{
		foreach($taxonomies_fields AS $tax_key => $tax_val){
			if(isset($tax_val['taxonomy_args']['meta_box_cb'])){
				$tax_val['taxonomy_args']['meta_box_cb'] = [$this, $tax_val['taxonomy_args']['meta_box_cb']];
				// LFM_core_proc::file_log("зис");
				// LFM_core_proc::file_log($this);
			}
			$ret = register_taxonomy($tax_val['taxonomy_name'], $tax_val['parent_objec_type'], $tax_val['taxonomy_args']);
			if(is_object($ret))
			if('WP_Error' == get_class($ret)){
				LFM_core_proc::file_log($ret);
			}
			if(isset($tax_val['meta'])){
				$this->register_metafields($tax_val['meta']);
			}
		}
	}

	private function make_objects_structure() : void {
		$this->default_data_structure = LFM_core_proc::read_json_file(dirname(__FILE__)."/system/lib_structure_defaults.json");
		if(1 === $this->default_data_structure) return; //TODO может что-то вывести пользователю?

		$json_data_structure = LFM_core_proc::read_json_file(dirname(__FILE__)."/lib_structure.json");
		if(1 === $json_data_structure) return; //TODO может что-то вывести пользователю?

		$ff = 0;
		foreach( $json_data_structure AS $ds_key => &$ds_val ) {			
			
			if( $ds_key == 'post' || $ds_key == 'taxonomy' || $ds_key == 'meta' ) {
				foreach($ds_val AS $dse_key => &$dse_val){
					$f = 0;
					$f = $this->comparing_merging_fields($this->default_data_structure[$ds_key],$dse_val);
					$dse_val['f'] = $f;
					if($f > 0) $ff = $f;
				}
			}
				
		}

		// LFM_core_proc::file_log($json_data_structure);

		// LFM_core_proc::file_log("merged_json_data_structure:");
		// LFM_core_proc::file_log($json_data_structure);

		if($ff > 0)
		{
			LFM_core_proc::file_log("структура воспроизведена с ошибкой, необходимо проверить логи");
			return;
		}
		
		
		foreach( $json_data_structure['taxonomy'] as $ds_key => $ds_val){
			$ret = register_taxonomy($ds_val['taxonomy_name'], $ds_val['parent_objec_type'], $ds_val['taxonomy_args']);
			if(is_object($ret))
			if('WP_Error' == get_class($ret)){
				LFM_core_proc::file_log($ret);
			}
			if(isset($ds_val['meta'])){
				$this->register_metafields($ds_val['meta']);
			}
		}

		foreach( $json_data_structure['post'] AS $post_key => $post_val)
		{
			if(isset( $post_val['taxonomy'] )){
				$this->register_taxonomies($post_val['taxonomy']);
			}
			$ret = register_post_type($post_val['post_type_name'],$post_val['post_type_args']);
			if(is_object($ret))
			if('WP_Error' == get_class($ret)){
				LFM_core_proc::file_log($ret);
			}
			remove_post_type_support( $post_val['post_type_name'], 'editor'); // удаляем текстовй блок
			
			if(isset($post_val['meta'])){
				$this->register_metafields($post_val['meta']);
			}
		}
		if(isset($json_data_structure['meta'])){
			$this->register_metafields($json_data_structure['meta']);
		}
		// GLOBAL $wp_taxonomies;
		// LFM_core_proc::file_log($wp_taxonomies);
	}


	function render_lfm_page() : void {
		echo "<h1>Настройка плагина lfm Plugin</h1>";
	}

	function add_lfm_menu_page() : void {
		add_menu_page( 'Управление комплектованием', 'Комплектование', 'manage_options', 'lfmanagement/lfm_menu.php', [$this,'render_lfm_page']);
//		$tax_edit_url = edit_term_link( 'Редактировать Вид изделия', '', '', 'lfm_card_item_type', false );
//		$tax_edit_url = apply_filters( 'edit_tag_link', $tax_edit_url );
		add_submenu_page( 'lfmanagement/lfm_menu.php', 'Редактировать вид изделия', 'Редактировать вид изделия','edit_posts', 'edit-tags.php?taxonomy=lfm_card_item_type&post_type=lfm_card', '', 6 );
//		$tax_edit_url = edit_term_link( 'Редактировать Вид изделия', '', '', 'lfm_card_age_rating', false );
//		$tax_edit_url = apply_filters( 'edit_tag_link', $tax_edit_url );
		add_submenu_page( 'lfmanagement/lfm_menu.php','Редактировать возрастные ограничения', 'Редактировать возрастные ограничения', 'edit_posts', 'edit-tags.php?taxonomy=lfm_card_age_rating&post_type=lfm_card', '', '5',  );
	}
	//TODO: проверить возможность применения wp_nav_menu_item_taxonomy_meta_box

	function create_data_types() : void {
		$f = self::make_objects_structure();

	}


	function add_card_meta_box($post) : void{
		add_meta_box('lfm_card_meta_box', esc_html__('Данные карточки', 'lfmanagement'), [$this,'lfm_card_meta_box__render'],'lfm_card');
	}
	function add_author_meta_box($post) : void{
		add_meta_box('lfm_author_meta_box', esc_html__('Данные автора', 'lfmanagement'), [$this,'lfm_post_fields_meta_box__render'],'lfm_author');
	}
	static function lfm_post_fields_meta_box__render($post) : void{
		self::lfm_render_meta_fields($post,'post','div');
	}
	static function lfm_card_meta_box__render($post) : void{
		self::lfm_render_meta_fields($post,'post','div');
	}

	static function remove_plugin_data() : void {
		GLOBAL $wpdb;
		$args = array(
			'public'   => true,
			'_builtin' => false
		);

		$output   = 'names'; // names or objects, note names is the default
		$operator = 'and';   // 'and' or 'or'

		$post_types = array();
		$post_types = get_post_types( $args, $output, $operator );

		foreach ($post_types as $key => $val)
		{
			if(!str_contains($val, 'lfm_')) { unset($post_types[$key]); }
		}

		$posts = array();
		$posts_args = ['numberposts' => -1, 'post_type' => $post_types];
		$posts = get_posts($posts_args);

		$in_pholders = implode( ',', array_fill( 0, count( $posts ), '%s' ) );

		//удаляем все мета-данные(поля) относящиеся к записям нашего плагина
		$sql = $wpdb->prepare(
			"DELETE $wpdb->postmeta WHERE post_id IN ( $in_pholders ) AND meta_key like 'lfm_%'"
		);
		$wpdb->query($sql);

		$sql =  $wpdb->prepare("DELETE $wpdb->posts WHERE ID in ( $in_pholders ) ");
		$wpdb->query($sql);


		foreach ($post_types as $pt_key=>$pt_val)
		{
			unregister_post_type($pt_val);
		}
	}

	static function lfm_default_meta_box__render($post, $params, $output_type) : void{

        $template_dir = plugin_dir_path(__FILE__)."templates/";
        $params['taxonomy'] = get_taxonomy($params["args"]["taxonomy"]);
		$params['terms'] = get_terms( ['taxonomy' => $params['taxonomy']->name,'hide_empty' => false ] );
        $params['current_name'] ='';
        $current = wp_get_object_terms($post->ID, $params['taxonomy']->name, ['orderby'=>'term_id', 'order'=>'ASC']);
		if ( ! is_wp_error( $current ) ) {
			if ( isset( $current[0]->name ) ) {
				$params['current_name'] = $current[0]->name;
			}
		}
		switch ( $output_type ) {
			case 'select' : echo LFM_core_proc::render_template( $template_dir . 'lfm_default_select_meta_box_template.php', $params ); break;
			case 'radio' : echo LFM_core_proc::render_template( $template_dir . 'lfm_default_radio_meta_field_template.php', $params ); break;
			case is_string( $output_type ) : echo LFM_core_proc::render_template( $output_type, $template_dir . $params . '.php' ); break;
			default : echo "функция отображения для " . $params['taxonomy']->label . " задана не верно";
		};
	}

    static function lfm_default_select_meta_box__render($post, $params) : void
    {
        self::lfm_default_meta_box__render($post,$params, "select");
    }
	static function lfm_default_radio_meta_box__render($post, $params) : void
	{
		self::lfm_default_meta_box__render($post,$params, "radio");
	}

	static function lfm_render_term_meta_fields__div($var1) : void{
		self::lfm_render_meta_fields($var1, 'term','div');
	}
	static function lfm_render_term_meta_fields__tr($var1) : void{
		self::lfm_render_meta_fields($var1,'term', 'tr');
	}
	static function lfm_render_meta_fields( $object, $object_type = 'post', $output_type = 'div' ) : void {
		//TODO: убрать закомментированное
		GLOBAL $wp_meta_keys;
		GLOBAL $wp_post_types;
		GLOBAL $wp_taxonomies;
		$output_string = '';
		$template_dir = plugin_dir_path(__FILE__)."templates/";
		$object_class = '';

		$meta_fields=[];

		if('term' == $object_type){
			if( is_a( $object, 'WP_Term' ) ) { //ага, значит это правка уже существующего терма, а не создание терма таксономии, но больше никакой разницы нет
				$meta_fields = $wp_meta_keys['term'][$object->taxonomy];
				$object_class = $object->taxonomy;
				$id = $object->term_id;
			}

			if( is_string( $object ) ){
				$object_class = $object;
				$object = get_taxonomy( $object );
				if( !$object )
					die( "задано неверное имя таксономии для вывода атрибутов" );
				$meta_fields = $wp_meta_keys['term'][$object->name];
			}
		}
		elseif ('post' == $object_type){
			$meta_fields = $wp_meta_keys['post'][$object->post_type];
			$id = $object->ID;
			$object_class = $object->post_type;
		}
		else {
			LFM_core_proc::file_log( 'Ошибка, передано значение вида объекта '.$object_type );
			die('задан неверный тип объекта');
		}

		$output_string = wp_nonce_field( plugin_basename( __FILE__ ), $object_class.'_wpnonce',TRUE, FALSE );

		foreach ( $meta_fields as $meta_field_key => $meta_field ){

			$params = [];
			$params['name'] = $meta_field_key;
			$params['value'] = ' ';
			$params['descr'] = ' ';
			$template = $template_dir;

			if( isset( $id ) ) {
					$params['value'] = get_metadata($object_type, $id, $meta_field_key, true );
			}
			$params['title'] = $meta_field['description'];
			if( 'string' == $meta_field['type'] ){
				$params['type'] = 'text';
				$template .= 'lfm_default_input_field_';
			}
			if( 'integer' == $meta_field['type'] || 'number' == $meta_field['type'] ){
				$params['type'] = 'number';
				$template .= 'lfm_default_input_field_';
			}
			if( 'boolean' == $meta_field['type']){
				$params['type'] = 'checkbox';
				$template .= 'lfm_default_single_checkbox_field_';
				$params['checked'] = '';
				if( $meta_field_key == $params['value'] ){
					$params['checked'] = 'checked';
				}
				$params['value'] = $meta_field_key;
			}
			//TODO: добвить доп параметры из настроечного массива

			if( 'tr' == $output_type )
				$template .= 'tr_';
			elseif( 'div' == $output_type )
				$template .= 'div_';
			else
				echo "функция отображения для " . $params['name'] . " задана не верно";

			$output_string .= LFM_core_proc::render_template( $template.'template.php', $params );
		}

		echo $output_string;
	}

	static function lfm_save_term_meta_data( $object_id ) : int {
		return self::lfm_save_meta_data('term', $object_id);
	}
	static function lfm_save_post_meta_data( $object_id ) : int {
		return self::lfm_save_meta_data('post', $object_id);
	}

	static function lfm_save_meta_data($type, $object_id ) : int {

		GLOBAL $wp_meta_keys;
		// [post_type] => lfm_author
		if(!count($_POST)) return 0;
		if ( 'post' != $type && 'term' != $type ) return 0;

		if ( ! current_user_can('edit_'.$type, $object_id) ) return 0;


		if('term' == $type){
			if(!isset($_POST['taxonomy'])) return 0;
			if(!str_starts_with($_POST['taxonomy'], 'lfm_')) return 0;
			$check1 = ! wp_verify_nonce( $_POST['_wpnonce'], "update-tag_$object_id" ) && // wp_nonce_field( 'update-tag_' . $tag_ID );
				! wp_verify_nonce( $_POST['_wpnonce_add-tag'], "add-tag" ); // wp_nonce_field('add-tag', '_wpnonce_add-tag');
			if($check1) return 0;
			}
		if('post' == $type) {
			if(!isset($_POST['post_type'])) return 0;
			if(!str_starts_with($_POST['post_type'], 'lfm_')) return 0;
			$check2 = ! wp_verify_nonce( $_POST[ $_POST['post_type'] . '_wpnonce' ], plugin_basename( __FILE__ ) );
			if($check2) return 0;
		}

		//// Все ОК! Теперь, нужно сохранить/удалить данные
		if ( 'term' == $type ){
			 $meta_fields = $wp_meta_keys['term'][$_POST['taxonomy']];
		}
		if ( 'post' == $type ){
			$meta_fields = $wp_meta_keys['post'][$_POST['post_type']];
			self::lfm_save_post_term_data($object_id);
		}

		foreach ($meta_fields AS $meta_field_key=>$meta_field){
			 if( isset( $_POST[$meta_field_key] ) ){
				 $val = wp_unslash( $_POST[$meta_field_key] );
				 $val = sanitize_text_field($val);
				 $_key = sanitize_key( $meta_field_key );

		        if( ! $val ) {
			        if ( 'term' == $type ) {
				        $res = delete_term_meta( $object_id, $meta_field_key );
			        }
			        if ( 'post' == $type ) {
				        $res = delete_post_meta( $object_id, $meta_field_key );
			        }
		        }
				else{
					if( 'term' == $type )
						$res = update_term_meta( $object_id, $meta_field_key, $val );
				    if( 'post' == $type )
					    $res = update_post_meta( $object_id, $meta_field_key, $val );
				}
			 }
			 elseif('boolean' == $meta_field['type'])
			 {
				 if( 'term' == $type )
				    $res =	delete_term_meta( $object_id, $meta_field_key );
				 if ( 'post' == $type )
					 $res = delete_post_meta( $object_id, $meta_field_key );
			 }
		}
		return $object_id;
	}

	static function lfm_save_post_term_data($post_ID) : bool
	{
		$post_type = get_post_type($post_ID);   
   		$taxonomies = get_object_taxonomies($post_type);  
		foreach($taxonomies AS $tax_id => $tax_name){
			if( isset( $_POST[$tax_name] ) ){
				$value = sanitize_text_field( $_POST[$tax_name] );
				if (! empty( $value ) ) {
					$term = get_term_by( 'name', $value, $tax_name );
					if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
						wp_set_object_terms( $post_ID, $term->term_id, $tax_name, false );
					}
				}
			}
		}
		return true;
	}

}

if(class_exists('lfmanagement')){
	$lfManagement = new lfmanagement();
}

register_activation_hook(__FILE__,[$lfManagement, 'activation'] );
register_deactivation_hook( __FILE__, [$lfManagement, 'deactivation'] );
