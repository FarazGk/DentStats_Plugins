jQuery(document).ready(function($) {
    const dots = $('.map-dot');
    const schoolItems = $('.school-item');
    const popup = $('#map-popup');
    let lastClickedElement = null;

    function handleElementClick(element, isListItem) {
        const locationId = element.data('location-id');
        const elementPosition = element.offset();

        if (!locationId) {
            alert('Error: Location ID is not defined.');
            return;
        }

        // Remove 'clicked' and 'active' class from the last clicked element
        if (lastClickedElement && lastClickedElement !== element) {
            lastClickedElement.removeClass('clicked active');
            lastClickedElement.find('.school-id').removeClass('active');
        }

        // Add 'clicked' and 'active' class to the current clicked element and its school-id
        element.addClass('clicked active');
        element.find('.school-id').addClass('active');
        lastClickedElement = element;

        fetchLocationData(locationId, elementPosition, isListItem);
    }

    dots.on('click', function() {
        handleElementClick($(this), false);
    });

    schoolItems.on('click', function() {
        handleElementClick($(this), true);
    });

    function fetchLocationData(locationId, elementPosition, isListItem) {
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
                    displayPopup(data, elementPosition, isListItem);
                } else {
                    alert('Error fetching location data: ' + data.error + '\nLocation ID: ' + data.location_id + '\nQuery: ' + data.query + '\nDB Error: ' + data.db_error);
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX error: ' + error);
            }
        });
    }

    function displayPopup(data, elementPosition, isListItem) {
        popup.html(`
            <div>
                <h4>${data.name ? data.name : 'N/A'}</h4>
                <p>City: ${data.city ? data.city : 'N/A'}</p>
                <p>State: ${data.state ? data.state : 'N/A'}</p>
                <p>Website: ${data.website ? '<a href="' + data.website + '">' + data.website + '</a>' : 'N/A'}</p>
                <p>Email: ${data.email ? data.email : 'N/A'}</p>
                <p>Phone Number: ${data.phone_number ? data.phone_number : 'N/A'}</p>
            </div>
        `);

        const popupWidth = popup.outerWidth();
        const popupHeight = popup.outerHeight();
        let topPosition, leftPosition;

        // Position the popup next to the list item
        topPosition = elementPosition.top + 10; // Slight offset below the item
        leftPosition = elementPosition.left + 10; // Slight offset to the right of the item

        // Adjust position if popup goes out of bounds
        if (topPosition + popupHeight > $(window).height() + $(window).scrollTop()) {
            topPosition = elementPosition.top - popupHeight - 10; // Show above the item if below goes out of bounds
        }
        if (leftPosition + popupWidth > $(window).width() + $(window).scrollLeft()) {
            leftPosition = elementPosition.left - popupWidth - 10; // Show left of the item if out of right bounds
        }

        popup.css({
            display: 'block',
            top: topPosition + 'px',
            left: leftPosition + 'px'
        }).appendTo('body'); // Ensure the popup is appended to the body to ensure proper positioning
    }

    // Hide popup and reset element color when clicking outside of it
    $(document).on('click', function(event) {
        if (!$(event.target).closest('.map-dot, .school-item, #map-popup').length) {
            popup.hide();
            if (lastClickedElement) {
                lastClickedElement.removeClass('clicked active');
                lastClickedElement.find('.school-id').removeClass('active');
                lastClickedElement = null;
            }
        }
    });
});
