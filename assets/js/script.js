// assets/js/script.js
// Client-side interactions, form validations, and asynchronous slot scheduling.

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Enable Bootstrap Form Validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // 2. Asynchronous Time-Slot Checker for Patient Booking
    const appointmentDateInput = document.getElementById('appointment_date');
    const doctorSelect = document.getElementById('doctor_id');
    const slotsContainer = document.getElementById('slots-container');
    const slotsLoading = document.getElementById('slots-loading');
    const submitBtn = document.getElementById('submit-booking-btn');

    if (appointmentDateInput && doctorSelect && slotsContainer) {
        
        function fetchAvailableSlots() {
            const dateVal = appointmentDateInput.value;
            const doctorIdVal = doctorSelect.value;
            
            if (!dateVal || !doctorIdVal) {
                slotsContainer.innerHTML = '<p class="text-muted fs-8 text-center my-3"><i class="fa-solid fa-calendar me-1"></i> Please choose a doctor and select a date first.</p>';
                if (submitBtn) submitBtn.disabled = true;
                return;
            }

            // Show loading spinner
            if (slotsLoading) slotsLoading.classList.remove('d-none');
            slotsContainer.innerHTML = '';
            if (submitBtn) submitBtn.disabled = true;

            const url = `book-appointment.php?action=get_slots&doctor_id=${doctorIdVal}&date=${dateVal}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network error fetching slots.');
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading spinner
                    if (slotsLoading) slotsLoading.classList.add('d-none');

                    if (data.error) {
                        slotsContainer.innerHTML = `<div class="alert alert-warning py-2 fs-8 text-center my-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> ${data.error}</div>`;
                        return;
                    }

                    if (!data.slots || data.slots.length === 0) {
                        slotsContainer.innerHTML = '<div class="alert alert-danger py-2 fs-8 text-center my-2"><i class="fa-solid fa-circle-xmark me-1"></i> The doctor has no scheduled shifts on this day of the week. Please select another date.</div>';
                        return;
                    }

                    // Render slots grid
                    let html = '<div class="slots-grid">';
                    let activeSlotsCount = 0;
                    
                    data.slots.forEach((slot, index) => {
                        const uniqueId = `slot_${index}`;
                        const disabledAttr = slot.booked ? 'disabled' : '';
                        const bookedClass = slot.booked ? 'booked' : '';
                        
                        if (!slot.booked) {
                            activeSlotsCount++;
                        }

                        html += `
                            <div>
                                <input type="radio" name="appointment_time" value="${slot.value}" id="${uniqueId}" class="slot-radio ${bookedClass}" ${disabledAttr} required>
                                <label for="${uniqueId}" class="slot-label fs-9">${slot.display}</label>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    slotsContainer.innerHTML = html;

                    if (activeSlotsCount > 0) {
                        if (submitBtn) submitBtn.disabled = false;
                    } else {
                        slotsContainer.innerHTML += '<div class="alert alert-warning py-2 fs-8 text-center mt-2 mb-0"><i class="fa-solid fa-business-time me-1"></i> All slots for this day are fully booked. Please choose another date.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (slotsLoading) slotsLoading.classList.add('d-none');
                    slotsContainer.innerHTML = '<div class="alert alert-danger py-2 fs-8 text-center my-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Failed to load available time slots. Please try again.</div>';
                });
        }

        // Add event listeners for triggering slot updates
        appointmentDateInput.addEventListener('change', fetchAvailableSlots);
        doctorSelect.addEventListener('change', fetchAvailableSlots);
    }
});
