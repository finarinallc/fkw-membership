<?php
namespace FKW\Membership;

use FKW\Membership\Admin\Levels;


class Subscription extends Base {


	public function __construct() {

	}

	public function init() {
		add_filter('woocommerce_product_get_price', [ $this, 'make_free_levels_free' ], 10, 2);
		add_filter('woocommerce_product_get_regular_price', [ $this, 'make_free_levels_free' ], 10, 2);
	}

	public function make_free_levels_free( $price, $product ) {
		// Define the product IDs that should be free for a specific membership level.
		$level_class = Levels::get_instance();
		$product_level = get_post_meta( $product->get_id(), '_product_membership_level', true );

		if( $level_class->is_level_free( $product_level ) ) {
			return 0;
		}

		return $price;
	}
}
