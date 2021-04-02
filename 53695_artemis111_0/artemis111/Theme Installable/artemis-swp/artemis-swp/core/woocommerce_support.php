<?php

/*
	Declare WooCommerce Support
*/
add_theme_support('woocommerce');


/*
	Unhook the WooCommerce Wrappers
*/
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_after_main_content', 'woocommerce_breadcrumb', 20);

function ARTEMIS_SWP_remove_shop_sidebar() {
	/*
		remove sidebar - when the user select this option from theme settings
	*/
	if ( !ARTEMIS_SWP_need_sidebar_on_woo() ) {
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );
	}
}
add_action('woocommerce_before_main_content', 'ARTEMIS_SWP_remove_shop_sidebar');


/*
	Hook in own functions to display the wrappers that JamSession theme requires
*/
add_action('woocommerce_before_main_content', 'ARTEMIS_SWP_woocommerce_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'ARTEMIS_SWP_woocommerce_wrapper_end', 10);

function ARTEMIS_SWP_woocommerce_wrapper_start() {

	if (ARTEMIS_SWP_need_sidebar_on_woo()) {
		/* content with sidebar here, boxed container in header/footer */
		if (is_shop() || 
			is_product_category() || 
			is_product_tag()) {
			$additional_class = " has_shop_sidebar_".ARTEMIS_SWP_get_theme_option('artemis_theme_general_options', 'lc_shop_sidebar');
		} else {
			/*is product page*/
			/*TODO: what if we have sidebar: make it full anyway*/
			$additional_class = " has_shop_sidebar_".ARTEMIS_SWP_get_theme_option('artemis_theme_general_options', 'lc_shop_sidebar_single');
		}
		
		echo '<div class="lc_content_with_sidebar'.esc_attr($additional_class).'">';
	} else {
	    /*todo: check product page type; if default => boxed*/
	    $boxed_class = "lc_swp_boxed";
		if (is_product()) {
		    if( ARTEMIS_SWP_get_product_page_template() != 'default'
            && ARTEMIS_SWP_get_product_page_template() != 'type_3') {
			    $boxed_class = "lc_swp_full";
            }
		}
		if (is_shop()) {
			$boxed_class = ARTEMIS_SWP_get_shop_width_class();
		}

		echo '<div class="lc_content_full '.esc_attr($boxed_class). ' lc_big_content_padding">';
	}
}

function ARTEMIS_SWP_woocommerce_wrapper_end() {
	echo '</div>';
}

/* 
	Ensure cart contents update when products are added to the cart via AJAX (place the following in functions.php) 
*/
//add_filter('woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment');
function woocommerce_header_add_to_cart_fragment($fragments)
{
	ob_start();
	?>
	<a class="cart-contents " href="<?php echo wc_get_cart_url(); ?>" title="<?php esc_html__('View your shopping cart', 'artemis-swp'); ?>">
		<i class="fa fa-shopping-bag" aria-hidden="true"></i>
		<span class="cart-contents-count">
			<?php echo esc_html(WC()->cart->get_cart_contents_count()); ?>
		</span>
	</a>
	<?php
	
	$fragments['a.cart-contents'] = ob_get_clean();
	
	return $fragments;
}


if (!function_exists('ARTEMIS_SWP_minicart_quantity')) {
    function ARTEMIS_SWP_minicart_quantity($value, $cart_item, $cart_item_key) {
        $output = '<dl>' .
                  '<dt>' . esc_html__('Qty', 'artemis-swp').  ':</dt>' .
                  '<dd>' . sprintf('%s', $cart_item['quantity']).  '</dd>' .
                  '</dl>';
        return $output;
    }
}
add_filter('woocommerce_widget_cart_item_quantity', 'ARTEMIS_SWP_minicart_quantity', 10, 3);

remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
add_action( 'woocommerce_checkout_shipping', 'woocommerce_checkout_payment', 20 );


function ARTEMIS_SWP_checkout_forms( $address_fields, $country ) {
	foreach ( $address_fields as $key => &$data ) {
		if ( isset( $data['label'] ) ) {
			$data['placeholder'] = $data['label'];
			unset( $data['label'] );
		}
	}

	return $address_fields;
}
add_filter( 'woocommerce_billing_fields', 'ARTEMIS_SWP_checkout_forms', 10, 2 );
add_filter( 'woocommerce_shipping_fields', 'ARTEMIS_SWP_checkout_forms', 10, 2 );


function ARTEMIS_SWP_woocommerce_review_order_before_payment() {
	$allowed_tags = array(
		'h3' => array(
			'class'	=> array()
		)
	);

	echo wp_kses(__('<h3 class="pay_info_title">Payment Information</h3>', 'artemis-swp'), $allowed_tags);
}
add_action( 'woocommerce_review_order_before_payment', 'ARTEMIS_SWP_woocommerce_review_order_before_payment' );



function ARTEMIS_SWP_woocommerce_gateway_icon( $html, $id ) {
	if ( $id == 'paypal' ) {
		$icons =  '<i class="fa fa-cc-mastercard"></i>';
		$icons .= '<i class="fa fa-cc-visa"></i>';
		$icons .= '<i class="fa fa-paypal"></i>';
		$icons .= '<i class="fa fa-cc-discover"></i>';
		$html = '<span class="artemis-swp-paypal-icons">' . $icons . "</span>" . $html;
	}

	return $html;
}
add_filter( 'woocommerce_gateway_icon', 'ARTEMIS_SWP_woocommerce_gateway_icon', 10, 2 );



function ARTEMIS_SWP_woocommerce_checkout_order_review() {
	echo '<h3>' . esc_html__( 'Order summary', 'artemis-swp' ) . '</h3>';
}
add_action('woocommerce_checkout_order_review', 'ARTEMIS_SWP_woocommerce_checkout_order_review',1);
remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );



