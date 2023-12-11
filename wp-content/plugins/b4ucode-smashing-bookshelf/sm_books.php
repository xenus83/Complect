<?php 
/*
Plugin Name: SM Books
Plugin URI: http://b4ucode.com
Description: Displays Books and books
Author: B4uCode
Version: 1.0.0
Author URI: http://b4ucode.com
*/

//Register Book Post Type 
add_action( 'init', 'create_sm_book_type' );
function create_sm_book_type() {
	register_post_type( 'sm_books',
		array(
			'labels' => array(
				'name' => __( 'Books' ),
				'singular_name' => __( 'Book' )
			),
		'public' => true,
		'has_archive' => true,
		'supports' => array('title','editor','thumbnail')
		)
	);
}

//Add Book Categories
add_action( 'init', 'sm_book_taxonomies', 0 );
function sm_book_taxonomies(){
register_taxonomy('sm_book_category', 'sm_books',
 array(
 'hierarchical'=>true,
 'label'=>'Categories')
 );
}

add_action( 'add_meta_boxes', 'sm_book_extra_box' );
add_action( 'save_post', 'lfm_card_save_post_data' );

function sm_book_extra_box() {
    add_meta_box(
        'sm_bk_info_box',
        __( 'Book Information', 'sm_bk_info_box' ), 
        'sm_bk_info_box',
        'sm_books',
	'side'
    );
}

/* Prints the box content */
function sm_bk_info_box( $post ) {

	$price = get_post_meta( $post->ID, 'price', true );
	$sale_price =  get_post_meta( $post->ID, 'sale_price', true );
	$link_amazon =  get_post_meta( $post->ID, 'link_amazon', true );
	$link_google =  get_post_meta( $post->ID, 'link_google', true );
	$link_apple =  get_post_meta( $post->ID, 'link_apple', true );
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'sm_book_noncename' );
	?>

<p>
  <label for="price">Price
  <input type="text" name="price"
   id="price" size="10" value="<?php echo $price; ?>" />
  </label>
</p>
<p>
  <label for="sale_price">Sale Price
  <input type="text" name="sale_price"
   id="sale_price" size="10" value="<?php echo $sale_price; ?>" />
  </label>
</p>
<p>
  <label for="link_amazon">Amazon Link
  <input type="text" name="link_amazon"
   id="link_amazon" size="25" value="<?php echo $link_amazon; ?>" />
  </label>
</p>
<p>
  <label for="link_google">Google Link
  <input type="text" name="link_google"
   id="link_google" size="25" value="<?php echo $link_google; ?>" />
  </label for="myplugin_new_field">
</p>
<p>
  <label for="link_apple">Apple Link
  <input type="text" name="link_apple"
   id="link_apple" size="25" value="<?php echo $link_apple; ?>" />
  </label>
</p>
<?php
}
function lfm_card_save_post_data($post_id){
if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

   if ( !wp_verify_nonce( $_POST['sm_book_noncename'],
    plugin_basename( __FILE__ ) ) )
      return;

   if ( !current_user_can( 'edit_post', $post_id ) )
        return;

  	if( isset( $_POST['price'] ) ){
		update_post_meta( $post_id,'price', 
        esc_attr( $_POST['price'] ) );
	}
  	if( isset( $_POST['sale_price'] ) ){
		update_post_meta( $post_id,'sale_price', 
        esc_attr( $_POST['sale_price'] ) );
	}
	if( isset( $_POST['link_amazon'] ) ){
		update_post_meta( $post_id,'link_amazon', 
        esc_attr( $_POST['link_amazon'] ) );
	}
	if( isset( $_POST['link_google'] ) ){
		update_post_meta( $post_id,'link_google', 
        esc_attr( $_POST['link_google'] ) );
	}
	if( isset( $_POST['link_apple'] ) ){
		update_post_meta( $post_id,'link_apple', 
        esc_attr( $_POST['link_apple'] ) );
	}
}

//Enable Thumbnail
if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
        set_post_thumbnail_size( 150, 150 );
		add_image_size( 'book-thumb', 84, 107, true );
}


function sm_display($atts)
{
	extract( shortcode_atts( array('category' => ''), $atts ) );

	$args = array('post_type'=>'sm_books', 'sm_book_category'=>$category);

	$posts = new WP_Query( $args );
	$html='<div class="sm_holder">
	<div class="shelf">
	<div class="innerDiv" id="sm_book_1">';
  // Book Loop 
  if ( $posts->have_posts() ) : while ( $posts->have_posts() ) : $posts->the_post();
  $book_cover = get_the_post_thumbnail(
  get_the_ID(),'book-thumb', 
  $attr=array(
        "alt"=>get_the_title())
        );
   $html.='<a href="'.get_permalink().'" class="books">'.$book_cover.'</a>';
		endwhile; endif;
		$html.='</div>
		</div>
  <table class="sm_book_tbl" cellspacing="0" cellpadding="0">';
    // The Loop 

   if ( $posts->have_posts() ) : while ( $posts->have_posts() ) : $posts->the_post();

    $price =  get_post_meta( get_the_ID(), 'price', true );
    $sale_price =  get_post_meta( get_the_ID(), 'sale_price', true );
    $link_amazon =  get_post_meta( get_the_ID(), 'link_amazon', true );
    $link_google =  get_post_meta( get_the_ID(), 'link_google', true );
    $link_apple =  get_post_meta( get_the_ID(), 'link_apple', true );		

    $html.='
    <tr>
  <td class="title"><a href="'.get_permalink().'">'.get_the_title().'</a><br>';
  if($link_amazon): 
    $html.='<small><a style="color:#999" href="'.$link_amazon.'">Amazon</a>';
    if($link_google || $link_apple):
    $html.=' | ';  
    endif;
    endif;
    if($link_google): 
     $html.='<a style="color:#999" href="'.$link_google.'">Google</a></small>';
    if($link_apple):    
    $html.=' | '; 
    endif;
    endif;
    if($link_apple):
     $html.='<a style="color:#999" href="'.$link_apple.'">Apple</a></small>';
   endif;
  $html.='</td>
  <td>'; 

    if($sale_price && $price)
    { 
    $html.=$sale_price.'<br />';
    $html.='<span class="old_price">'.$price.'</span>';
    }elseif($price){
    $html.=$price;
    }else{
         $html.='';
    }
     $html.='</td>
 <td class="cart">
 <a style="margin:0px" class="sm_cart_button" href="'.get_permalink().'">
 Add to Cart
 </a>
 </td>
    </tr>';
  endwhile;  endif;
    $html.='</table>';
 $html.='</div>';
 return $html;
}

	 add_shortcode( 'sm_books', 'sm_display' );
	 
	 function sm_add_styles()
{
	wp_register_style( 'sm_add_styles',
	 plugins_url('sm_books/style.css', __FILE__) );
    wp_enqueue_style( 'sm_add_styles' );
}

add_action( 'wp_enqueue_scripts', 'sm_add_styles' );