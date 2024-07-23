<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function us_dental_schools_shortcode() {
    ob_start();
    ?>
    <div class="map-container">
        <img src="https://dentstats.com/wp-content/uploads/2024/07/US_Map.webp" alt="Dental School Map" class="map-image">
        <div class="map-dot" style="top: 70%; left: 75%;" data-location-id="1">1</div>
        <div class="map-dot" style="top: 62%; left: 25%;" data-location-id="2">2</div>
        <div class="map-dot" style="top: 62%; left: 27%;" data-location-id="3">3</div>
        <div class="map-dot" style="top: 42%; left: 11%;" data-location-id="4">4</div>
        <div class="map-dot" style="top: 56%; left: 12.5%;" data-location-id="5">5</div>
        <div class="map-dot" style="top: 54%; left: 11%;" data-location-id="6">6</div>
        <div class="map-dot" style="top: 44%; left: 9%;" data-location-id="7">7</div>
        <div class="map-dot" style="top: 40%; left: 9%;" data-location-id="8">8</div>
        <div class="map-dot" style="top: 52%; left: 12.5%;" data-location-id="9">9</div>
        <div class="map-dot" style="top: 54%; left: 14%;" data-location-id="10">10</div>
        <div class="map-dot" style="top: 21%; left: 10%;" data-location-id="53">53</div>
        <div class="map-dot" style="top: 11%; left: 16%;" data-location-id="69">69</div>
        <div class="popup" id="map-popup"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('us_dental_schools_map', 'us_dental_schools_shortcode');