function ARTEMIS_SWP_artemis_swp_order_payment_method_icon($payment_method, $order){
    if ( $payment_method == 'paypal' ) { ?>
        <span class="artemis-swp-paypal-icons"><i class="fa fa-paypal"></i></span>
    <?php }
}
add_action('artemis_swp_order_payment_method_icon', 'ARTEMIS_SWP_artemis_swp_order_payment_method_icon', 10, 2);



function ARTEMIS_SWP_woocommerce_clear_cart_url() {
  global $woocommerce;

    if ( isset( $_GET['empty-cart'] ) ) {
        $woocommerce->cart->empty_cart();
    }
}
add_action( 'init', 'ARTEMIS_SWP_woocommerce_clear_cart_url' );



function ARTEMIS_SWP_add_cart_buttons() {
    $emptyCartUrl = add_query_arg( 'empty-cart', 1, wc_get_cart_url() ); ?>
    <a class="button alt at_clear_cart" href="<?php echo esc_url( $emptyCartUrl ) ?>">
        <?php echo esc_html__( 'Clear Shopping Cart', 'artemis-swp' ) ?>
    </a>
    <?php
}
add_action('woocommerce_cart_actions', 'ARTEMIS_SWP_add_cart_buttons');



/*
	WooCommerce Util function
	Checks if we need to place the sidebar on woocommerce pages
	Used also outside of WooCommerce - need to check if WooCommerce is active
*/
function ARTEMIS_SWP_need_sidebar_on_woo() {
	if (!ARTEMIS_SWP_is_woocommerce_active()) {
		return false;
	}

	if (!ARTEMIS_SWP_shop_has_sidebar() && !ARTEMIS_SWP_single_product_has_sidebar()) {
		return false;
	}

	if (is_shop() || 
		is_product_category() || 
		is_product_tag()) {
		return ARTEMIS_SWP_shop_has_sidebar();
	}

	if (is_product()) {
		/*single product page has individual sidebar setting*/
		return ARTEMIS_SWP_single_product_has_sidebar();

	}

	return false;
}

