<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


define( 'VHC_VERSION', '1.0.0' );
define( 'VHC_THEME_DIR', get_stylesheet_directory() );
define( 'VHC_THEME_URI', get_stylesheet_directory_uri() );
require_once VHC_THEME_DIR . '/inc/price/price-calculator.php';
require_once VHC_THEME_DIR . '/inc/price/price-formatter.php';
require_once VHC_THEME_DIR . '/inc/price/price-shortcode.php';
require_once VHC_THEME_DIR . '/inc/price/price-hooks.php';
require_once VHC_THEME_DIR . '/inc/admin/exchange-rate-options.php';

// if ( is_admin() ) {
//     require_once VHC_THEME_DIR . '/inc/admin/exchange-rate-options.php';
// }

if ( !function_exists( 'wpestate_chld_thm_cfg_parent_css' ) ):
    function wpestate_chld_thm_cfg_parent_css() {
        $parent_style = 'wpestate_style'; 
     
        
        $use_mimify     =   wpresidence_get_option('wp_estate_use_mimify','');
        $mimify_prefix  =   '';
        if($use_mimify==='yes'){
            $mimify_prefix  =   '.min';    
        }
        
        if($mimify_prefix===''){
            wp_enqueue_style($parent_style,get_template_directory_uri().'/style.css', '', '1.0', 'all');  
        }else{
            wp_enqueue_style($parent_style,get_template_directory_uri().'/style.min.css', '', '1.0', 'all');  
        }
        
        if ( is_rtl() ) {
           wp_enqueue_style( 'chld_thm_cfg_parent-rtl',  trailingslashit( get_template_directory_uri() ). '/rtl.css' );
    }
        wp_enqueue_style( 'wpestate-child-style',
            get_stylesheet_directory_uri() . '/style.css',
                array( $parent_style ),
                wp_get_theme()->get('Version')
        );
        
    }
endif;


add_action('after_setup_theme', function() {
    $domain = 'wpresidence';
    $locale = get_locale();

    // 1. Load parent theme translations from WP language directory
    load_theme_textdomain($domain, WP_LANG_DIR . '/themes');
    
    // 2. Load child theme translations
    load_child_theme_textdomain($domain, WP_LANG_DIR . '/themes');
    
    // 3. Fallback to child theme languages directory
    $child_mofile = get_stylesheet_directory() . "/languages/{$locale}.mo";
    if (file_exists($child_mofile)) {
        load_textdomain($domain, $child_mofile);
    }
    
    
});

add_action( 'wp_enqueue_scripts', 'wpestate_chld_thm_cfg_parent_css' );


// require_once VHC_THEME_DIR . '/inc/helpers/number-helper.php';

function vhc_enqueue_assets() {
    // CSS
    wp_enqueue_style( 
        'vhc-currency-switcher', 
        VHC_THEME_URI . '/assets/css/currency-switcher.css', 
        array(), 
        VHC_VERSION 
    );
    
    // JS
    wp_enqueue_script( 
        'vhc-currency-switcher', 
        VHC_THEME_URI . '/assets/js/currency-switcher.js', 
        array(), 
        VHC_VERSION, 
        true 
    );

    wp_localize_script( 'vhc-currency-switcher', 'vhcCurrency', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'vhc_currency_nonce' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'vhc_enqueue_assets' );


if(!function_exists('wpestate_all_prop_details_prop_unit')):
function wpestate_all_prop_details_prop_unit(){
    $single_details = array(

        'Image'         =>  'image',
        'Title'         =>  'title',
        'Description'   =>  'description',
        'Categories'    =>  'property_category',
        'Action'        =>  'property_action_category',
        'City'          =>  'property_city',
        'Neighborhood'  =>  'property_area',
        'County / State'=>  'property_county_state',
        'Address'       =>  'property_address',
        'Zip'           =>  'property_zip',
        'Country'       =>  'property_country',
        'Status'        =>  'property_status',
        'Price'         =>  'property_price',

        'Size'              =>  'property_size',
        'Lot Size'          =>  'property_lot_size',
        'Rooms'             =>  'property_rooms',
        'Bedrooms'          =>  'property_bedrooms',
        'Bathrooms'         =>  'property_bathrooms',
        'Agent'             =>  'property_agent',
        'Agent Picture'     =>  'property_agent_picture'

    );

    $custom_fields = wpresidence_get_option( 'wp_estate_custom_fields', '');
    if( !empty($custom_fields)){
        $i=0;
        while($i< count($custom_fields) ){
            $name =   $custom_fields[$i][0];
            $slug         =     wpestate_limit45(sanitize_title( $name ));
            $slug         =     sanitize_key($slug);
            $single_details[str_replace('-',' ',$name)]=     $slug;
            $i++;
       }
    }

    return $single_details;
}
endif;