

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;

Alpine.data('deliverySchedule', (slots, today, currentTime, selectedDate, selectedSlot) => ({
    currentTime,
    selectedDate,
    selectedSlot,
    slots,
    today,
    isSlotAvailable(slot) {
        return this.selectedDate !== this.today || slot.end_time > this.currentTime;
    },
    clearUnavailableSlot() {
        if (this.selectedSlot && !this.isSlotAvailable(this.slots[this.selectedSlot])) {
            this.selectedSlot = '';
        }
    },
}));

const confirmationDefaults = {
    buttonsStyling: false,
    cancelButtonText: 'Batal',
    confirmButtonText: 'Ya, lanjutkan',
    customClass: {
        actions: 'suki-swal-actions',
        cancelButton: 'suki-swal-cancel',
        confirmButton: 'suki-swal-confirm',
        popup: 'suki-swal-popup',
        title: 'suki-swal-title',
    },
    focusCancel: true,
    icon: 'warning',
    reverseButtons: true,
    showCancelButton: true,
};

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
        return;
    }

    if (form.dataset.confirmed === 'true') {
        delete form.dataset.confirmed;

        return;
    }

    event.preventDefault();

    if (form.dataset.confirming === 'true') {
        return;
    }

    form.dataset.confirming = 'true';

    Swal.fire({
        ...confirmationDefaults,
        confirmButtonText: form.dataset.confirmButton || confirmationDefaults.confirmButtonText,
        icon: form.dataset.confirmIcon || confirmationDefaults.icon,
        text: form.dataset.confirm,
        title: form.dataset.confirmTitle || 'Anda yakin?',
    }).then((result) => {
        delete form.dataset.confirming;

        if (result.isConfirmed) {
            form.dataset.confirmed = 'true';
            form.requestSubmit();
        }
    });
});

Alpine.start();