//region WishList
function ARTEMIS_SWP_wishlist_button() {
    global $post;
    $wishlist_products = ARTEMIS_SWP_get_wishlist_products();

    if ( isset( $wishlist_products[ $post->ID ] ) ) {
        $text = sprintf( '<span class="artemis_swp_already_on_wishlist" title="%s"><i class="fa fa-heart"></i> <span>%s</span></span>', esc_html__( 'Already on wishlist', 'artemis-swp' ), esc_html__( 'Already on wishlist', 'artemis-swp' ) );
        $btn = apply_filters( 'artemis_swp_filter_already_on_wishlist', $text );
    }
    else {
        $text = sprintf( '<a href="#" class="artemis_swp_add_to_wishlist at_first_color" title="%s" data-wishlist-id="%s"><i class="fa fa-heart-o"></i> <span>%s</span></a>', esc_html__( 'Add to wishlist', 'artemis-swp' ), $post->ID, esc_html__( 'Add to wishlist', 'artemis-swp' ) );
        $btn = apply_filters( 'artemis_swp_filter_add_to_wishlist', $text );
    }
	echo wp_kses( $btn, array(
		'a'    => array( 'href' => array(), 'class' => array(), 'title' => array(), 'data-wishlist-id' => array() ),
		'i'    => array( 'class' => array() ),
		'span' => array( 'class' => array(), 'title' => array() )
	) );
}
add_action( 'woocommerce_after_add_to_cart_form', 'ARTEMIS_SWP_wishlist_button', 30 );

function ARTEMIS_SWP_sharing_icons() {
	?>
	<div class="at_share_product">
		<?php get_template_part('views/utils/sharing_icons'); ?>
	</div>
	<div class="clearfix"></div>
	<?php
}
add_action( 'woocommerce_after_add_to_cart_form', 'ARTEMIS_SWP_sharing_icons', 30 );

function ARTEMIS_SWP_add_to_wishlist() {
	$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'], 10 ) : 0;

	try {
		$product = wc_get_product( $product_id );

		$wl_product = array(
			'id'           => $product->get_id(),
			'price'        => $product->get_price_html(),
			'title'        => $product->get_title(),
			'image'        => $product->get_image( 'shop_thumbnail'),
			'product_type' => $product->product_type,
			'permalink'    => $product->get_permalink()
		);

		$wishlist = ARTEMIS_SWP_get_wishlist_products();

		if ( ! isset( $wishlist[ $product->get_id() ] ) ) {
			$wishlist[ $product->get_id() ] = $wl_product;
			ARTEMIS_SWP_update_wishlist_products( $wishlist );

			set_query_var( 'artemis_swp_product', $product );
			ob_start();
			get_template_part( 'views/utils/mini_wishlist','item' );
			$mini_wishlist = ob_get_clean();
			$response = array(
				'success'            => true,
				'code'               => 1,
				'message'            => esc_html__( 'Added to wishlist', 'artemis-swp' ),
				'product'            => $wl_product,
				'mini_wishlist_item' => $mini_wishlist
			);
		} else {
			$response = array(
				'success' => true,
				'code'    => 2,
				'message' => esc_html__( 'Already in wishlist', 'artemis-swp' ),
				'product' => $wl_product
			);
		}

	} catch( Exception $e ) {
        $response = array(
	        'error'   => true,
	        'message' => $e->getMessage()
        );
	}
	echo json_encode( $response );
	exit;
}
add_action( 'wp_ajax_artemis_swp_add_to_wishlist', 'ARTEMIS_SWP_add_to_wishlist'  );
add_action( 'wp_ajax_nopriv_artemis_swp_add_to_wishlist',  'ARTEMIS_SWP_add_to_wishlist' );

