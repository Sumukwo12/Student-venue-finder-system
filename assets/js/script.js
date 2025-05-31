document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all tab content
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Show the corresponding tab content
            const tabId = this.getAttribute('data-tab');
            document.getElementById(`${tabId}-content`).classList.add('active');
        });
    });
    
    // History tab switching
    const historyTabs = document.querySelectorAll('.history-tab');
    historyTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all history tabs
            historyTabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked history tab
            this.classList.add('active');
            
            // Hide all history content
            const historyContents = document.querySelectorAll('.history-content');
            historyContents.forEach(content => content.classList.remove('active'));
            
            // Show the corresponding history content
            const status = this.getAttribute('data-status');
            document.getElementById(`${status}-content`).classList.add('active');
        });
    });
    
    // Filter venues
    const filterBtn = document.getElementById('filter-btn');
    if (filterBtn) {
        filterBtn.addEventListener('click', filterVenues);
    }
    
    // Load booking history
    loadBookingHistory();
    
    // Modal handling
    const modals = document.querySelectorAll('.modal');
    const closeBtns = document.querySelectorAll('.close');
    
    closeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.style.display = 'none';
        });
    });
    
    window.addEventListener('click', function(event) {
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Booking form submission
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', submitBooking);
    }
    
    // Feedback form submission
    const feedbackForm = document.getElementById('feedback-form');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', submitFeedback);
    }
    
    // Skip feedback button
    const skipFeedbackBtn = document.getElementById('skip-feedback');
    if (skipFeedbackBtn) {
        skipFeedbackBtn.addEventListener('click', function() {
            document.getElementById('feedback-modal').style.display = 'none';
        });
    }
});

