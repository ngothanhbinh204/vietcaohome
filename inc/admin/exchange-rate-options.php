<?php
/**
 * Exchange Rate Options Page
 * 
 * Tạo trang admin để quản lý tỷ giá quy đổi tiền tệ.
 * Lưu vào wp_options để dùng global cho toàn site.
 * 
 * @package ViethomeCao
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VHC_Exchange_Rate_Options {
    
    /**
     * Option key lưu tỷ giá
     */
    const OPTION_KEY = 'vhc_exchange_rates';
    
    /**
     * Menu slug
     */
    const MENU_SLUG = 'vhc-exchange-rates';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_options_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }
    
    /**
     * Thêm trang options vào admin menu
     */
    public function add_options_page() {
        add_options_page(
            __( 'Quản lý tỷ giá', 'viethomecao' ),
            __( 'Tỷ giá tiền tệ', 'viethomecao' ),
            'manage_options',
            self::MENU_SLUG,
            array( $this, 'render_options_page' )
        );
    }
    
    /**
     * Đăng ký settings
     */
    public function register_settings() {
        // Đăng ký option
        register_setting(
            'vhc_exchange_rate_group',
            self::OPTION_KEY,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_rates' ),
                'default'           => $this->get_default_rates(),
            )
        );
        
        // Thêm section
        add_settings_section(
            'vhc_exchange_rate_section',
            __( 'Thiết lập tỷ giá quy đổi', 'viethomecao' ),
            array( $this, 'render_section_description' ),
            self::MENU_SLUG
        );
        
        // Field USD
        add_settings_field(
            'rate_usd',
            __( '1 USD', 'viethomecao' ),
            array( $this, 'render_rate_field' ),
            self::MENU_SLUG,
            'vhc_exchange_rate_section',
            array(
                'currency' => 'USD',
                'label'    => __( 'VND', 'viethomecao' ),
            )
        );
        
        // Field TWD
        add_settings_field(
            'rate_twd',
            __( '1 TWD', 'viethomecao' ),
            array( $this, 'render_rate_field' ),
            self::MENU_SLUG,
            'vhc_exchange_rate_section',
            array(
                'currency' => 'TWD',
                'label'    => __( 'VND', 'viethomecao' ),
            )
        );
    }
    
    /**
     * Mô tả section
     */
    public function render_section_description() {
        echo '<p>' . __( 'Nhập tỷ giá quy đổi từ ngoại tệ sang VND. Tỷ giá này sẽ được áp dụng cho toàn bộ website.', 'viethomecao' ) . '</p>';
        echo '<p><strong>' . __( 'Lưu ý:', 'viethomecao' ) . '</strong> ' . __( 'Tỷ giá phải lớn hơn 0. Ví dụ: 1 USD = 25000 VND', 'viethomecao' ) . '</p>';
    }
    
    /**
     * Render field nhập tỷ giá
     */
    public function render_rate_field( $args ) {
        $rates    = $this->get_rates();
        $currency = strtolower( $args['currency'] );
        $value    = isset( $rates[ $currency ] ) ? $rates[ $currency ] : '';
        
        printf(
            '<input type="number" name="%s[%s]" value="%s" step="0.01" min="0.01" class="regular-text" required /> <span class="description">%s</span>',
            esc_attr( self::OPTION_KEY ),
            esc_attr( $currency ),
            esc_attr( $value ),
            esc_html( $args['label'] )
        );
        
        // Hiển thị giá trị hiện tại
        if ( ! empty( $value ) ) {
            echo '<p class="description">';
            printf(
                __( 'Hiện tại: 1 %s = %s VND', 'viethomecao' ),
                esc_html( $args['currency'] ),
                '<strong>' . number_format( $value, 2, '.', ',' ) . '</strong>'
            );
            echo '</p>';
        }
    }
    
    /**
     * Render trang options
     */
    public function render_options_page() {
        // Kiểm tra quyền
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Bạn không có quyền truy cập trang này.', 'viethomecao' ) );
        }
        ?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php settings_errors(); ?>

	<form method="post" action="options.php">
		<?php
                settings_fields( 'vhc_exchange_rate_group' );
                do_settings_sections( self::MENU_SLUG );
                submit_button( __( 'Lưu tỷ giá', 'viethomecao' ) );
                ?>
	</form>

	<hr>

	<div class="card">
		<h2><?php _e( 'Hướng dẫn sử dụng', 'viethomecao' ); ?></h2>
		<ol>
			<li><?php _e( 'Nhập tỷ giá quy đổi từ USD và TWD sang VND.', 'viethomecao' ); ?></li>
			<li><?php _e( 'Tỷ giá phải lớn hơn 0 và có thể nhập số thập phân.', 'viethomecao' ); ?></li>
			<li><?php _e( 'Sau khi lưu, hệ thống sẽ tự động áp dụng cho toàn bộ website.', 'viethomecao' ); ?></li>
			<li><?php _e( 'Giá gốc luôn là VND, USD và TWD được tính runtime từ tỷ giá này.', 'viethomecao' ); ?></li>
		</ol>

		<h3><?php _e( 'Công thức tính:', 'viethomecao' ); ?></h3>
		<p><code>Giá USD = Giá VND / Tỷ giá USD</code></p>
		<p><code>Giá TWD = Giá VND / Tỷ giá TWD</code></p>
	</div>
