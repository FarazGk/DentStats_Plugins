<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function us_dental_schools_shortcode() {
    global $wpdb;

    // Fetch all IDs and short names
    $table_name = 'j1rn_us_dental_schools_data';
    $schools = $wpdb->get_results("SELECT id, short_name FROM $table_name", ARRAY_A);

    ob_start();
    ?>
    <div class="map-container">
        <img src="https://dentstats.com/wp-content/uploads/2024/07/US_Map.webp" alt="Dental School Map" class="map-image">
        <svg class="map-dot" style="top: 70%; left: 75%;" data-location-id="1" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">1</text>
        </svg>
        <svg class="map-dot" style="top: 62%; left: 25%;" data-location-id="2" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">2</text>
        </svg>
        <svg class="map-dot" style="top: 62%; left: 27%;" data-location-id="3" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">3</text>
        </svg>
        <svg class="map-dot" style="top: 42%; left: 11%;" data-location-id="4" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">4</text>
        </svg>
        <svg class="map-dot" style="top: 56%; left: 12.5%;" data-location-id="5" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">5</text>
        </svg>
        <svg class="map-dot" style="top: 54%; left: 11%;" data-location-id="6" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">6</text>
        </svg>
        <svg class="map-dot" style="top: 44%; left: 9%;" data-location-id="7" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">7</text>
        </svg>
        <svg class="map-dot" style="top: 40%; left: 9%;" data-location-id="8" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">8</text>
        </svg>
        <svg class="map-dot" style="top: 52%; left: 12.5%;" data-location-id="9" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">9</text>
        </svg>
        <svg class="map-dot" style="top: 54%; left: 14%;" data-location-id="10" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">10</text>
        </svg>
        <svg class="map-dot" style="top: 21%; left: 10%;" data-location-id="53" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">53</text>
        </svg>
        <svg class="map-dot" style="top: 11%; left: 16%;" data-location-id="69" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">69</text>
        </svg>
        <div class="popup" id="map-popup"></div>
    </div>

    <div class="school-list">
        <?php foreach ($schools as $school): ?>
            <div class="school-item" data-location-id="<?php echo $school['id']; ?>">
                <span class="school-id"><?php echo $school['id']; ?>.</span>
                <span class="school-name"><?php echo $school['short_name']; ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('us_dental_schools_map', 'us_dental_schools_shortcode');
