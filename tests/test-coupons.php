<?php
/**
 * Jilt Coupon Management Coupons Tests.
 *
 * @since   0.0.0
 * @package Jilt_Coupon_Management
 */
class JCM_Coupons_Test extends WP_UnitTestCase {

	/**
	 * Test if our class exists.
	 *
	 * @since  0.0.0
	 */
	function test_class_exists() {
		$this->assertTrue( class_exists( 'JCM_Coupons' ) );
	}

	/**
	 * Test that we can access our class through our helper function.
	 *
	 * @since  0.0.0
	 */
	function test_class_access() {
		$this->assertInstanceOf( 'JCM_Coupons', jilt_coupon_management()->coupons );
	}

	/**
	 * Replace this with some actual testing code.
	 *
	 * @since  0.0.0
	 */
	function test_sample() {
		$this->assertTrue( true );
	}
}
