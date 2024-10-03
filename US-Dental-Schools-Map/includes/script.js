jQuery(document).ready(function($) {
    const dots = $('.map-dot');
    const schoolItems = $('.school-item');
    const popup = $('#map-popup');
    let lastClickedElement = null;
    let lastClickedDot = null;

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

        // If it's a list item, highlight the corresponding dot
        if (isListItem) {
            const correspondingDot = $(`.map-dot[data-location-id='${locationId}']`);

            // Remove active styles from the previously selected dot
            if (lastClickedDot && lastClickedDot !== correspondingDot) {
                lastClickedDot.removeClass('clicked active');
            }

            // Add active styles to the corresponding dot
            correspondingDot.addClass('clicked active');
            lastClickedDot = correspondingDot;
        }

        // Fields for basic data
        const basicFields = ['id', 'name', 'city', 'state', 'website', 'email', 'phone_number'];

        fetchLocationData(locationId, basicFields, function(data) {
            displayPopup(data, elementPosition);
        });
    }

    // When a dot on the map is clicked
    dots.on('click', function() {
        handleElementClick($(this), false);
    });

    // When a school item from the list is clicked
    schoolItems.on('click', function() {
        handleElementClick($(this), true);
    });

    function fetchLocationData(locationId, fields, callback) {
        $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            dataType: 'json', // Ensure the response is parsed as JSON
            data: {
                action: 'fetch_location_data',
                location_id: locationId,
                'fields[]': fields,
                nonce: ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    callback(response.data);
                } else {
                    alert('Error fetching location data: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                alert('AJAX error: ' + error);
            }
        });
    }

    function fetchAdditionalLocationData(locationId) {
        // Fields for detailed data
        const detailedFields = [
            'name',
            'website',
            'email',
            'phone_number',
            'deadline',
            'letters_of_evaluation',
            'supplemental_app',
            'shadowing',
            'application_fee',
            'resident_tuition',
            'non-resident_tuition',
            'additional_fees',
            'class_size',
            'total_enrollment',
            'grading_system',
            'student_ranking',
            'dual_admission',
            'other_degrees_offered',
            'housing_offered',
            'students_per_chair',
            'state_resident_required',
            'prerequisite_courses_per_semester_hours'
        ];

        // Log the fields being sent for debugging
        console.log('Sending fields:', detailedFields);

        $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            dataType: 'json', // Ensure the response is parsed as JSON
            data: {
                action: 'fetch_location_data',
                location_id: locationId, 
                'fields[]': detailedFields, // Correctly send the array
                nonce: ajax_object.nonce // The nonce for security
            },
            success: function(response) {
                if (response.success) {
                    console.log('Data received:', response.data); // Log the response for debugging
                    displaySecondaryPopup(response.data);
                } else {
                    alert('Error fetching location data: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                alert('AJAX error: ' + error);
            }
        });
    }
    
    
    function displayPopup(data, elementPosition) {
        // Adjust to check for 'NULL' values
        popup.html(
            `<div>
                <h4>${data.name && data.name !== 'NULL' ? data.name : 'N/A'}</h4>
                <p><strong>City:</strong> ${data.city && data.city !== 'NULL' ? data.city : 'N/A'}</p>
                <p><strong>State:</strong> ${data.state && data.state !== 'NULL' ? data.state : 'N/A'}</p>
                <p><strong>Website:</strong> ${data.website && data.website !== 'NULL' ? '<a href="' + data.website + '" target="_blank">' + data.website + '</a>' : 'N/A'}</p>
                <p><strong>Email:</strong> ${data.email && data.email !== 'NULL' ? data.email : 'N/A'}</p>
                <p><strong>Phone Number:</strong> ${data.phone_number && data.phone_number !== 'NULL' ? data.phone_number : 'N/A'}</p>
                <button class="view-more-details" data-location-id="${data.id}">View More Details</button>
            </div>`
        );

        // Positioning code
        const popupWidth = popup.outerWidth();
        const popupHeight = popup.outerHeight();
        let topPosition, leftPosition;

        // Position the popup next to the element
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
    
    function displaySecondaryPopup(data) {
        const modal = $('#secondary-popup');
        const modalContent = $('#secondary-popup-content');
    
        // Start building the HTML content
        let contentHtml = `<div class="popup-content">`;
    
        // Display the title outside of the columns
        contentHtml += `<h4 class="popup-title">${data.name && data.name !== 'NULL' ? data.name : 'N/A'}</h4>`;
    
        // Container for the columns
        contentHtml += `<div class="popup-columns">`;
    
        // First Column
        contentHtml += `<div class="popup-column">`;
    
        // Fields for the first column
        if (data.website && data.website !== 'NULL') {
            contentHtml += `<p><strong>Website:</strong> <a href="${data.website}" target="_blank">${data.website}</a></p>`;
        }
    
        if (data.email && data.email !== 'NULL') {
            contentHtml += `<p><strong>Email:</strong> <a href="mailto:${data.email}">${data.email}</a></p>`;
        }
    
        if (data.phone_number && data.phone_number !== 'NULL') {
            contentHtml += `<p><strong>Phone Number:</strong> ${data.phone_number}</p>`;
        }
    
        if (data.deadline && data.deadline !== 'NULL') {
            contentHtml += `<p><strong>Deadline:</strong> ${data.deadline}</p>`;
        }
    
        if (data.supplemental_app && data.supplemental_app !== 'NULL') {
            contentHtml += `<p><strong>Supplemental App:</strong> ${data.supplemental_app}</p>`;
        }
    
        if (data.shadowing && data.shadowing !== 'NULL') {
            contentHtml += `<p><strong>Shadowing:</strong> ${data.shadowing}</p>`;
        }
    
        if (data.application_fee && data.application_fee !== 'NULL') {
            contentHtml += `<p><strong>Application Fee:</strong> ${data.application_fee}</p>`;
        }
    
        if (data.resident_tuition && data.resident_tuition !== 'NULL') {
            contentHtml += `<p><strong>Resident Tuition:</strong> ${data.resident_tuition}</p>`;
        }
    
        if (data['non-resident_tuition'] && data['non-resident_tuition'] !== 'NULL') {
            contentHtml += `<p><strong>Non-Resident Tuition:</strong> ${data['non-resident_tuition']}</p>`;
        }
    
        if (data.additional_fees && data.additional_fees !== 'NULL') {
            contentHtml += `<p><strong>Additional Fees:</strong> ${data.additional_fees}</p>`;
        }
    
        if (data.class_size && data.class_size !== 'NULL') {
            contentHtml += `<p><strong>Class Size:</strong> ${data.class_size}</p>`;
        }
    
        if (data.total_enrollment && data.total_enrollment !== 'NULL') {
            contentHtml += `<p><strong>Total Enrollment:</strong> ${data.total_enrollment}</p>`;
        }
    
        if (data.grading_system && data.grading_system !== 'NULL') {
            contentHtml += `<p><strong>Grading System:</strong> ${data.grading_system}</p>`;
        }
    
        if (data.student_ranking && data.student_ranking !== 'NULL') {
            contentHtml += `<p><strong>Student Ranking:</strong> ${data.student_ranking}</p>`;
        }
    
        if (data.dual_admission && data.dual_admission !== 'NULL') {
            contentHtml += `<p><strong>Dual Admission:</strong> ${data.dual_admission}</p>`;
        }
    
        if (data.other_degrees_offered && data.other_degrees_offered !== 'NULL') {
            contentHtml += `<p><strong>Other Degrees Offered:</strong> ${data.other_degrees_offered}</p>`;
        }
    
        if (data.housing_offered && data.housing_offered !== 'NULL') {
            contentHtml += `<p><strong>Housing Offered:</strong> ${data.housing_offered}</p>`;
        }
    
        if (data.students_per_chair && data.students_per_chair !== 'NULL') {
            contentHtml += `<p><strong>Students per Chair:</strong> ${data.students_per_chair}</p>`;
        }
    
        if (data.state_resident_required && data.state_resident_required !== 'NULL') {
            contentHtml += `<p><strong>State Resident Required:</strong> ${data.state_resident_required}</p>`;
        }
    
        // Close the first column div
        contentHtml += `</div>`;
    
        // Second Column
        contentHtml += `<div class="popup-column">`;
    
        // Fields for the second column with content on a new line
        if (data.letters_of_evaluation && data.letters_of_evaluation !== 'NULL') {
            contentHtml += `
                <div class="field-block">
                    <p><strong>Letters of Evaluation:</strong></p>
                    <p>${formatSemicolon(data.letters_of_evaluation)}</p>
                </div>`;
        }
    
        if (data.prerequisite_courses_per_semester_hours && data.prerequisite_courses_per_semester_hours !== 'NULL') {
            contentHtml += `
                <div class="field-block">
                    <p><strong>Prerequisite Courses per Semester Hours:</strong></p>
                    <p>${formatSemicolon(data.prerequisite_courses_per_semester_hours)}</p>
                </div>`;
        }
    
        // Close the second column div
        contentHtml += `</div>`;
    
        // Close the columns container div
        contentHtml += `</div>`;
    
        // Close the main content div
        contentHtml += `</div>`;
    
        // Set the content and display the modal
        modalContent.html(contentHtml);
        modal.addClass('show').fadeIn();
        }
        
        // Helper function to format prerequisite courses
        function formatSemicolon(courses) {
            // Check if the input is valid and not empty
            if (!courses || typeof courses !== 'string') {
                return '';
            }
        
            // Split the courses by the semicolon (;) delimiter
            let courseList = courses.split(';').map(course => course.trim());
        
            // Join each course into a new line with <br> for HTML output
            return courseList.filter(course => course !== '').join('<br>');
        }




    // Close button functionality
    $(document).on('click', '.close-button', function() {
        $('#secondary-popup').fadeOut();
    });

    // Close modal when clicking outside content
    $(window).on('click', function(event) {
        if ($(event.target).is('#secondary-popup')) {
            $('#secondary-popup').fadeOut();
        }
    });

    // Hide popup and reset element color when clicking outside of it
    $(document).on('click', function(event) {
        if (!$(event.target).closest('.map-dot, .school-item, #map-popup').length) {
            popup.hide();
            if (lastClickedElement) {
                lastClickedElement.removeClass('clicked active');
                lastClickedElement.find('.school-id').removeClass('active');
                lastClickedElement = null;
            }
            if (lastClickedDot) {
                lastClickedDot.removeClass('clicked active');
                lastClickedDot = null;
            }
        }
    });

    // Event delegation for dynamically added elements
    $(document).on('click', '.view-more-details', function() {
        const locationId = $(this).data('location-id');
        fetchAdditionalLocationData(locationId);
    });
});