</div>

<style>
.card {
	max-width: 800px;
	margin-top: 20px;
}

.card ol {
	padding-left: 20px;
}

.card code {
	background: #f0f0f1;
	padding: 2px 6px;
	border-radius: 3px;
}
</style>
<?php
    }
    
    /**
     * Sanitize và validate dữ liệu tỷ giá
     * 
     * @param array $input Dữ liệu input từ form
     * @return array Dữ liệu đã sanitize
     */
    public function sanitize_rates( $input ) {
        $sanitized = array();
        $errors    = array();
        
        // Validate USD
        if ( isset( $input['usd'] ) ) {
            $usd = floatval( $input['usd'] );
            
            if ( $usd <= 0 ) {
                $errors[] = __( 'Tỷ giá USD phải lớn hơn 0.', 'viethomecao' );
            } else {
                $sanitized['usd'] = $usd;
            }
        } else {
            $errors[] = __( 'Vui lòng nhập tỷ giá USD.', 'viethomecao' );
        }
        
        // Validate TWD
        if ( isset( $input['twd'] ) ) {
            $twd = floatval( $input['twd'] );
            
            if ( $twd <= 0 ) {
                $errors[] = __( 'Tỷ giá TWD phải lớn hơn 0.', 'viethomecao' );
            } else {
                $sanitized['twd'] = $twd;
            }
        } else {
            $errors[] = __( 'Vui lòng nhập tỷ giá TWD.', 'viethomecao' );
        }
        
        // Hiển thị errors
        if ( ! empty( $errors ) ) {
            foreach ( $errors as $error ) {
                add_settings_error(
                    self::OPTION_KEY,
                    'vhc_exchange_rate_error',
                    $error,
                    'error'
                );
            }
            
            // Trả về giá trị cũ nếu có lỗi
            return $this->get_rates();
        }
        
        // Thông báo thành công
        add_settings_error(
            self::OPTION_KEY,
            'vhc_exchange_rate_success',
            __( 'Tỷ giá đã được cập nhật thành công!', 'viethomecao' ),
            'success'
        );
        
        return $sanitized;
    }
    
    /**
     * Lấy tỷ giá mặc định
     * 
     * @return array
     */
    private function get_default_rates() {
        return array(
            'usd' => 25000,  // 1 USD = 25,000 VND
            'twd' => 850,    // 1 TWD = 850 VND
        );
    }
    
    /**
     * Lấy tỷ giá hiện tại
     * 
     * @return array
     */
    public function get_rates() {
        $rates = get_option( self::OPTION_KEY, $this->get_default_rates() );
        
        // Đảm bảo có đầy đủ cả 2 loại tiền
        return wp_parse_args( $rates, $this->get_default_rates() );
    }
    
    /**
     * Lấy tỷ giá của 1 loại tiền cụ thể
     * 
     * @param string $currency Mã tiền tệ (usd, twd)
     * @return float|false
     */
    public static function get_rate( $currency ) {
        $currency = strtolower( $currency );
        $rates    = get_option( self::OPTION_KEY );
        
        if ( ! empty( $rates[ $currency ] ) && $rates[ $currency ] > 0 ) {
            return floatval( $rates[ $currency ] );
        }
        
        // Fallback về giá trị mặc định
        $defaults = array(
            'usd' => 25000,
            'twd' => 850,
        );
        
        return isset( $defaults[ $currency ] ) ? $defaults[ $currency ] : false;
    }
}

// Khởi tạo class
new VHC_Exchange_Rate_Options();