function ARTEMIS_SWP_remove_from_wishlist() {
	$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'], 10 ) : 0;
	$wishlist = ARTEMIS_SWP_get_wishlist_products();


	if ( isset( $wishlist[ $product_id ] ) ) {
		unset( $wishlist[ $product_id ] );
		ARTEMIS_SWP_update_wishlist_products( $wishlist );
		$response = array(
		        'success' => true,
                'products_in_wishlist' => count($wishlist)
        );
	} else {
		$response = array(
			'error'   => true,
			'message' => esc_html__( 'Product not found in wishlist', 'artemis-swp' )
		);
	}
	echo json_encode( $response );
	exit;
}
add_action( 'wp_ajax_artemis_swp_remove_from_wishlist', 'ARTEMIS_SWP_remove_from_wishlist'  );
add_action( 'wp_ajax_nopriv_artemis_swp_remove_from_wishlist',  'ARTEMIS_SWP_remove_from_wishlist' );

function ARTEMIS_SWP_remove_all_from_wishlist() {
	ARTEMIS_SWP_update_wishlist_products( array() );
	echo json_encode( array( 'success' => true ) );
	exit;
}
add_action( 'wp_ajax_artemis_swp_remove_all_from_wishlist', 'ARTEMIS_SWP_remove_from_wishlist'  );
add_action( 'wp_ajax_nopriv_artemis_swp_remove_all_from_wishlist',  'ARTEMIS_SWP_remove_from_wishlist' );
//endregion

function ARTEMIS_SWP_product_custom_description() {
	get_template_part( 'views/woocommerce', 'custom-product-description' );
}

function ARTEMIS_SWP_product_tab_heading() {
    return '';
}
function ARTEMIS_SWP_product_description_tab( $tabs = array() ) {
    global $post;

    $override_description = get_post_meta( $post->ID, 'lc_swp_meta_override_description', true );

    if ( intval( $override_description, 10 ) ) {

	    $custom_description = get_post_meta( $post->ID, 'lc_swp_meta_custom_description', true );
	    if( !$custom_description ) {
	        unset($tabs['description']);
        }
        elseif ( isset( $tabs['description'] ) ) {
            $tabs['description']['callback'] = 'ARTEMIS_SWP_product_custom_description';
        }
    }
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = esc_html__('Details', 'artemis-swp');
    }

    if( 'default' == ARTEMIS_SWP_get_product_page_template() ){
        add_filter('woocommerce_product_description_heading', 'ARTEMIS_SWP_product_tab_heading');
        add_filter('woocommerce_product_additional_information_heading', 'ARTEMIS_SWP_product_tab_heading');
    }
    return $tabs;
}
add_filter('woocommerce_product_tabs', 'ARTEMIS_SWP_product_description_tab', 11);

function ARTEMIS_SWP_product_description() {
    global $post;
	$override_description = get_post_meta( $post->ID, 'lc_swp_meta_override_description', true );
	//remove all floating before displaying content
	echo '<div class="clearfix"></div>';
	if ( intval( $override_description, 10 ) ) {
		the_content();
	}
}
add_action('woocommerce_after_single_product', 'ARTEMIS_SWP_product_description');

if ( ARTEMIS_SWP_get_product_page_template() == 'type_1' ) {
    function ARTEMIS_SWP_after_single_product_summary() {
        echo '<div class="artemis_swp_full_line"></div>';
    }

    add_action( 'woocommerce_after_single_product_summary', 'ARTEMIS_SWP_after_single_product_summary', 1 );
}

function ARTEMIS_SWP_product_body_class($classes) {

	$classes = (array) $classes;

	if( ARTEMIS_SWP_is_woocommerce_active() && is_product()) {
	    $classes[] = 'artemis_swp_template-' . ARTEMIS_SWP_get_product_page_template();
    }
	return $classes;
}
add_filter('body_class', 'ARTEMIS_SWP_product_body_class');

function ARTEMIS_SWP_post_class( $classes ){

	$classes = (array) $classes;

	if(ARTEMIS_SWP_is_woocommerce_active() && is_product()) {
	    $classes[] = 'clearfix';
    }
	return $classes;
}
add_filter('post_class', 'ARTEMIS_SWP_post_class');


