<?php
require_once 'includes/header.php';
requireLogin();

// Get days, time slots, and capacity ranges for filters
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$timeSlots = ['7:10-10:00', '10:00-13:00', '13:00-16:00', '16:00-18:45'];
$capacityRanges = ['50-100', '100-200', '200-300', '300-400', '400+'];
?>

<div class="tabs">
    <button class="tab active" data-tab="book">Book Venue</button>
    <button class="tab" data-tab="history">History</button>
</div>

<div class="tab-content active" id="book-content">
    <div class="card">
        <h2>Find Available Venues</h2>
        
        <div class="filter-form">
            <div class="filter-group">
                <label for="day">Day</label>
                <select id="day" name="day">
                    <option value="">Select day</option>
                    <?php foreach ($days as $day): ?>
                        <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="time">Time Slot</label>
                <select id="time" name="time">
                    <option value="">Select time</option>
                    <?php foreach ($timeSlots as $timeSlot): ?>
                        <option value="<?php echo $timeSlot; ?>"><?php echo $timeSlot; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="capacity">Capacity</label>
                <select id="capacity" name="capacity">
                    <option value="">Select capacity</option>
                    <?php foreach ($capacityRanges as $range): ?>
                        <option value="<?php echo $range; ?>"><?php echo $range; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button id="filter-btn" class="btn">Filter</button>
        </div>
        
        <div id="venues-table-container">
            <table id="venues-table" class="hidden">
                <thead>
                    <tr>
                        <th>Venue Name</th>
                        <th>Capacity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="venues-list">
                    <!-- Venues will be loaded here via JavaScript -->
                </tbody>
            </table>
            <p id="no-venues-message" class="hidden">No venues match your criteria. Please adjust your filters.</p>
            <p id="filter-prompt">Use the filters above to find available venues.</p>
        </div>
    </div>
</div>

<div class="tab-content" id="history-content">
    <div class="card">
        <h2>Booking History</h2>
        
        <div class="history-tabs">
            <button class="history-tab active" data-status="pending">Pending</button>
            <button class="history-tab" data-status="approved">Approved</button>
            <button class="history-tab" data-status="rejected">Rejected</button>
        </div>
        
        <div class="history-content active" id="pending-content">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Venue</th>
                        <th>Day</th>
                        <th>Time Slot</th>
                        <th>Course</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="pending-bookings">
                    <!-- Pending bookings will be loaded here via JavaScript -->
                </tbody>
            </table>
        </div>
        
        <div class="history-content" id="approved-content">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Venue</th>
                        <th>Day</th>
                        <th>Time Slot</th>
                        <th>Course</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="approved-bookings">
                    <!-- Approved bookings will be loaded here via JavaScript -->
                </tbody>
            </table>
        </div>
        
        <div class="history-content" id="rejected-content">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Venue</th>
                        <th>Day</th>
                        <th>Time Slot</th>
                        <th>Course</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="rejected-bookings">
                    <!-- Rejected bookings will be loaded here via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Booking Form Modal -->
<div id="booking-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Book Venue</h2>
        <p id="booking-description"></p>
        
        <form id="booking-form">
            <input type="hidden" id="venue-id" name="venue_id">
            
            <div class="form-group">
                <label for="venue-name">Venue</label>
                <input type="text" id="venue-name" name="venue_name" disabled>
            </div>
            
            <div class="form-group">
                <label for="booking-day">Day</label>
                <input type="text" id="booking-day" name="day" disabled>
            </div>
            
            <div class="form-group">
                <label for="booking-time">Time Slot</label>
                <input type="text" id="booking-time" name="time_slot" disabled>
            </div>
            
            <div class="form-group">
                <label for="course-name">Course Name</label>
                <input type="text" id="course-name" name="course_name" required>
                <span class="error-message" id="course-name-error"></span>
            </div>
            
            <div class="form-group">
                <label for="course-code">Course Code</label>
                <input type="text" id="course-code" name="course_code" required>
                <span class="error-message" id="course-code-error"></span>
            </div>
            
            <div class="form-group">
                <label for="lecturer">Lecturer</label>
                <input type="text" id="lecturer" name="lecturer" required>
                <span class="error-message" id="lecturer-error"></span>
            </div>
            
            <button type="submit" class="btn">Submit Booking</button>
        </form>
    </div>
</div>

<!-- Feedback Form Modal -->
<div id="feedback-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Feedback</h2>
        <p>Your booking has been submitted successfully. We would appreciate your feedback on the booking process.</p>
        
        <form id="feedback-form">
            <input type="hidden" id="booking-id" name="booking_id">
            
            <div class="form-group">
                <label>How would you rate your booking experience?</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="rating" value="excellent"> Excellent
                    </label>
                    <label>
                        <input type="radio" name="rating" value="good"> Good
                    </label>
                    <label>
                        <input type="radio" name="rating" value="average"> Average
                    </label>
                    <label>
                        <input type="radio" name="rating" value="poor"> Poor
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="comment">Additional Comments</label>
                <textarea id="comment" name="comment" placeholder="Please share any additional feedback or suggestions..."></textarea>
            </div>
            
            <div class="form-buttons">
                <button type="button" id="skip-feedback" class="btn btn-outline">Skip</button>
                <button type="submit" class="btn">Submit Feedback</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/script.js"></script>
<?php require_once 'includes/footer.php'; ?>