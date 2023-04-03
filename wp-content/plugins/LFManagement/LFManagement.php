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
		add_action( 'init', [$this,'lfm_register_script']);
		add_action( 'init',[$this, 'create_data_types']);
		add_action( 'admin_menu', [$this,'add_lfm_menu_page']);
		add_action( 'init', [$this,'lfm_card_taxonomies'] );
		add_action( 'add_meta_boxes_lfm_card', [$this, 'add_card_meta_box'] );
		add_action( 'save_post_lfm_card', [$this, 'lfm_card_post_data__save'] );
		add_action( 'wp_enqueue_scripts',[$this,'lfm_enqueue_css']);
		add_action( 'admin_enqueue_scripts',[$this,'lfm_enqueue_css']);
		add_action('lfm_card_item_type_edit_form_fields ',[$this,'lfm_fnc']);
		add_action('lfm_card_item_type_add_form_fields',[$this,'lfm_fnc']);
	}

	protected array $data_structure = [
		'lfm_card'=>
			[
				'taxonomies'=>
				[
					'lfm_card_item_type'=>
					[
						'labels' => [
							 'name' => 'Вид изделия'
							,'singular_name' => 'Вид изделия'
						]
						,'fields' =>[
							'lfm_card_item_type__acc_unit' =>[
							'type' => 'string'
							]
						]
					]
				]
			]
	];
	static function activation() : void {
		flush_rewrite_rules();
	}

	static function deactivation() : void{
		flush_rewrite_rules();
	}
	function lfm_register_script() : void{

		wp_register_style('lfm_styles',plugins_url('/css/lfm_styles.css',__FILE__));
	}
	function lfm_enqueue_css() : void{
		$log = date('Y-m-d H:i:s') . ' Запись в лог lfm_enqueue_css script';
		//file_put_contents(__DIR__ . '/log.log', $log . PHP_EOL, FILE_APPEND);
		wp_enqueue_style('lfm_styles');


	}
	static function lfm_render_term_meta_fields__div($var1) : void{

	}
	static function lfm_render_term_meta_fields__tr($var1) : void{

	}
	static function lfm_render_term_meta_fields($taxonomy, $input_view) : void {
		$taxonomy = get_taxonomy($taxonomy);
		if(!$taxonomy) die("задано неверное имя таксономии для вывода атрибутов");
		$meta_feilds = wp_get_regist();
	}
	function render_lfm_page() : void {
		echo "<h1>Настройка плагина lfm Plugin</h1>";
	}

	function add_lfm_menu_page() : void {
		add_menu_page( 'Управление комплектованием', 'Комплектование', 'manage_options', 'lfmanagement/lfm_menu.php', [$this,'render_lfm_page']);
	}

	function create_data_types() : void {

//		console_log( self::class, null, 0, "register post_types" );
        $post_type_args = array(
	        'public' => true
        ,'label' => esc_html__('Тип изделия', 'lfmanagement')
        ,'show_ui' => true
        ,'show_in_menu' => 'lfmanagement/lfm_menu.php'
        ,'has_archive' => true
        ,'support' => ['title','author', 'custom-fields']
        );
		//Описание типа изделия
		//тип изделия, книга, брошюра, аудиокассета и т.д.
		register_post_type('lfm_item_type', $post_type_args);
		remove_post_type_support( 'lfm_item_type', 'editor');//отключаем текстовый блок

		//Название вида изделия
		$meta_args = array(
			'type'              => 'string',
			'object_subtype'    => 'lfm_item_type',
			'description'       => 'Описание вида изделия',
			'single'            => TRUE,
			'sanitize_callback' => null,
			'auth_callback'     => null,
			'show_in_rest'      => TRUE,
		);
		register_meta('post', 'lfm_item_type__description',$meta_args);
		//

		//end тип изделия

        //запись
        $post_type_args['label'] = esc_html__('Запись', 'lfmanagement');
		register_post_type('lfm_record', $post_type_args);
		remove_post_type_support( 'lfm_record', 'editor');

        //филиал
		$post_type_args['label'] = esc_html__('Филиал', 'lfmanagement');
			register_post_type('lfm_filial', $post_type_args);
		remove_post_type_support( 'lfm_filial', 'editor');

		$post_type_args['label'] = esc_html__('Карточка', 'lfmanagement');
		register_post_type('lfm_card', $post_type_args);
		remove_post_type_support( 'lfm_card', 'editor');

		self::create_card_type();

		//описание изделия, книги, или чего-то ещё библиотечного //TODO заменить на taxonomy?
		$post_type_args['label'] = esc_html__('Изделие', 'lfmanagement');
		register_post_type('lfm_item', $post_type_args);
		remove_post_type_support( 'lfm_item', 'editor');

		//инвентарный номер изделия inv
		$meta_args = array(
			'type'              => 'string',
			'object_subtype'    => 'lfm_item',
			'description'       => 'Инв. № изделия',
			'single'            => TRUE,
			'sanitize_callback' => null,
			'auth_callback'     => null,
			'show_in_rest'      => TRUE,
		);
		register_meta('post', 'lfm_item__inv',$meta_args);

//todo  точно заменить на таксономию
		register_post_type('lfm_BBK', array(
			'public' => true
			,'label' => esc_html__('ББК', 'lfmanagement')
			,'show_ui' => true
			,'show_in_menu' => 'lfmanagement/lfm_menu.php'
			,'has_archive' => true
			, 'support' => ['title','author', 'custom-fields']
		));

//
//		$args = array(
//			'public'   => true,
//			'_builtin' => false
//		);
//		$output   = 'names'; // names or objects, note names is the default
//		$operator = 'and';   // 'and' or 'or'
//
//		$post_types = array();
//		$post_types = get_post_types( $args, $output, $operator );
//		console_log( self::class, $post_types, 0, "post_types" );
	}

    private function create_card_type() : void{

	    //isbn
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'isbn изделия',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__isbn',$args);
	    //Год
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Год издания',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__year',$args);
	    //цена изделия - cost
	    $args = array(
		    'type'              => 'number',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Цена',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__cost',$args);
	    // Сведения к заглавию
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Сведения к заголовку',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__title_info',$args);
	    // Том
	    $args = array(
		    'type'              => 'integer',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Том',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__volume_p',$args);
	    // Свед об отв //TODO уточнить полное наименование
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Свед об отв',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__otv_info',$args);
	    // Свед изд //TODO уточнить полное наименование
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Свед изд',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__izd_info',$args);

	    // Место издания
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Место издания',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__publishing_place',$args);

	    // Издательство
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Издательство',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__publishing_house',$args);

	    // Сист. треб
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Сист. треб',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__sys',$args);

	    // ОснЗаглСер //TODO уточнить полное наименование
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'ОснЗаглСер',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__osnzaglser',$args);
	    // Примечание
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Примечание',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__note',$args);
	    // Содержание
	    $args = array(
		    'type'              => 'string',
		    'object_subtype'    => 'lfm_card',
		    'description'       => 'Содержание',
		    'single'            => TRUE,
		    'sanitize_callback' => null,
		    'auth_callback'     => null,
		    'show_in_rest'      => TRUE,
	    );
	    register_meta('post', 'lfm_card__resume',$args);
	    //
	    //card
    }

	function add_card_meta_box($post) : void{
		add_meta_box('lfm_card_meta_box', esc_html__('Данные карточки', 'lfmanagement'), [$this,'lfm_card_meta_box__render'],'lfm_Card');
//		console_log( self::class, NULL, 0,  'test');
	}

	static function lfm_card_meta_box__render($post) : void{
        $params =[];
		$params['isbn'] = get_post_meta( $post->ID, 'lfm_card__isbn', true );
		$params['year'] = get_post_meta( $post->ID, 'lfm_card__year', true );
		$params['cost'] = get_post_meta( $post->ID, 'lfm_card__cost', true );
		$params['title_info'] = get_post_meta( $post->ID, 'lfm_card__title_info', true );
		$params['volume_p'] = get_post_meta( $post->ID, 'lfm_card__volume_p', true );
		$params['otv_info'] = get_post_meta( $post->ID, 'lfm_card__otv_info', true );
		$params['izd_info'] = get_post_meta( $post->ID, 'lfm_card__izd_info', true );
		$params['publishing_place'] = get_post_meta( $post->ID, 'lfm_card__publishing_place', true );
		$params['publishing_house'] = get_post_meta( $post->ID, 'lfm_card__publishing_house', true );
		$params['sys'] = get_post_meta( $post->ID, 'lfm_card__sys', true );
		$params['osnzaglser'] = get_post_meta( $post->ID, 'lfm_card__osnzaglser', true );
		$params['note'] = get_post_meta( $post->ID, 'lfm_card__note', true );
		$params['resume'] = get_post_meta( $post->ID, 'lfm_card__resume', true );

        //автозаполняем год издания текущим если он пустой
		$params['year'] = (""!==$params['year'] ? $params['year']: date( 'Y', time() ));
		$res = wp_nonce_field( plugin_basename( __FILE__ ), 'lfm_card_noncenamenoncename',TRUE, FALSE );
        $res .= LFM_core_proc::render_template(dirname(__FILE__).'/templates/lfm_card.php',$params);
		echo $res;

	}
	static function lfm_card_post_data__save($post_id) : void{

//		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )//todo а зачем, может убрать?
//  		return;


		if ( !wp_verify_nonce( $_POST['lfm_card_noncenamenoncename'],
			plugin_basename( __FILE__ ) ) )
			return;


		if ( !current_user_can( 'edit_post', $post_id ) )
			return;

		if( isset( $_POST['lfm_card__isbn'] ) ){
			update_post_meta( $post_id,'lfm_card__isbn',
				esc_attr( $_POST['lfm_card__isbn'] ) );
		}
		if( isset( $_POST['lfm_card__year'] ) ){
			update_post_meta( $post_id,'lfm_card__year',
				esc_attr( $_POST['lfm_card__year'] ) );
		}
        if( isset( $_POST['lfm_card__cost'] ) ){
			update_post_meta( $post_id,'lfm_card__cost',
				esc_attr( $_POST['lfm_card__cost'] ) );
		}
		if( isset( $_POST['lfm_card__title_info'] ) ){
			update_post_meta( $post_id,'lfm_card__title_info',
				esc_attr( $_POST['lfm_card__title_info'] ) );
		}
        if( isset( $_POST['lfm_card__volume_p'] ) ){
			update_post_meta( $post_id,'lfm_card__volume_p',
				esc_attr( $_POST['lfm_card__volume_p'] ) );
		}
        if( isset( $_POST['lfm_card__otv_info'] ) ){
			update_post_meta( $post_id,'lfm_card__otv_info',
				esc_attr( $_POST['lfm_card__otv_info'] ) );
		}
        if( isset( $_POST['lfm_card__izd_info'] ) ){
			update_post_meta( $post_id,'lfm_card__izd_info',
				esc_attr( $_POST['lfm_card__izd_info'] ) );
		}
        if( isset( $_POST['lfm_card__publishing_place'] ) ) {
	        update_post_meta( $post_id, 'lfm_card__publishing_place',
		        esc_attr( $_POST['lfm_card__publishing_place'] ) );
        }
        if( isset( $_POST['lfm_card__publishing_house'] ) ){
			update_post_meta( $post_id,'lfm_card__publishing_house',
				esc_attr( $_POST['lfm_card__publishing_house'] ) );
		}
        if( isset( $_POST['lfm_card__sys'] ) ){
			update_post_meta( $post_id,'lfm_card__sys',
				esc_attr( $_POST['lfm_card__sys'] ) );
		}
        if( isset( $_POST['lfm_card__osnzaglser'] ) ){
			update_post_meta( $post_id,'lfm_card__osnzaglser',
				esc_attr( $_POST['lfm_card__osnzaglser'] ) );
		}
        if( isset( $_POST['lfm_card__osnzaglser'] ) ){
			update_post_meta( $post_id,'lfm_card__osnzaglser',
				esc_attr( $_POST['lfm_card__osnzaglser'] ) );
		}
        if( isset( $_POST['lfm_card__note'] ) ){
			update_post_meta( $post_id,'lfm_card__note',
				esc_attr( $_POST['lfm_card__note'] ) );
		}
        if( isset( $_POST['lfm_card__resume'] ) ){
			update_post_meta( $post_id,'lfm_card__resume',
				esc_attr( $_POST['lfm_card__resume'] ) );
		}

//		file_put_contents(__DIR__ . '/log.log', print_r($_POST,true) . PHP_EOL, FILE_APPEND);
        if( isset( $_POST['lfm_card_age_rating'] ) ){
	        $rating = sanitize_text_field( $_POST['lfm_card_age_rating'] );

	        if (! empty( $rating ) ) {
                $term = get_term_by( 'name', $rating, 'lfm_card_age_rating' );
                if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
                    wp_set_object_terms( $post_id, $term->term_id, 'lfm_card_age_rating', false );
                }
	        }
        }

	}

	function lfm_card_taxonomies() : void {
		 register_taxonomy('lfm_card_age_rating', 'lfm_card',
			[
				'hierarchical'=>FALSE,
				'labels'=>[
                     'name'=> esc_html__('Возрастные ограничения','lfmanagement')
                    ,'singular_name' => esc_html__('Возрастная группа','lfmanagement')
                ]
			,'show_ui'           => true
			,'show_admin_column' => true
			,'query_var'         => true
			,'show_in_menu'      => true
			,'show_in_rest'      => true
			,'meta_box_cb'       => [$this, 'lfm_default_select_meta_box__render']
		]
        );
		register_taxonomy('lfm_card_item_type', 'lfm_card', [
				'hierarchical'=>FALSE,
				'labels'=>[
					'name'=> esc_html__('Вид изделия','lfmanagement')
					,'singular_name' => esc_html__('Вид изделия','lfmanagement')
				]
			,'show_ui'           => true
			,'show_admin_column' => true
			,'query_var'         => true
			,'show_in_menu'      => true
			,'show_in_rest'      => true
			,'meta_box_cb'       => [$this, 'lfm_default_select_meta_box__render']
			]
		);
		register_term_meta( 'lfm_card_item_type', 'lfm_card_item_type__acc_unit', array(
//			'show_in_rest'      => true     // Добавим в ответ REST
			 'sanitize_callback' => NULL // Обработаем значение поля при сохранение его в базу, функцией absint()
			,'description'       => 'Единица объёма'//например страницы, минуты, зависит от того, что за вид изделия например для компакт дисков это мегабайты
			,'single'            => TRUE
			,'show_in_rest' => ['schema' => [
				'type' => 'string',
				'format' => 'url',
				'context' => [ 'view', 'edit' ],
				'readonly' => true,
			]]
		) );


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

	//		console_log( self::class, $post_types, 0, "post_types" );

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
			if ( isset( $current[0] ) && isset( $current[0]->name ) ) {
				$params['current_name'] = $current[0]->name;
			}
		}
		switch ( $output_type ) {
			case 'select' : echo LFM_core_proc::render_template( $template_dir . 'lfm_default_select_meta_box_template.php', $params ); break;
			case 'radio' : echo LFM_core_proc::render_template( $template_dir . 'lfm_default_radio_meta_box_template.php', $params ); break;
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
}

if(class_exists('lfmanagement')){
	$lfManagement = new lfmanagement();
}

register_activation_hook(__FILE__,[$lfManagement, 'activation'] );
register_deactivation_hook( __FILE__, [$lfManagement, 'deactivation'] );