function ARTEMIS_SWP_after_product_images() {
    global $post, $product, $woocommerce;
    ?>
    <div class="artemis_swp_gallery_thumbnails clearfix">
    <?php
    $attachment_ids = $product->get_gallery_image_ids();

    if ( has_post_thumbnail() ) {
        $props = wc_get_product_attachment_props( get_post_thumbnail_id(), $post );
        $image = wp_get_attachment_image( get_post_thumbnail_id(), apply_filters( 'single_product_small_thumbnail_size', 'thumbnail' ), 0 );

        echo apply_filters(
            'woocommerce_single_product_image_thumbnail_html',
            sprintf(
                '<a href="%s" class="%s" title="%s" class="wp-post-thumb-image">%s</a>',
                esc_url( $props['url'] ),
                'artemis_swp_gallery_thumbnail active',
                esc_attr( $props['caption'] ),
                $image
            ),
            $post->ID
        );
    } else {
        $placeholder_src = wc_placeholder_img_src();
        printf( '<a href="%s" class="%s" title="%s" ><img src="%s" alt="%s" class="wp-post-thumb-image"/></a>',
                $placeholder_src,
                'artemis_swp_gallery_thumbnail active',
                esc_html__( 'Awaiting product image', 'artemis-swp' ),
                $placeholder_src,
                esc_html__( 'Awaiting product image', 'artemis-swp' ) );
    }
    if ( $attachment_ids ) {
        foreach ( $attachment_ids as $attachment_id ) {

            $props = wc_get_product_attachment_props( $attachment_id, $post );

            if ( ! $props['url'] ) {
                continue;
            }

            echo apply_filters(
                'woocommerce_single_product_image_thumbnail_html',
                sprintf(
                    '<a href="%s" class="%s" title="%s">%s</a>',
                    esc_url( $props['url'] ),
                    'artemis_swp_gallery_thumbnail',
                    esc_attr( $props['caption'] ),
                    wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'thumbnail' ), 0 )
                ),
                $attachment_id,
                $post->ID,
                ''//esc_attr( $image_class )
            );
        }


    }
    ?></div><?php
}
if ( ARTEMIS_SWP_get_product_page_template() == 'type_2' ) {
    global $wp_query;
    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);

    add_action('artemis_swp_before_content', 'woocommerce_show_product_images');
    function ARTEMIS_SWP_before_single_product_summary_type2() {
        echo "<div class='clearfix'></div>";
        echo "<div class='artemis_swp_single_product_details_container clearfix'>";
    }
    //priority = 21 => after image gallery
    add_action( 'woocommerce_before_single_product_summary', 'ARTEMIS_SWP_before_single_product_summary_type2', 21 );
    function ARTEMIS_SWP_after_single_product_summary_type2() {
        echo "</div>";
    }
    //priority = 11 => after woocommerce_output_product_data_tabs
    add_action( 'woocommerce_after_single_product_summary', 'ARTEMIS_SWP_after_single_product_summary_type2', 11 );
    add_action('woocommerce_after_product_images','ARTEMIS_SWP_after_product_images');
}

if ( ARTEMIS_SWP_get_product_page_template() == 'type_3' ) {
    add_action( 'woocommerce_after_product_images', 'ARTEMIS_SWP_after_product_images' );
}

//wrap loop product title in link
add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 9 );
add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 11 );

function ARTEMIS_SWP_quickview_button() {
	global $post;
	$quick_view_url = add_query_arg(
		array( 'action' => 'artemis_swp_quick_view', 'product_id' => $post->ID ),
		admin_url( 'admin-ajax.php' )
	);
	echo '<span class="artemis_swp_quickview_button">' .
         '<a data-src="' . esc_attr( $quick_view_url ) . '" ' .
         'title="' .esc_attr__( 'Quick View', 'artemis-swp' ). '" ' .
         'data-caption="' .esc_attr( $post->post_title ). '" ' .
         'href="javascript:void(0)" ' .
         'data-type="ajax">' .
         '<i class="fa fa-eye"></i>' .
         '</a>' .
         '</span>';
}

