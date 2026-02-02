<?php
/**
 * Template: Property Price Block
 * 
 * Hiển thị BLOCK GIÁ đầy đủ cho property.
 * 
 * NGUYÊN TẮC:
 * - Backend render SẴN tất cả currency (VND / USD / TWD)
 * - Đưa toàn bộ dữ liệu vào data-attributes
 * - JS CHỈ switch hiển thị, KHÔNG tính toán
 * 
 * @package ViethomeCao
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Lấy dữ liệu giá
$price_data = VHC_Price_Calculator::calculate( $post_id );

if ( ! $price_data ) {
    echo '<div class="property-price-block-empty">' . esc_html__( 'Liên hệ để biết giá', 'viethomecao' ) . '</div>';
    return;
}

// Default currency từ localStorage hoặc VND
$default_currency = isset( $default_currency ) ? $default_currency : 'VND';
$show_switcher    = isset( $show_switcher ) ? $show_switcher : true;
?>

<div class="property-price-block" 
     data-default-currency="<?php echo esc_attr( $default_currency ); ?>"
     data-price-vnd-total="<?php echo esc_attr( $price_data['total_price_vnd'] ); ?>"
     data-price-vnd-unit="<?php echo esc_attr( $price_data['unit_price_vnd'] ); ?>"
     data-price-usd-total="<?php echo esc_attr( $price_data['total_price_usd'] ); ?>"
     data-price-usd-unit="<?php echo esc_attr( $price_data['unit_price_usd'] ); ?>"
     data-price-twd-total="<?php echo esc_attr( $price_data['total_price_twd'] ); ?>"
     data-price-twd-unit="<?php echo esc_attr( $price_data['unit_price_twd'] ); ?>"
     data-area="<?php echo esc_attr( $price_data['area_m2'] ); ?>">
    
    <?php if ( $show_switcher ) : ?>
    <!-- Currency Switcher -->
    <div class="currency-switcher">
        <button class="currency-btn" data-currency="VND">VND</button>
        <button class="currency-btn" data-currency="USD">USD</button>
        <button class="currency-btn" data-currency="TWD">TWD</button>
    </div>
    <?php endif; ?>
    
    <!-- Price Display Area -->
    <div class="price-display-area">
        
        <!-- VND Price -->
        <div class="price-group price-group-vnd" data-currency-group="VND">
            <div class="price-row price-total">
                <span class="price-label"><?php esc_html_e( 'Tổng giá:', 'viethomecao' ); ?></span>
                <span class="price-value" data-price-type="total">
                    <?php echo VHC_Price_Formatter::format_price( $price_data['total_price_vnd'], 'VND', true ); ?>
                </span>
            </div>
            <div class="price-row price-unit">
                <span class="price-label"><?php esc_html_e( 'Đơn giá:', 'viethomecao' ); ?></span>
                <span class="price-value" data-price-type="unit">
                    <?php echo VHC_Price_Formatter::format_price( $price_data['unit_price_vnd'], 'VND', true ); ?>/m²
                </span>
            </div>
        </div>
        
        <!-- USD Price -->
        <div class="price-group price-group-usd" data-currency-group="USD" style="display: none;">
            <div class="price-row price-total">
                <span class="price-label"><?php esc_html_e( 'Total Price:', 'viethomecao' ); ?></span>
                <span class="price-value" data-price-type="total">
                    <?php echo VHC_Price_Formatter::format_price( $price_data['total_price_usd'], 'USD', true ); ?>
                </span>
            </div>
            <div class="price-row price-unit">
                <span class="price-label"><?php esc_html_e( 'Unit Price:', 'viethomecao' ); ?></span>
                <span class="price-value" data-price-type="unit">
                    <?php echo VHC_Price_Formatter::format_price( $price_data['unit_price_usd'], 'USD', true ); ?>/m²
                </span>
            </div>
        </div>
        
        <!-- TWD Price -->
        <div class="price-group price-group-twd" data-currency-group="TWD" style="display: none;">
            <div class="price-row price-total">
                <span class="price-label"><?php esc_html_e( '總價:', 'viethomecao' ); ?></span>
                <span class="price-value" data-price-type="total">
                    <?php echo VHC_Price_Formatter::format_price( $price_data['total_price_twd'], 'TWD', true ); ?>
                </span>
            </div>
            <div class="price-row price-unit">
                <span class="price-label"><?php esc_html_e( '單價:', 'viethomecao' ); ?></span>
                <span class="price-value" data-price-type="unit">
                    <?php echo VHC_Price_Formatter::format_price( $price_data['unit_price_twd'], 'TWD', true ); ?>/m²
                </span>
            </div>
        </div>
        
    </div>
    
    <!-- Area Info -->
    <div class="property-area-info">
        <span class="area-label"><?php esc_html_e( 'Diện tích:', 'viethomecao' ); ?></span>
        <span class="area-value"><?php echo VHC_Price_Formatter::format_area( $price_data['area_m2'], true ); ?></span>
    </div>
    
    <?php if ( current_user_can( 'manage_options' ) ) : ?>
    <!-- Debug Info (chỉ admin thấy) -->
    <div class="price-debug-info" style="margin-top: 15px; padding: 10px; background: #f0f0f1; font-size: 11px; display: none;">
        <strong>Debug Info:</strong><br>
        - Rate USD: <?php echo number_format( $price_data['rate_usd'], 2, '.', ',' ); ?><br>
        - Rate TWD: <?php echo number_format( $price_data['rate_twd'], 2, '.', ',' ); ?><br>
        <button type="button" onclick="this.parentElement.style.display='none';" style="margin-top: 5px;">Đóng</button>
    </div>
    <?php endif; ?>
</div>
