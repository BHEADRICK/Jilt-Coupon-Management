<?php
/**
 * Jilt Coupon Management Coupons.
 *
 * @since   0.0.0
 * @package Jilt_Coupon_Management
 */

/**
 * Jilt Coupon Management Coupons.
 *
 * @since 0.0.0
 */
class JCM_Coupons {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.0
	 *
	 * @var   Jilt_Coupon_Management
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.0.0
	 *
	 * @param  Jilt_Coupon_Management $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.0
	 */
	public function hooks() {
        add_action('restrict_manage_posts', [$this, 'manage_posts']);
        add_filter( 'parse_query', array( $this, 'filter_coupons' ) );
        add_filter('woocommerce_coupon_discount_types', [$this, 'coupon_types']);
        add_filter('request', [$this, 'request_query'], 11);
        add_action('jilt_coupon_cleanup', [$this, 'coupon_cleanup']);
        add_action('jilt_coupon_delete', [$this, 'coupon_delete']);
	}

	public function coupon_delete($coupon_id){
	    error_log('deleting coupon ' . $coupon_id);
        $coupon = new WC_Coupon($coupon_id);
        $coupon->delete(true);
    }


	public function coupon_cleanup(){
            global $wpdb;

	    $sql = "select pm.post_id from $wpdb->postmeta pm
join $wpdb->postmeta j on pm.post_id = j.post_id && j.meta_key = 'jilt_discount_id'
where pm.meta_key = 'expiry_date' && pm.meta_value < curdate() && pm.meta_value != '';";
//join wp_postmeta u on u.post_id = pm.post_id and u.meta_key = 'usage_count'
	    $expired_coupons = $wpdb->get_col($sql);

	    $delay = 0;
	    foreach($expired_coupons as $coupon_id){
           wp_schedule_single_event(time() + $delay, 'jilt_coupon_delete', [$coupon_id]);
           $delay +=300;
        }


    }

	public function request_query($query_vars){
        global $typenow;
        if($typenow == 'shop_coupon'){
            global $wpdb;



            if($query_vars['meta_key'] === 'discount_type' && $query_vars['meta_value'] === 'jilt'){

                $sql = "select post_id from $wpdb->postmeta where meta_key = 'jilt_discount_id'";
            $coupon_ids = $wpdb->get_col($sql);
            $query_vars['post__in'] = $coupon_ids;
            unset($query_vars['meta_key']);
            unset($query_vars['meta_value']);
            }
        }
	    return $query_vars;
    }

	public function coupon_types($types){
	    $types['jilt'] = 'Jilt Coupon';

	    return $types;
    }

	public function manage_posts(){
        global $typenow;

        if($typenow !== 'shop_coupon'){
            return;
        }
    }


    public function filter_coupons( $query )
    {
        global $typenow;
        global $pagenow;




        if( $pagenow == 'edit.php' && $typenow == 'shop_coupon')
        {
            global $wpdb;

            $sql = "select post_id from $wpdb->postmeta where meta_key = 'jilt_discount_id'";

            $coupon_ids = $wpdb->get_col($sql);

            $coupon_type = filter_input(INPUT_GET, 'coupon_type');

            if($coupon_type === 'jilt'){
                $query->query_vars['post__in'] = $coupon_ids;
            }else{
                $query->query_vars['post__not_in'] = $coupon_ids;
            }




        }
    }
}
