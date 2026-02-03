<?php
/** MILLDONE
 * Property Card Price Template
 * src: templates\property_cards_templates\property_card_details_templates\property_card_price.php
 * This template is responsible for displaying the price of a property
 * on property cards in the WpResidence theme.
 *
 * @package WpResidence
 * @subpackage PropertyCard
 * @since 1.0
 */


/**
 * Retrieve currency symbol and position
 */
$currency_symbol = wpresidence_get_option('wp_estate_currency_symbol', '');
$currency_position = wpresidence_get_option('wp_estate_where_currency_symbol', '');

/**
 * Display the property price
 */
?>
<div class="listing_unit_price_wrapper">
    <?php
    $prop_id = 0;
    if ( isset( $property_unit_cached_data ) && is_array( $property_unit_cached_data ) ) {
        if ( isset( $property_unit_cached_data['ID'] ) ) {
            $prop_id = $property_unit_cached_data['ID'];
        }
    }
    
    if ( ! $prop_id ) {
        $prop_id = get_the_ID();
    }
    // var_dump($prop_id); 
    $class_exists = class_exists( 'VHC_Price_Calculator' );
    $price_data = $class_exists ? VHC_Price_Calculator::calculate( $prop_id ) : false;

    if ( $price_data ) {
        echo VHC_Price_Formatter::render_multi_currency_block( $prop_id );
    } else {
        $currency_symbol = wpresidence_get_option('wp_estate_currency_symbol', '');
        $currency_position = wpresidence_get_option('wp_estate_where_currency_symbol', '');
        
        if ( function_exists( 'wpestate_show_price_from_cache' ) ) {
            wpestate_show_price_from_cache($property_unit_cached_data, $currency_symbol, $currency_position);
        }
    }
    ?>
</div>