// Filter venues function
function filterVenues() {
    const day = document.getElementById('day').value;
    const time = document.getElementById('time').value;
    const capacity = document.getElementById('capacity').value;
    
    // Show loading state
    document.getElementById('filter-prompt').style.display = 'none';
    document.getElementById('venues-table').classList.add('hidden');
    document.getElementById('no-venues-message').classList.add('hidden');
    
    // Make AJAX request to filter venues
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/filter-venues.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status === 200) {
            const response = JSON.parse(this.responseText);
            
            if (response.success) {
                const venues = response.venues;
                
                if (venues.length > 0) {
                    // Populate venues table
                    const venuesList = document.getElementById('venues-list');
                    venuesList.innerHTML = '';
                    
                    venues.forEach(venue => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${venue.venue_name}</td>
                            <td>${venue.capacity}</td>
                            <td>
                                <button class="btn btn-sm book-btn" 
                                        data-id="${venue.id}" 
                                        data-name="${venue.venue_name}" 
                                        data-day="${day}" 
                                        data-time="${time}">
                                    Book
                                </button>
                            </td>
                        `;
                        venuesList.appendChild(row);
                    });
                    
                    // Add event listeners to book buttons
                    const bookBtns = document.querySelectorAll('.book-btn');
                    bookBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                            openBookingModal(
                                this.getAttribute('data-id'),
                                this.getAttribute('data-name'),
                                this.getAttribute('data-day'),
                                this.getAttribute('data-time')
                            );
                        });
                    });
                    
                    // Show venues table
                    document.getElementById('venues-table').classList.remove('hidden');
                } else {
                    // Show no venues message
                    document.getElementById('no-venues-message').classList.remove('hidden');
                }
            } else {
                alert('Error: ' + response.message);
            }
        } else {
            alert('Error: Could not fetch venues');
        }
    };
    
    xhr.send(`day=${encodeURIComponent(day)}&time=${encodeURIComponent(time)}&capacity=${encodeURIComponent(capacity)}`);
}

// Open booking modal
function openBookingModal(venueId, venueName, day, time) {
    // Set values in the booking form
    document.getElementById('venue-id').value = venueId;
    document.getElementById('venue-name').value = venueName;
    document.getElementById('booking-day').value = day;
    document.getElementById('booking-time').value = time;
    document.getElementById('booking-description').textContent = `Complete the form below to book ${venueName} for ${day}, ${time}.`;
    
    // Clear any previous errors
    document.getElementById('course-name-error').textContent = '';
    document.getElementById('course-code-error').textContent = '';
    document.getElementById('lecturer-error').textContent = '';
    
    // Show the booking modal
    document.getElementById('booking-modal').style.display = 'block';
}

// Submit booking
function submitBooking(event) {
    event.preventDefault();
    
    // Get form data
    const venueId = document.getElementById('venue-id').value;
    const venueName = document.getElementById('venue-name').value;
    const day = document.getElementById('booking-day').value;
    const timeSlot = document.getElementById('booking-time').value;
    const courseName = document.getElementById('course-name').value;
    const courseCode = document.getElementById('course-code').value;
    const lecturer = document.getElementById('lecturer').value;
    
    // Validate form
    let isValid = true;
    
    if (!courseName.trim()) {
        document.getElementById('course-name-error').textContent = 'Course name is required';
        isValid = false;
    }
    
    if (!courseCode.trim()) {
        document.getElementById('course-code-error').textContent = 'Course code is required';
        isValid = false;
    }
    
    if (!lecturer.trim()) {
        document.getElementById('lecturer-error').textContent = 'Lecturer name is required';
        isValid = false;
    }
    
    if (!isValid) {
        return;
    }
    
    // Make AJAX request to book venue
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/book-venue.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status === 200) {
            const response = JSON.parse(this.responseText);
            
            if (response.success) {
                // Close booking modal
                document.getElementById('booking-modal').style.display = 'none';
                
                // Set booking ID for feedback
                document.getElementById('booking-id').value = response.booking_id;
                
                // Show feedback modal
                document.getElementById('feedback-modal').style.display = 'block';
                
                // Reset booking form
                document.getElementById('booking-form').reset();
            } else {
                alert('Error: ' + response.message);
            }
        } else {
            alert('Error: Could not submit booking');
        }
    };
    
    xhr.send(`venue_id=${encodeURIComponent(venueId)}&venue_name=${encodeURIComponent(venueName)}&day=${encodeURIComponent(day)}&time_slot=${encodeURIComponent(timeSlot)}&course_name=${encodeURIComponent(courseName)}&course_code=${encodeURIComponent(courseCode)}&lecturer=${encodeURIComponent(lecturer)}`);
}

// Submit feedback
function submitFeedback(event) {
    event.preventDefault();
    
    // Get form data
    const bookingId = document.getElementById('booking-id').value;
    const ratingElements = document.getElementsByName('rating');
    let rating = '';
    
    for (let i = 0; i < ratingElements.length; i++) {
        if (ratingElements[i].checked) {
            rating = ratingElements[i].value;
            break;
        }
    }
    
    const comment = document.getElementById('comment').value;
    
    // Make AJAX request to submit feedback
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/submit-feedback.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status === 200) {
            const response = JSON.parse(this.responseText);
            
            if (response.success) {
                // Close feedback modal
                document.getElementById('feedback-modal').style.display = 'none';
                
                // Reset feedback form
                document.getElementById('feedback-form').reset();
                
                // Reload booking history
                loadBookingHistory();
            } else {
                alert('Error: ' + response.message);
            }
        } else {
            alert('Error: Could not submit feedback');
        }
    };
    
    xhr.send(`booking_id=${encodeURIComponent(bookingId)}&rating=${encodeURIComponent(rating)}&comment=${encodeURIComponent(comment)}`);
}

// Load booking history
function loadBookingHistory() {
    // Make AJAX request to get booking history
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/get-bookings.php', true);
    
    xhr.onload = function() {
        if (this.status === 200) {
            const response = JSON.parse(this.responseText);
            
            if (response.success) {
                const bookings = response.bookings;
                
                // Group bookings by status
                const pendingBookings = bookings.filter(booking => booking.status === 'pending');
                const approvedBookings = bookings.filter(booking => booking.status === 'approved');
                const rejectedBookings = bookings.filter(booking => booking.status === 'rejected');
                
                // Populate pending bookings
                populateBookings('pending-bookings', pendingBookings);
                
                // Populate approved bookings
                populateBookings('approved-bookings', approvedBookings);
                
                // Populate rejected bookings
                populateBookings('rejected-bookings', rejectedBookings);
            } else {
                alert('Error: ' + response.message);
            }
        }
    };
    
    xhr.send();
}

// Populate bookings table
function populateBookings(tableId, bookings) {
    const table = document.getElementById(tableId);
    table.innerHTML = '';
    
    if (bookings.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="5" class="text-center">No bookings found</td>';
        table.appendChild(row);
        return;
    }
    
    bookings.forEach(booking => {
        const row = document.createElement('tr');
        
        let statusClass = '';
        if (booking.status === 'pending') {
            statusClass = 'status-pending';
        } else if (booking.status === 'approved') {
            statusClass = 'status-approved';
        } else if (booking.status === 'rejected') {
            statusClass = 'status-rejected';
        }
        
        row.innerHTML = `
            <td>${booking.venue_name}</td>
            <td>${booking.day}</td>
            <td>${booking.time_slot}</td>
            <td>${booking.course_name} (${booking.course_code})</td>
            <td><span class="status ${statusClass}">${booking.status}</span></td>
        `;
        
        table.appendChild(row);
    });
}