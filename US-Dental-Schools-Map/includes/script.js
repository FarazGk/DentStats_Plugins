jQuery(document).ready(function($) {
    const dots = $('.map-dot');
    const popup = $('#map-popup');

    dots.on('click', function() {
        const locationId = $(this).data('location-id');
        const dotPosition = $(this).offset();
        fetchLocationData(locationId, dotPosition);
    });

    function fetchLocationData(locationId, dotPosition) {
        $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'fetch_location_data',
                location_id: locationId
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (!data.error) {
                    displayPopup(data, dotPosition);
                } else {
                    alert(data.error);
                }
            }
        });
    }

    function displayPopup(data, dotPosition) {
        popup.html(`
            <div>
                <h3>${data.name}</h3>
                <p>City: ${data.city}</p>
                <p>State: ${data.state}</p>
                <p>Website: <a href="${data.website}">${data.website}</a></p>
                <p>Email: ${data.email}</p>
                <p>Phone Number: ${data.phone_number}</p>
                <p>Scores: ${data.scores}</p>
                <p>GPA Min: ${data.gpa_min}</p>
            </div>
        `);

        // Position the popup next to the dot
        const popupWidth = popup.outerWidth();
        const popupHeight = popup.outerHeight();
        const mapContainer = $('.map-container');

        let topPosition = dotPosition.top - mapContainer.offset().top - popupHeight - 10; // Adjust 10 pixels above the dot
        let leftPosition = dotPosition.left - mapContainer.offset().left + 10; // Adjust 10 pixels to the right of the dot

        // Adjust position if popup goes out of bounds
        if (topPosition < 0) {
            topPosition = dotPosition.top - mapContainer.offset().top + 40; // Show below the dot if above goes out of bounds
        }
        if (leftPosition + popupWidth > mapContainer.width()) {
            leftPosition = dotPosition.left - mapContainer.offset().left - popupWidth - 10; // Show left of the dot if out of right bounds
        }

        popup.css({
            display: 'block',
            top: topPosition + 'px',
            left: leftPosition + 'px'
        });
    }

    // Hide popup when clicking outside of it
    $(document).on('click', function(event) {
        if (!$(event.target).closest('.map-dot, #map-popup').length) {
            popup.hide();
        }
    });
});
