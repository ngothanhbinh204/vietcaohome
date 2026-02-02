<?php
/**
 * Price Hooks
 * 
 * Tích hợp với theme bằng hook/filter.
 * KHÔNG sửa template gốc của parent theme.
 * 
 * File này có thể chứa code theme-specific.
 * Điều chỉnh hook/filter tuỳ theo theme đang dùng.
 * 
 * @package ViethomeCao
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VHC_Price_Hooks {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Hook vào hiển thị giá trong listing
        add_filter( 'the_content', array( $this, 'add_price_to_content' ), 20 );
        
        // Hook vào widget giá
        // add_action( 'theme_property_price', array( $this, 'render_price_widget' ) );
        
        // Filter giá cho search results
        // add_filter( 'property_price_display', array( $this, 'filter_price_display' ), 10, 2 );
        
        // Hook vào loop property
        // add_action( 'property_loop_item_price', array( $this, 'render_loop_price' ) );
    }
    
    /**
     * Thêm giá vào content của single property
     * 
     * @param string $content Post content
     * @return string
     */
    public function add_price_to_content( $content ) {
        // Chỉ áp dụng cho single property post
        if ( ! is_singular( 'property' ) ) {
            return $content;
        }
        
        // Kiểm tra property có giá không
        if ( ! VHC_Price_Calculator::has_price() ) {
            return $content;
        }
        
        // Render price block
        $price_block = do_shortcode( '[property_price_block]' );
        
        // Thêm price block vào đầu content
        // Có thể điều chỉnh vị trí tuỳ theme
        $content = $price_block . $content;
        
        return $content;
    }
    
    /**
     * Render price widget
     * Hook này dùng khi theme có action riêng cho price
     */
    public function render_price_widget() {
        if ( ! VHC_Price_Calculator::has_price() ) {
            echo '<div class="property-price-contact">' . esc_html__( 'Liên hệ', 'viethomecao' ) . '</div>';
            return;
        }
        
        echo do_shortcode( '[property_price_block show_switcher="yes"]' );
    }
    
    /**
     * Render giá trong loop
     * Hook này dùng khi theme có action trong loop
     */
    public function render_loop_price() {
        if ( ! VHC_Price_Calculator::has_price() ) {
            echo '<div class="loop-price-empty">' . esc_html__( 'Liên hệ', 'viethomecao' ) . '</div>';
            return;
        }
        
        // Hiển thị giá ngắn gọn trong loop
        echo do_shortcode( '[property_price type="total" currency="VND" class="loop-price"]' );
    }
    
    /**
     * Filter price display
     * Dùng khi theme có filter riêng cho price
     * 
     * @param string $price_html HTML giá hiện tại
     * @param int    $post_id    Post ID
     * @return string
     */
    public function filter_price_display( $price_html, $post_id ) {
        if ( ! VHC_Price_Calculator::has_price( $post_id ) ) {
            return $price_html;
        }
        
        // Replace bằng price formatter của chúng ta
        return VHC_Price_Formatter::format_price_simple( $post_id, 'VND' );
    }
}

// Khởi tạo hooks
// COMMENT OUT nếu chưa cần tích hợp với theme
// Chỉ UNCOMMENT khi đã biết rõ hook/filter của theme
// new VHC_Price_Hooks();
