<?php
/**
 * Price Calculator
 * 
 * Tính toán GIÁ BẤT ĐỘNG SẢN cho đa tiền tệ (VND / USD / TWD).
 * 
 * NGUYÊN TẮC:
 * - Giá gốc duy nhất là VND
 * - USD/TWD tính runtime, KHÔNG lưu DB
 * - Không làm tròn sớm
 * - Tổng giá và đơn giá tính ĐỘC LẬP
 * 
 * @package ViethomeCao
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VHC_Price_Calculator {
    
    /**
     * Tính toán đầy đủ giá cho property
     * 
     * @param int|WP_Post $post_id Post ID hoặc WP_Post object
     * @return array|false Trả về array dữ liệu hoặc false nếu thiếu data
     */
    public static function calculate( $post_id = null ) {
        if ( is_null( $post_id ) ) {
            $post_id = get_the_ID();
        } elseif ( is_object( $post_id ) && isset( $post_id->ID ) ) {
            $post_id = $post_id->ID;
        }
        
        if ( ! $post_id ) {
            return false;
        }
        
        $total_price_vnd = self::get_total_price_vnd( $post_id );
        $area_m2         = self::get_area( $post_id );
        
        if ( ! $total_price_vnd ) {
            return false;
        }
        if ( ! $area_m2 || $area_m2 <= 0 ) {
            $area_m2 = 0;
        }
        $rate_usd = VHC_Exchange_Rate_Options::get_rate( 'usd' );
        $rate_twd = VHC_Exchange_Rate_Options::get_rate( 'twd' );
        
        if ( ! $rate_usd || ! $rate_twd ) {
            return false;
        }
        
        $unit_price_vnd = ( $area_m2 > 0 ) ? ( $total_price_vnd / $area_m2 ) : 0;
        
        $total_price_usd = $total_price_vnd / $rate_usd;
        $unit_price_usd  = ( $area_m2 > 0 ) ? ( $unit_price_vnd / $rate_usd ) : 0;
        
        $total_price_twd = $total_price_vnd / $rate_twd;
        $unit_price_twd  = ( $area_m2 > 0 ) ? ( $unit_price_vnd / $rate_twd ) : 0;
        
        return array(
            'area_m2'         => floatval( $area_m2 ),
            'total_price_vnd' => floatval( $total_price_vnd ),
            'unit_price_vnd'  => floatval( $unit_price_vnd ),
            'total_price_usd' => floatval( $total_price_usd ),
            'unit_price_usd'  => floatval( $unit_price_usd ),
            'total_price_twd' => floatval( $total_price_twd ),
            'unit_price_twd'  => floatval( $unit_price_twd ),
            'rate_usd'        => floatval( $rate_usd ),
            'rate_twd'        => floatval( $rate_twd ),
        );
    }
    
    /**
     * Lấy tổng giá VND từ post meta
     * 
     * @param int $post_id Post ID
     * @return float|false
     */
    private static function get_total_price_vnd( $post_id ) {
        $meta_keys = apply_filters( 'vhc_price_meta_keys', array(
            'property_price',
            '_property_price',
            'price',
            '_price',
            'total_price',
            '_total_price',
            'property_total_price',
            '_property_total_price',
        ) );
        
        foreach ( $meta_keys as $key ) {
            $value = get_post_meta( $post_id, $key, true );
            
            if ( ! empty( $value ) && is_numeric( $value ) ) {
                return floatval( $value );
            }
        }
        
        return false;
    }
    
    /**
     * Lấy diện tích từ post meta
     * 
     * @param int $post_id Post ID
     * @return float|false
     */
    private static function get_area( $post_id ) {
        $meta_keys = apply_filters( 'vhc_area_meta_keys', array(
            'property_area',
            'property_lot_size',
            '_property_area',
            'area',
            '_area',
            'property_size',
            '_property_size',
            'land_area',
            '_land_area',
        ) );
        
        foreach ( $meta_keys as $key ) {
            $value = get_post_meta( $post_id, $key, true );
            
            if ( ! empty( $value ) && is_numeric( $value ) ) {
                return floatval( $value );
            }
        }
        
        return false;
    }
    
    /**
     * Lấy giá theo currency cụ thể
     * 
     * @param int    $post_id  Post ID
     * @param string $currency Currency code (vnd, usd, twd)
     * @param string $type     Price type (total, unit)
     * @return float|false
     */
    public static function get_price( $post_id, $currency = 'vnd', $type = 'total' ) {
        $data = self::calculate( $post_id );
        
        if ( ! $data ) {
            return false;
        }
        
        $currency = strtolower( $currency );
        $type     = strtolower( $type );
        
        $key = $type . '_price_' . $currency;
        
        return isset( $data[ $key ] ) ? $data[ $key ] : false;
    }
    
    /**
     * Kiểm tra property có giá hay không
     * 
     * @param int $post_id Post ID
     * @return bool
     */
    public static function has_price( $post_id = null ) {
        if ( is_null( $post_id ) ) {
            $post_id = get_the_ID();
        }
        
        $total_price = self::get_total_price_vnd( $post_id );
        
        return ! empty( $total_price );
    }
}