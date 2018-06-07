<?php
/**
 * Notice if guest checkout is enabled.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="error">
    <p>
        <strong><?php esc_html_e( 'Coupon First Buy Only for WooCommerce', 'coupon-first-buy-only' ); ?></strong> <?php esc_html_e( "won't work if guest checkout is enabled.", 'coupon-first-buy-only' ); ?>
		<?php echo sprintf( __( '<a href="%s">Disable it in WooCommerce Settings</a> to get plugin working correctly.', 'coupon-first-buy-only' ), esc_url( self_admin_url( 'admin.php?page=wc-settings&tab=account' ) ) ); ?>
    </p>
</div>