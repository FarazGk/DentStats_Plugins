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
        <svg class="map-dot" style="top: 44%; left: 38%;" data-location-id="11" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">11</text>
        </svg>
        <svg class="map-dot" style="top: 33%; left: 97%;" data-location-id="12" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">12</text>
        </svg>
        <svg class="map-dot" style="top: 43%; left: 90%;" data-location-id="13" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">13</text>
        </svg>
        <svg class="map-dot" style="top: 83%; left: 84%;" data-location-id="14" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">14</text>
        </svg>
        <svg class="map-dot" style="top: 87%; left: 87%;" data-location-id="15" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">15</text>
        </svg>
        <svg class="map-dot" style="top: 78.5%; left: 84.5%;" data-location-id="16" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">16</text>
        </svg>
        <svg class="map-dot" style="top: 65%; left: 83%;" data-location-id="17" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">17</text>
        </svg>
        <svg class="map-dot" style="top: 39%; left: 64%;" data-location-id="18" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">18</text>
        </svg>
        <svg class="map-dot" style="top: 39%; left: 69%;" data-location-id="19" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">19</text>
        </svg>
        <svg class="map-dot" style="top: 45%; left: 67%;" data-location-id="20" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">20</text>
        </svg>
        <svg class="map-dot" style="top: 37%; left: 70%;" data-location-id="21" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">21</text>
        </svg>
        <svg class="map-dot" style="top: 42%; left: 73%;" data-location-id="22" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">22</text>
        </svg>
        <svg class="map-dot" style="top: 49%; left: 77%;" data-location-id="23" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">23</text>
        </svg>
        <svg class="map-dot" style="top: 49%; left: 75%;" data-location-id="24" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">24</text>
        </svg>
        <svg class="map-dot" style="top: 79%; left: 67%;" data-location-id="25" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">25</text>
        </svg>
        <svg class="map-dot" style="top: 22%; left: 100%;" data-location-id="26" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">26</text>
        </svg>
        <svg class="map-dot" style="top: 42%; left: 92%;" data-location-id="27" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">27</text>
        </svg>
        <svg class="map-dot" style="top: 28%; left: 98%;" data-location-id="28" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">28</text>
        </svg>
        <svg class="map-dot" style="top: 30%; left: 96%;" data-location-id="29" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">29</text>
        </svg>
        <svg class="map-dot" style="top: 30%; left: 99%;" data-location-id="30" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">30</text>
        </svg>
        <svg class="map-dot" style="top: 32%; left: 78%;" data-location-id="31" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">31</text>
        </svg>
        <svg class="map-dot" style="top: 32%; left: 76%;" data-location-id="32" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">32</text>
        </svg>
        <svg class="map-dot" style="top: 29%; left: 60%;" data-location-id="33" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">33</text>
        </svg>
        <svg class="map-dot" style="top: 72%; left: 69%;" data-location-id="34" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">34</text>
        </svg>
        <svg class="map-dot" style="top: 54%; left: 59%;" data-location-id="35" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">35</text>
        </svg>
        <svg class="map-dot" style="top: 49%; left: 59.5%;" data-location-id="36" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">36</text>
        </svg>
        <svg class="map-dot" style="top: 44%; left: 63%;" data-location-id="37" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">37</text>
        </svg>
        <svg class="map-dot" style="top: 39%; left: 55%;" data-location-id="38" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">38</text>
        </svg>
        <svg class="map-dot" style="top: 42%; left: 54%;" data-location-id="39" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">39</text>
        </svg>
        <svg class="map-dot" style="top: 50%; left: 18%;" data-location-id="40" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">40</text>
        </svg>
        <svg class="map-dot" style="top: 35.5%; left: 94.5%;" data-location-id="41" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">41</text>
        </svg>
        <svg class="map-dot" style="top: 33%; left: 95%;" data-location-id="42" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">42</text>
        </svg>
        <svg class="map-dot" style="top: 33.5%; left: 93%;" data-location-id="43" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">43</text>
        </svg>
        <svg class="map-dot" style="top: 30.5%; left: 87%;" data-location-id="44" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">44</text>
        </svg>
        <svg class="map-dot" style="top: 35%; left: 98%;" data-location-id="45" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">45</text>
        </svg>
        <svg class="map-dot" style="top: 31%; left: 93.7%;" data-location-id="46" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">46</text>
        </svg>
        <svg class="map-dot" style="top: 53%; left: 93%;" data-location-id="47" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">47</text>
        </svg>
        <svg class="map-dot" style="top: 56%; left: 84%;" data-location-id="48" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">48</text>
        </svg>
        <svg class="map-dot" style="top: 54%; left: 90%;" data-location-id="49" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">49</text>
        </svg>
        <svg class="map-dot" style="top: 39%; left: 80.5%;" data-location-id="50" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">50</text>
        </svg>
        <svg class="map-dot" style="top: 38%; left: 78.5%;" data-location-id="51" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">51</text>
        </svg>
        <svg class="map-dot" style="top: 58%; left: 54%;" data-location-id="52" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">52</text>
        </svg>
        <svg class="map-dot" style="top: 65%; left: 83%;" data-location-id="53" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">53</text>
        </svg>
        <svg class="map-dot" style="top: 38%; left: 89%;" data-location-id="54" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">54</text>
        </svg>
        <svg class="map-dot" style="top: 36%; left: 92%;" data-location-id="55" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">55</text>
        </svg>
        <svg class="map-dot" style="top: 39%; left: 84%;" data-location-id="56" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">56</text>
        </svg>
        <!--<svg class="map-dot" style="top: 21%; left: 10%;" data-location-id="57" viewBox="0 0 100 100">-->
        <!--    <circle cx="50" cy="50" r="50" />-->
        <!--    <text x="50" y="50" dy=".3em">57</text>-->
        <!--</svg>-->
        <svg class="map-dot" style="top: 63%; left: 88%;" data-location-id="58" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">58</text>
        </svg>
        <svg class="map-dot" style="top: 57%; left: 74%;" data-location-id="59" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">59</text>
        </svg>   
        <svg class="map-dot" style="top: 55%; left: 76%;" data-location-id="60" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">60</text>
        </svg>   
        <svg class="map-dot" style="top: 60%; left: 68%;" data-location-id="61" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">61</text>
        </svg>
        <svg class="map-dot" style="top: 71%; left: 53%;" data-location-id="62" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">62</text>
        </svg>
        <svg class="map-dot" style="top: 79%; left: 57%;" data-location-id="63" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">63</text>
        </svg>
        <svg class="map-dot" style="top: 83%; left: 50%;" data-location-id="64" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">64</text>
        </svg>
        <svg class="map-dot" style="top: 71%; left: 35%;" data-location-id="65" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">65</text>
        </svg>
        <svg class="map-dot" style="top: 39%; left: 27%;" data-location-id="66" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">66</text>
        </svg>
        <svg class="map-dot" style="top: 39.5%; left: 29%;" data-location-id="67" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">67</text>
        </svg>
        <svg class="map-dot" style="top: 49%; left: 92%;" data-location-id="68" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">68</text>
        </svg>
        <svg class="map-dot" style="top: 11%; left: 15%;" data-location-id="69" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">69</text>
        </svg>
        <svg class="map-dot" style="top: 44%; left: 85%;" data-location-id="70" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">70</text>
        </svg>
        <svg class="map-dot" style="top: 32%; left: 69%;" data-location-id="71" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">71</text>
        </svg>
        <svg class="map-dot" style="top: 39%; left: 82%;" data-location-id="72" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">72</text>
        </svg>
        <svg class="map-dot" style="top: 14%; left: 16%;" data-location-id="73" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" />
            <text x="50" y="50" dy=".3em">73</text>
        </svg>
        <div class="popup" id="map-popup"></div>
    </div>

    <div class="school-list">
        <?php foreach ($schools as $school): ?>
            <div class="school-item" data-location-id="<?php echo $school['id']; ?>">
                <span class="school-id"><?php echo $school['id']; ?>.</span>
                <span class="school-name"><?php echo $school['short_name']; ?></span>
            </div>
            
            <!-- Modal for Secondary Pop-up -->
            <div class="modal" id="secondary-popup" style="display:none;">
                <div class="modal-content">
                    <span class="close-button">&times;</span>
                    <div id="secondary-popup-content"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('us_dental_schools_map', 'us_dental_schools_shortcode');
