<?php
/**
 * Price Formatter
 * 
 * Chuẩn hoá hiển thị số và giá tiền.
 * 
 * NGUYÊN TẮC:
 * - Mọi hiển thị giá BẮT BUỘC phải dùng formatter này
 * - Round CHỈ 1 lần khi render
 * - Format theo chuẩn từng loại tiền tệ
 * 
 * @package ViethomeCao
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VHC_Price_Formatter {
    
    /**
     * Format số tiền theo currency
     * 
     * @param float  $amount   Số tiền cần format
     * @param string $currency Currency code (VND, USD, TWD)
     * @param bool   $with_symbol Có hiển thị ký hiệu tiền tệ không
     * @return string
     */
    public static function format_price( $amount, $is_unit_price = false, $currency = 'VND', $with_symbol = true ) {
        if ( ! is_numeric( $amount ) ) {
            return '';
        }
        $amount   = floatval( $amount );
        
        $currency = strtoupper( $currency );
        $config = self::get_currency_config( $currency );
        $decimals = $is_unit_price ? 2 : $config['decimals'];
        $amount = round( $amount, $decimals );
        $formatted = number_format(
            $amount,
            $config['decimals'],
            $config['decimal_separator'],
            $config['thousand_separator']
        );
        
        // Thêm ký hiệu tiền tệ nếu cần
        if ( $with_symbol ) {
            $symbol = $config['symbol'];
            
            if ( $config['symbol_position'] === 'before' ) {
                $formatted = $symbol . $formatted;
            } else {
                $formatted = $formatted . ' ' . $symbol;
            }
        }
        
        return $formatted;
    }

    /**
     * Render Block Giá Đa Tiền Tệ (Standard HTML Structure)
     * Dùng chung cho: Property Card, Single Property Page, Shortcode...
     * 
     * @param int $post_id ID của Bất động sản
     * @param array $args Các tham số tùy chọn (custom class, label override...)
     */
    public static function render_multi_currency_block( $post_id, $args = array() ) {
        if ( ! class_exists( 'VHC_Price_Calculator' ) ) {
            return '';
        }
        $price_data = VHC_Price_Calculator::calculate( $post_id );
        if ( ! $price_data ) {
            return '';
        }

        $label        = esc_html( get_post_meta( $post_id, 'property_label', true ) );
        $label_before = esc_html( get_post_meta( $post_id, 'property_label_before', true ) );

        ob_start();
        ?>
        <div class="property-price-block <?php echo isset($args['class']) ? esc_attr($args['class']) : ''; ?>">
            <div class="price-group" data-currency-group="VND">
                <div class="price-inner-group">
                    <div class="price-total">
                        <?php echo $label_before ? '<span class="price_label price_label_before">' . $label_before . '</span> ' : ''; ?>
                        <?php echo self::format_price( $price_data['total_price_vnd'], false, 'VND', true ); ?>
                        <?php echo $label ? '<span class="price_label">' . $label . '</span>' : ''; ?>
                    </div>
                    <?php if ( $price_data['unit_price_vnd'] > 0 ) : ?>
                        <div class="price-unit">
                            <?php echo self::format_price( $price_data['unit_price_vnd'], true, 'VND', true ); ?>/m²
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="price-group" data-currency-group="USD" style="display: none;">
                <div class="price-inner-group">
                    <div class="price-total">
                        <?php echo $label_before ? '<span class="price_label price_label_before">' . $label_before . '</span> ' : ''; ?>
                        <?php echo self::format_price( $price_data['total_price_usd'], false, 'USD', true ); ?>
                        <?php echo $label ? '<span class="price_label">' . $label . '</span>' : ''; ?>
                    </div>
                    <?php if ( $price_data['unit_price_usd'] > 0 ) : ?>
                        <div class="price-unit">
                            <?php echo self::format_price( $price_data['unit_price_usd'], true, 'USD', true ); ?>/m²
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="price-group" data-currency-group="TWD" style="display: none;">
                <div class="price-inner-group">
                    <div class="price-total">
                        <?php echo $label_before ? '<span class="price_label price_label_before">' . $label_before . '</span> ' : ''; ?>
                        <?php echo self::format_price( $price_data['total_price_twd'], false, 'TWD', true ); ?>
                        <?php echo $label ? '<span class="price_label">' . $label . '</span>' : ''; ?>
                    </div>
                    <?php if ( $price_data['unit_price_twd'] > 0 ) : ?>
                        <div class="price-unit">
                            <?php echo self::format_price( $price_data['unit_price_twd'], true, 'TWD', true ); ?>/m²
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <?php
        return ob_get_clean();
    }
    /**
     * Lấy config format cho từng currency
     */
    private static function get_currency_config( $currency ) {
        $configs = array(
            'VND' => array(
                'symbol'             => 'VND',
                'symbol_position'    => 'after',  
                'decimals'           => 0,         
                'decimal_separator'  => ',',
                'thousand_separator' => '.',
            ),
            'USD' => array(
                'symbol'             => 'USD',
                'symbol_position'    => 'after',
                'decimals'           => 2,
                'decimal_separator'  => '.',
                'thousand_separator' => ',',
            ),
            'TWD' => array(
                'symbol'             => 'TWD',
                'symbol_position'    => 'after',
                'decimals'           => 0,       
                'decimal_separator'  => '.',
                'thousand_separator' => ',',
            ),
        );
        
        $configs = apply_filters( 'vhc_currency_configs', $configs );
        
        return isset( $configs[ $currency ] ) ? $configs[ $currency ] : $configs['VND'];
    }
    
    /**
     * Format diện tích
     * 
     * @param float $area     Diện tích
     * @param bool  $with_unit Có hiển thị đơn vị m² không
     * @return string
     */
    public static function format_area( $area, $with_unit = true ) {
        if ( ! is_numeric( $area ) ) {
            return '';
        }
        
        $area = floatval( $area );
        
        // Format với 2 số thập phân (có thể điều chỉnh)
        $formatted = number_format( $area, 2, '.', ',' );
        
        if ( $with_unit ) {
            $formatted .= ' m²';
        }
        
        return $formatted;
    }
    
    /**
     * Format giá cho property (full output)
     * 
     * @param int    $post_id  Post ID
     * @param string $currency Currency code (VND, USD, TWD)
     * @param bool   $show_unit Hiển thị đơn giá không
     * @return string HTML output
     */
    public static function format_property_price( $post_id, $currency = 'VND', $show_unit = true ) {
        $data = VHC_Price_Calculator::calculate( $post_id );
        
        if ( ! $data ) {
            return '';
        }
        
        $currency = strtoupper( $currency );
        $currency_lower = strtolower( $currency );
        
        $total_price = $data[ 'total_price_' . $currency_lower ];
        $unit_price  = $data[ 'unit_price_' . $currency_lower ];
        
        $output = '<div class="property-price-wrapper" data-currency="' . esc_attr( $currency ) . '">';
        
        // Tổng giá
        $output .= '<div class="property-price-total">';
        $output .= '<span class="price-label">' . esc_html__( 'Tổng giá:', 'viethomecao' ) . '</span> ';
        $output .= '<span class="price-value">' . self::format_price( $total_price, $currency, true ) . '</span>';
        $output .= '</div>';
        
        // Đơn giá
        if ( $show_unit ) {
            $output .= '<div class="property-price-unit">';
            $output .= '<span class="price-label">' . esc_html__( 'Đơn giá:', 'viethomecao' ) . '</span> ';
            $output .= '<span class="price-value">' . self::format_price( $unit_price, $currency, true ) . '/m²</span>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Format giá ngắn gọn (chỉ tổng giá)
     * 
     * @param int    $post_id  Post ID
     * @param string $currency Currency code
     * @return string
     */
    public static function format_price_simple( $post_id, $currency = 'VND' ) {
        $currency = strtoupper( $currency );
        $price = VHC_Price_Calculator::get_price( $post_id, $currency, 'total' );
        
        if ( $price === false ) {
            return '';
        }
        
        return self::format_price( $price, $currency, true );
    }
    
    /**
     * Lấy tên đầy đủ của currency
     * 
     * @param string $currency Currency code
     * @return string
     */
    public static function get_currency_name( $currency ) {
        $names = array(
            'VND' => __( 'Việt Nam Đồng', 'viethomecao' ),
            'USD' => __( 'Đô la Mỹ', 'viethomecao' ),
            'TWD' => __( 'Đô la Đài Loan', 'viethomecao' ),
        );
        
        $currency = strtoupper( $currency );
        
        return isset( $names[ $currency ] ) ? $names[ $currency ] : $currency;
    }
    
    /**
     * Lấy symbol của currency
     * 
     * @param string $currency Currency code
     * @return string
     */
    public static function get_currency_symbol( $currency ) {
        $config = self::get_currency_config( strtoupper( $currency ) );
        return $config['symbol'];
    }
}
