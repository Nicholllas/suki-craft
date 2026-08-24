

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

Alpine.data('productForm', (variants = [], recipes = [], bouquetSizes = []) => ({
    bouquetSizes: bouquetSizes.map((size) => ({
        code: '',
        is_active: true,
        is_custom: false,
        label: '',
        max_sheets: '',
        min_sheets: 1,
        service_price: 0,
        ...size,
        max_sheets: size.max_sheets ?? '',
        service_price: size.service_price ?? 0,
    })),
    recipes: recipes.map((recipe) => ({
        ingredient_id: '',
        product_variant_id: '',
        quantity_needed: 1,
        ratio_per_unit: 1,
        ...recipe,
        ratio_per_unit: recipe.ratio_per_unit ?? 1,
    })),
    section: 'info',
    variants,
    addBouquetSize() {
        this.bouquetSizes.push({ code: '', is_active: true, is_custom: false, label: '', max_sheets: '', min_sheets: 1, service_price: 0 });
    },
    addRecipe() {
        this.recipes.push({ ingredient_id: '', product_variant_id: '', quantity_needed: 1, ratio_per_unit: 1 });
    },
    addVariant() {
        this.variants.push({ label: '', price_adjustment: 0, is_active: true, is_quantity_based: false });
    },
    applyDefaultBouquetSizes() {
        if (this.bouquetSizes.length > 0 && !window.confirm('Aturan ukuran yang ada akan diganti dengan template S–XXL dan Custom. Lanjutkan?')) {
            return;
        }

        this.bouquetSizes = [
            { code: 'S', is_active: true, is_custom: false, label: 'Small', max_sheets: 6, min_sheets: 1, service_price: 55000 },
            { code: 'M', is_active: true, is_custom: false, label: 'Medium', max_sheets: 15, min_sheets: 7, service_price: 100000 },
            { code: 'L', is_active: true, is_custom: false, label: 'Large', max_sheets: 29, min_sheets: 16, service_price: 165000 },
            { code: 'XL', is_active: true, is_custom: false, label: 'Extra Large', max_sheets: 40, min_sheets: 30, service_price: 180000 },
            { code: 'XXL', is_active: true, is_custom: false, label: 'Extra Extra Large', max_sheets: 45, min_sheets: 41, service_price: 250000 },
            { code: 'CUSTOM', is_active: true, is_custom: true, label: 'Custom', max_sheets: '', min_sheets: 46, service_price: 0 },
        ];
    },
    isQuantityBasedRecipe(recipe) {
        const selectedVariant = this.variants.find((variant) => String(variant.id) === String(recipe.product_variant_id));

        return [true, 1, '1'].includes(selectedVariant?.is_quantity_based);
    },
    persistedVariants() {
        return this.variants.filter((variant) => variant.id);
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