function ARTEMIS_SWP_show_grid_mode() {
    $grid_mode = ARTEMIS_SWP_get_products_view_mode();
    ?>
    <div class="at_product_list_mode_container">
        <form id="at_product_list_mode_form">
            <input type="hidden" id="at_product_view_mode" name="mode" value="<?php esc_attr($grid_mode) ?>">
            <a data-mode="grid" class="at_product_list_mode grid <?php echo ( 'grid' == $grid_mode ) ? 'active' : '' ?>">
                <i class="fa fa-grid-view"></i>
            </a>
            <a data-mode="list" class="at_product_list_mode list <?php echo ( 'list' == $grid_mode ) ? 'active' : '' ?>">
                <i class="fa fa-list-view"></i>
            </a>
	        <?php
		        // Keep query string vars intact
		        foreach ( $_GET as $key => $val ) {
			        if ( 'mode' === $key || 'submit' === $key ) {
				        continue;
			        }
			        if ( is_array( $val ) ) {
				        foreach ( $val as $innerVal ) {
					        echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $innerVal ) . '" />';
				        }
			        } else {
				        echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" />';
			        }
		        }
	        ?>
        </form>
    </div>
    <?php
}
add_action('woocommerce_before_shop_loop', 'ARTEMIS_SWP_show_grid_mode', 10);


