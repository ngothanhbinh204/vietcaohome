<?php
/**
 * Price Shortcodes
 * 
 * Tạo shortcode để hiển thị giá bất động sản.
 * 
 * CÁCH DÙNG:
 * [property_price]                              // Hiển thị đầy đủ với VND mặc định
 * [property_price currency="USD"]               // Hiển thị với USD
 * [property_price type="total" currency="TWD"]  // Chỉ hiển thị tổng giá TWD
 * [property_price type="unit"]                  // Chỉ hiển thị đơn giá
 * 
 * @package ViethomeCao
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VHC_Price_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode( 'property_price', array( $this, 'render_price' ) );
        add_shortcode( 'property_price_block', array( $this, 'render_price_block' ) );
    }
    
    /**
     * Render shortcode [property_price]
     * 
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_price( $atts ) {
        // Parse attributes
        $atts = shortcode_atts( array(
            'id'       => null,              // Post ID, null = current post
            'currency' => 'VND',             // VND, USD, TWD
            'type'     => 'full',            // full, total, unit
            'class'    => '',                // Custom CSS class
        ), $atts, 'property_price' );
        
        // Lấy post ID
        $post_id = $atts['id'] ? intval( $atts['id'] ) : get_the_ID();
        
        if ( ! $post_id ) {
            return '';
        }
        
        // Kiểm tra property có giá không
        if ( ! VHC_Price_Calculator::has_price( $post_id ) ) {
            return '<div class="property-price-empty">' . esc_html__( 'Liên hệ', 'viethomecao' ) . '</div>';
        }
        
        $currency = strtoupper( $atts['currency'] );
        $type     = strtolower( $atts['type'] );
        $class    = ! empty( $atts['class'] ) ? ' ' . esc_attr( $atts['class'] ) : '';
        
        // Lấy dữ liệu giá
        $data = VHC_Price_Calculator::calculate( $post_id );
        
        if ( ! $data ) {
            return '';
        }
        
        $currency_lower = strtolower( $currency );
        $total_price = $data[ 'total_price_' . $currency_lower ];
        $unit_price  = $data[ 'unit_price_' . $currency_lower ];
        
        $output = '<div class="property-price-shortcode' . $class . '" data-currency="' . esc_attr( $currency ) . '">';
        
        switch ( $type ) {
            case 'total':
                // Chỉ hiển thị tổng giá
                $output .= '<span class="price-value price-total">';
                $output .= VHC_Price_Formatter::format_price( $total_price, $currency, true );
                $output .= '</span>';
                break;
                
            case 'unit':
                // Chỉ hiển thị đơn giá
                $output .= '<span class="price-value price-unit">';
                $output .= VHC_Price_Formatter::format_price( $unit_price, $currency, true );
                $output .= '/m²</span>';
                break;
                
            case 'full':
            default:
                // Hiển thị đầy đủ
                $output .= '<div class="price-row price-total-row">';
                $output .= '<span class="price-label">' . esc_html__( 'Tổng giá:', 'viethomecao' ) . '</span> ';
                $output .= '<span class="price-value">' . VHC_Price_Formatter::format_price( $total_price, $currency, true ) . '</span>';
                $output .= '</div>';
                
                $output .= '<div class="price-row price-unit-row">';
                $output .= '<span class="price-label">' . esc_html__( 'Đơn giá:', 'viethomecao' ) . '</span> ';
                $output .= '<span class="price-value">' . VHC_Price_Formatter::format_price( $unit_price, $currency, true ) . '/m²</span>';
                $output .= '</div>';
                break;
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render shortcode [property_price_block]
     * Hiển thị block giá đầy đủ với switcher
     * 
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_price_block( $atts ) {
        // Parse attributes
        $atts = shortcode_atts( array(
            'id'              => null,    // Post ID
            'show_switcher'   => 'yes',   // Hiển thị currency switcher
            'default_currency' => 'VND',  // Currency mặc định
        ), $atts, 'property_price_block' );
        
        // Lấy post ID
        $post_id = $atts['id'] ? intval( $atts['id'] ) : get_the_ID();
        
        if ( ! $post_id ) {
            return '';
        }
        
        // Kiểm tra property có giá không
        if ( ! VHC_Price_Calculator::has_price( $post_id ) ) {
            return '<div class="property-price-block-empty">' . esc_html__( 'Liên hệ để biết giá', 'viethomecao' ) . '</div>';
        }
        
        // Load template
        ob_start();
        
        $template_path = get_stylesheet_directory() . '/template-parts/property/price-block.php';
        
        if ( file_exists( $template_path ) ) {
            // Set variables cho template
            $show_switcher   = ( $atts['show_switcher'] === 'yes' );
            $default_currency = strtoupper( $atts['default_currency'] );
            
            include $template_path;
        }
        
        return ob_get_clean();
    }
}

// Khởi tạo shortcode
new VHC_Price_Shortcode();
