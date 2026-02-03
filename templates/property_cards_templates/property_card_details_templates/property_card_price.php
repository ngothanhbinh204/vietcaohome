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

    $price_label   = get_post_meta( $prop_id, 'property_label', true );
    $price_label   = $price_label ? '<span class="price_label">' . esc_html( $price_label ) . '</span>' : '';
    
    $label_before  = get_post_meta( $prop_id, 'property_label_before', true );
    $label_before  = $label_before ? '<span class="price_label price_label_before">' . esc_html( $label_before ) . '</span> ' : '';

    if ( $price_data ) : ?>
        <div class="property-price-block">
            <div class="price-group" data-currency-group="VND">
                <?php echo $label_before; ?>
                <?php echo VHC_Price_Formatter::format_price( $price_data['total_price_vnd'], 'VND', true ); ?>
                <?php echo $price_label; ?>
                <?php if ( $price_data['unit_price_vnd'] > 0 ) : ?>
                    <div class="price-unit" style="font-size: 0.9em; opacity: 0.8;">
                        <?php echo VHC_Price_Formatter::format_price( $price_data['unit_price_vnd'], 'VND', true ); ?>/m²
                    </div>
                <?php endif; ?>
            </div>
            <div class="price-group" data-currency-group="USD" style="display: none;">
                <?php echo $label_before; ?>
                <?php echo VHC_Price_Formatter::format_price( $price_data['total_price_usd'], 'USD', true ); ?>
                <?php echo $price_label; ?>
                <?php if ( $price_data['unit_price_usd'] > 0 ) : ?>
                    <div class="price-unit" style="font-size: 0.9em; opacity: 0.8;">
                        <?php echo VHC_Price_Formatter::format_price( $price_data['unit_price_usd'], 'USD', true ); ?>/m²
                    </div>
                <?php endif; ?>
            </div>
            <div class="price-group" data-currency-group="TWD" style="display: none;">
                <?php echo $label_before; ?>
                <?php echo VHC_Price_Formatter::format_price( $price_data['total_price_twd'], 'TWD', true ); ?>
                <?php echo $price_label; ?>
                <?php if ( $price_data['unit_price_twd'] > 0 ) : ?>
                    <div class="price-unit" style="font-size: 0.9em; opacity: 0.8;">
                        <?php echo VHC_Price_Formatter::format_price( $price_data['unit_price_twd'], 'TWD', true ); ?>/m²
                    </div>
                <?php endif; ?>
            </div>

        </div>

    <?php else : 
        $currency_symbol = wpresidence_get_option('wp_estate_currency_symbol', '');
        $currency_position = wpresidence_get_option('wp_estate_where_currency_symbol', '');
        
        if ( function_exists( 'wpestate_show_price_from_cache' ) ) {
            wpestate_show_price_from_cache($property_unit_cached_data, $currency_symbol, $currency_position);
        }
    endif; 
    ?>
</div>