function ARTEMIS_SWP_set_product_view_mode() {
    if ( isset( $_REQUEST['mode'] ) ) {
        $grid_mode = intval( $_REQUEST['mode'] );
	    if ( ! in_array( $grid_mode, array('grid', 'list') ) ) {
		    $grid_mode = 'grid';
	    }
        setcookie( 'artemis_swp_products_view_mode', $grid_mode );
    }
}
add_action( 'init', 'ARTEMIS_SWP_set_product_view_mode' );
function ARTEMIS_SWP_open_wishlist_quickview_container() {
    echo '<div class="at_wishlist_quickview_btns">';
}
function ARTEMIS_SWP_close_wishlist_quickview_container() {
    echo '</div>';
}
if('grid' == ARTEMIS_SWP_get_products_view_mode() ){
    function ARTEMIS_SWP_product_loop_top_container_open(){
        echo '<div class="at_product_loop_top_container">';
    }
    add_action('woocommerce_before_shop_loop_item', 'ARTEMIS_SWP_product_loop_top_container_open', 5);

    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
    remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

    add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 11 );

    //add add to cart inside mask after woocommerce_template_loop_product_thumbnail - 10
    function ARTEMIS_SWP_open_product_list_mask() {
        global $post;
        echo '<div class="at_product_actions_mask lc_js_link" data-href="'. esc_attr(get_permalink($post->ID)) .'" data-atcot="0">';
    }
    add_action( 'woocommerce_before_shop_loop_item_title', 'ARTEMIS_SWP_open_product_list_mask', 11 );

    add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_rating', 15 );

    add_action( 'woocommerce_before_shop_loop_item_title', 'ARTEMIS_SWP_open_wishlist_quickview_container', 19 );
    add_action( 'woocommerce_before_shop_loop_item_title', 'ARTEMIS_SWP_wishlist_button', 20 );
    add_action( 'woocommerce_before_shop_loop_item_title', 'ARTEMIS_SWP_quickview_button', 20 );
	add_action( 'woocommerce_before_shop_loop_item_title', 'ARTEMIS_SWP_close_wishlist_quickview_container', 21 );

    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
    add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 30 );

    function ARTEMIS_SWP_close_product_list_mask() {
        echo "</div>";
    }
    add_action( 'woocommerce_before_shop_loop_item_title', 'ARTEMIS_SWP_close_product_list_mask', 50);

    function ARTEMIS_SWP_product_loop_top_container_close() {
        echo '</div>';
    }
    add_action( 'woocommerce_before_shop_loop_item_title', 'ARTEMIS_SWP_product_loop_top_container_close', 100 );

    add_filter( 'loop_shop_columns', 'ARTEMIS_SWP_get_products_per_row' );

    function ARTEMIS_SWP_products_per_row_buttons( ) {
        $ppr = ARTEMIS_SWP_get_products_per_row(); ?>
        <div class="at_products_per_row_container">
            <form id="at_products_per_page_form" method="get">
                <input type="hidden" id="at_products_per_row" name="products_per_row" value="<?php echo esc_attr($ppr) ?>">
                <?php esc_attr_e('Show Grid:', 'artemis-swp') ?>
                <a href="#" data-per_page="3" class="at_products_per_row_item <?php echo esc_attr($ppr == 3 ? 'active' : ''); ?>">3</a>
                <a href="#" data-per_page="4" class="at_products_per_row_item <?php echo esc_attr($ppr == 4 ? 'active' : ''); ?>">4</a>
                <a href="#" data-per_page="5" class="at_products_per_row_item <?php echo esc_attr($ppr == 5 ? 'active' : ''); ?>">5</a>
                <?php
                    // Keep query string vars intact
                    foreach ( $_GET as $key => $val ) {
                        if ( 'products_per_row' === $key || 'submit' === $key ) {
                            continue;
                        }
                        if ( is_array( $val ) ) {
                            foreach ( $val as $innerVal ) {
                                echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $innerVal ) . '" />';
                            }
                        } else {
                            echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" />';
                        }
                    }
                ?>
            </form>
        </div>
        <?php
    }
    add_action( 'woocommerce_before_shop_loop', 'ARTEMIS_SWP_products_per_row_buttons', 25 );

    function ARTEMIS_SWP_set_products_per_row() {
        if ( isset( $_REQUEST['products_per_row'] ) ) {
            $ppr = intval( $_REQUEST['products_per_row'] );
            if ( 3 <= $ppr && $ppr <= 5 ) {
                setcookie( 'artemis_swp_products_per_row', $ppr );
            }
        }
    }
    add_action( 'init', 'ARTEMIS_SWP_set_products_per_row' );
}
else {
    //list mode
    //before woocommerce_template_loop_product_link_open - 10
    add_action('woocommerce_before_shop_loop_item', create_function('', 'echo "<div class=\"at_product_image_container\">";'), 1);

    //remove product link wrapping image
	remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

    //before woocommerce_template_loop_product_title - 10
    add_action('woocommerce_shop_loop_item_title', create_function('', 'echo "</div>";'), 1);

    add_action('woocommerce_shop_loop_item_title', create_function('', 'echo "<div class=\"at_product_details_container\">";'), 2);

    function ARTEMIS_SWP_product_list_short_description() {
        global $product;
        $excerpt = get_the_excerpt($product->get_id());
        if( ! $excerpt ){
            return;
        }
        ?>
        <p class="at_product_short_description"><?php echo esc_html($excerpt); ?></p>
        <?php
    }
    add_action('woocommerce_after_shop_loop_item_title', 'ARTEMIS_SWP_product_list_short_description', 20);

    //after add to cart
	add_action( 'woocommerce_after_shop_loop_item', 'ARTEMIS_SWP_open_wishlist_quickview_container', 19 );
	add_action( 'woocommerce_after_shop_loop_item', 'ARTEMIS_SWP_wishlist_button', 20 );
	add_action( 'woocommerce_after_shop_loop_item', 'ARTEMIS_SWP_quickview_button', 20 );
	add_action( 'woocommerce_after_shop_loop_item', 'ARTEMIS_SWP_close_wishlist_quickview_container', 21 );

	add_action( 'woocommerce_after_shop_loop_item', create_function( '', 'echo "</div>";' ), 50 );
}
function ARTEMIS_SWP_pagination_args( $args ) {
    $args['prev_text'] = esc_html__('Prev', 'artemis-swp');
    $args['next_text'] = esc_html__('Next', 'artemis-swp');
    return $args;
}
add_filter('woocommerce_pagination_args', 'ARTEMIS_SWP_pagination_args');

