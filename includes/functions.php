<?php
// includes/functions.php
// Global helper functions for formatting, sanitizing, and time-slot generation.

/**
 * Sanitizes input text to prevent XSS.
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Standardizes date presentation.
 */
function format_date($date) {
    if (!$date) return 'N/A';
    return date('M d, Y', strtotime($date));
}

/**
 * Standardizes time presentation.
 */
function format_time($time) {
    if (!$time) return 'N/A';
    return date('h:i A', strtotime($time));
}

/**
 * Determines CSS badge styling depending on appointment status.
 */
function get_badge_class($status) {
    switch (trim($status)) {
        case 'Pending':
            return 'bg-warning text-dark';
        case 'Approved':
            return 'bg-success';
        case 'Rejected':
            return 'bg-danger';
        case 'Completed':
            return 'bg-info text-dark';
        case 'Cancelled':
            return 'bg-secondary';
        default:
            return 'bg-primary';
    }
}

/**
 * Generates an array of time slots based on start/end times and slot duration.
 * Flags slots that are already booked.
 * 
 * @param string $start Start time (e.g. '09:00:00')
 * @param string $end End time (e.g. '13:00:00')
 * @param int $duration_mins Duration of slot in minutes (e.g. 30)
 * @param array $booked_slots List of times already booked (e.g. ['09:30:00', '11:00:00'])
 * @return array Generated slots list with status details
 */
function generate_time_slots($start, $end, $duration_mins, $booked_slots = []) {
    $slots = [];
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    $duration_secs = $duration_mins * 60;
    
    // Convert booked slots to comparable format (H:i)
    $booked_times = array_map(function($time) {
        return date('H:i', strtotime($time));
    }, $booked_slots);
    
    for ($t = $start_time; $t < $end_time; $t += $duration_secs) {
        $slot_time = date('H:i:s', $t);
        $compare_time = date('H:i', $t);
        $display_time = date('h:i A', $t);
        
        $is_booked = in_array($compare_time, $booked_times);
        
        $slots[] = [
            'value' => $slot_time,
            'display' => $display_time,
            'booked' => $is_booked
        ];
    }
    
    return $slots;
}
?>