function ARTEMIS_SWP_back_to_shop_button(){
    if ( wc_get_page_id( 'shop' ) > 0 ) { ?>
        <a class="button wc-backward at_back_to_shop"
           href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
                <?php esc_html_e( 'Back To Shop', 'artemis-swp' ) ?>
        </a>
	<?php }
}
add_action( 'woocommerce_proceed_to_checkout', 'ARTEMIS_SWP_back_to_shop_button', 20 );
remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
add_action( 'woocommerce_cart_actions', 'woocommerce_button_proceed_to_checkout', 20 );

remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 5 );

function ARTEMIS_SWP_collaterals_two_columns() {
    echo '</div><div class="two_of_two">';
}
add_action( 'woocommerce_cart_collaterals', 'ARTEMIS_SWP_collaterals_two_columns', 9 );

function ARTEMIS_SWP_order_totals( $total_rows, $order ) {
    if( isset($total_rows['order_total']) ) {
        $total_rows['order_total']['label'] = esc_html__('Grand Total','artemis-swp');
    }
    return $total_rows;
}

add_filter( 'woocommerce_get_order_item_totals', 'ARTEMIS_SWP_order_totals', 10, 2 );

remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

function ARTEMIS_SWP_add_to_cart_contents_count( $fragments ) {
	$fragments['at_cart_contents_count'] = esc_html( WC()->cart->get_cart_contents_count() );

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'ARTEMIS_SWP_add_to_cart_contents_count' );

function ARTEMIS_SWP_quick_view_remove_additional() {
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
}
add_action('artemis_swp_quick_view_before', 'ARTEMIS_SWP_quick_view_remove_additional');

function ARTEMIS_SWP_quick_view_body_class($classes){
    $classes[] = 'woocommerce';
    $classes[] = 'at_quick_view';
    return $classes;
}
function ARTEMIS_SWP_quick_view_post_class($classes){
    $classes[] = 'at_quick_view_container';
    $classes[] = 'product';
    return $classes;
}

function ARTEMIS_SWP_quick_view() {
    add_filter( 'body_class', 'ARTEMIS_SWP_quick_view_body_class' );
    ?>

            <?php
            try {
                if ( ! ARTEMIS_SWP_is_woocommerce_active() ) {
                    throw new Exception( esc_html__( 'Product not available', 'artemis-swp' ) );
                }
                if(class_exists('WC_Shortcodes')) {
                    WC_Shortcodes::init();
                }
                $product_id = absint( $_GET['product_id'] );

                $meta_query = WC()->query->get_meta_query();

                $args = array(
                    'post_type'      => 'product',
                    'posts_per_page' => 1,
                    'no_found_rows'  => 1,
                    'post_status'    => 'publish',
                    'meta_query'     => $meta_query,
                    'p'              => $product_id
                );



                if ( isset( $atts['id'] ) ) {
                    $args['p'] = $atts['id'];
                }

                $products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, array('id' => $product_id) ) );

                if ( $products->have_posts() ) {
                    remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
                    add_filter( 'post_class', 'ARTEMIS_SWP_quick_view_post_class' );
                    while( $products->have_posts() ) {
                        $products->the_post();
                        global $product;

                        if ( empty( $product ) || ! $product->is_visible() ) {
                            throw new Exception( 'Product not available', 'artemis-swp' );
                        }
                        do_action('artemis_swp_quick_view_before');
                        get_template_part( 'views/product-quick-view' );
                        do_action( 'artemis_swp_quick_view_after' );
                    } // end of the loop.
                } else {
                    throw new Exception( esc_html__( 'Product was not found', 'artemis-swp' ) );
                }
            } catch( Exception $e ) {
                echo "<div class='at_error'>{$e->getMessage()}</div>";
            }
            ?>
    <?php exit;
}
add_action( 'wp_ajax_artemis_swp_quick_view', 'ARTEMIS_SWP_quick_view' );
add_action( 'wp_ajax_nopriv_artemis_swp_quick_view', 'ARTEMIS_SWP_quick_view' );


remove_action('woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10);
add_action('woocommerce_review_before_comment_text', 'woocommerce_review_display_rating', 10);
