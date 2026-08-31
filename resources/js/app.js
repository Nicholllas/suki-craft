

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;

Alpine.data('deliverySchedule', (slots, today, currentTime, sameDayPreparationHours, selectedDate, selectedSlot) => ({
    currentTime,
    sameDayPreparationHours,
    selectedDate,
    selectedSlot,
    slots,
    today,
    isSlotAvailable(slot) {
        if (this.selectedDate !== this.today) {
            return true;
        }

        const [startHours, startMinutes] = slot.start_time.split(':').map(Number);
        const [currentHours, currentMinutes] = this.currentTime.split(':').map(Number);
        const cutoffMinutes = (startHours * 60) + startMinutes - (this.sameDayPreparationHours * 60);

        return (currentHours * 60) + currentMinutes < cutoffMinutes;
    },
    clearUnavailableSlot() {
        if (this.selectedSlot && !this.isSlotAvailable(this.slots[this.selectedSlot])) {
            this.selectedSlot = '';
        }
    },
}));

Alpine.data('checkoutForm', () => ({
    isSubmitting: false,
    submitCheckout(event) {
        if (this.isSubmitting) {
            event.preventDefault();

            return;
        }

        this.isSubmitting = true;
    },
}));

Alpine.data('checkoutSummary', (initial, validationUrl, csrfToken) => ({
    code: initial.code ?? '',
    discount: initial.discount_amount,
    error: initial.message ?? '',
    loading: false,
    promotionType: initial.promotion_type,
    promotionValue: initial.promotion_value,
    showItems: window.innerWidth >= 1024,
    total: initial.total,
    totalBeforeDiscount: initial.total_before_discount,
    format(value) {
        return new Intl.NumberFormat('id-ID').format(value);
    },
    resetPricing() {
        this.discount = 0;
        this.promotionType = null;
        this.promotionValue = null;
        this.total = this.totalBeforeDiscount;
    },
    async applyPromotion() {
        this.loading = true;
        this.error = '';

        try {
            const response = await fetch(validationUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    code: this.code,
                    customer_phone: document.getElementById('customer-phone').value,
                }),
            });
            const payload = await response.text();
            const data = payload ? JSON.parse(payload) : {};

            if (!response.ok) {
                this.resetPricing();
                this.error = data.errors?.promotion_code?.[0] ?? data.message ?? 'Kode promo tidak dapat diterapkan. Silakan coba lagi.';

                return;
            }

            this.code = data.code;
            this.discount = data.discount_amount;
            this.promotionType = data.promotion_type;
            this.promotionValue = data.promotion_value;
            this.total = data.total;
            this.totalBeforeDiscount = data.total_before_discount;
        } catch (error) {
            this.resetPricing();
            this.error = 'Kode promo tidak dapat diterapkan. Muat ulang halaman lalu coba kembali.';
        } finally {
            this.loading = false;
        }
    },
}));

Alpine.data('productForm', (variants = [], recipes = [], bouquetSizes = [], initialErrors = {}) => ({
    alertIsOpen: false,
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
    initialErrors,
    recipes: recipes.map((recipe) => ({
        ingredient_id: '',
        product_variant_id: '',
        quantity_needed: 1,
        ratio_per_unit: 1,
        ...recipe,
        ratio_per_unit: recipe.ratio_per_unit ?? 1,
    })),
    section: 'info',
    validationErrors: {},
    variants,
    init() {
        this.validationErrors = Object.keys(this.initialErrors).reduce((errors, field) => ({ ...errors, [field]: true }), {});

        if (Object.keys(this.validationErrors).length > 0) {
            queueMicrotask(() => this.showValidationAlert());
        }
    },
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
        if (this.bouquetSizes.length > 0 && !window.confirm('Aturan ukuran yang ada akan diganti dengan template S–XXL. Lanjutkan?')) {
            return;
        }

        this.bouquetSizes = [
            { code: 'S', is_active: true, is_custom: false, label: 'Small', max_sheets: 6, min_sheets: 1, service_price: 55000 },
            { code: 'M', is_active: true, is_custom: false, label: 'Medium', max_sheets: 15, min_sheets: 7, service_price: 100000 },
            { code: 'L', is_active: true, is_custom: false, label: 'Large', max_sheets: 29, min_sheets: 16, service_price: 165000 },
            { code: 'XL', is_active: true, is_custom: false, label: 'Extra Large', max_sheets: 40, min_sheets: 30, service_price: 180000 },
            { code: 'XXL', is_active: true, is_custom: false, label: 'Extra Extra Large', max_sheets: 45, min_sheets: 41, service_price: 250000 },
        ];
    },
    clearFieldError(element) {
        if (!element.matches?.('input, select, textarea') || !element.name) {
            return;
        }

        const isFileInput = element instanceof HTMLInputElement && element.type === 'file';

        if ((!isFileInput && !element.checkValidity()) || (isFileInput && element.files?.length === 0)) {
            return;
        }

        const field = this.fieldKey(element.name);
        const validationErrors = { ...this.validationErrors };

        Object.keys(validationErrors).forEach((errorField) => {
            if (errorField === field || errorField.startsWith(`${field}.`)) {
                delete validationErrors[errorField];
            }
        });

        this.validationErrors = validationErrors;
    },
    errorSummary() {
        const form = this.form();
        const tabs = [
            ['info', 'Info dasar'],
            ['variants', 'Varian'],
            ['sizes', 'Ukuran buket'],
            ['ingredients', 'Resep / Bahan'],
            ['gallery', 'Galeri foto'],
        ];
        const groups = tabs.map(([id, label]) => ({
            fields: [...new Set(Object.keys(this.validationErrors)
                .filter((field) => this.tabForField(field) === id)
                .map((field) => this.fieldLabel(field, form)))],
            label,
        })).filter((tab) => tab.fields.length > 0);

        return `<p>Lengkapi field wajib berikut:</p><ul>${groups.map((tab) => `<li><strong>${this.escapeHtml(tab.label)}</strong>: ${tab.fields.map((field) => this.escapeHtml(field)).join(', ')}</li>`).join('')}</ul>`;
    },
    escapeHtml(value) {
        return value.replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[character]);
    },
    fieldKey(name) {
        return name.replace(/\[\]/g, '').replace(/\[([^\]]+)\]/g, '.$1').replace(/\.$/, '');
    },
    fieldLabel(field, form) {
        const element = [...(form?.querySelectorAll('[name]') ?? [])].find((input) => {
            const inputField = this.fieldKey(input.name);

            return inputField === field || field.startsWith(`${inputField}.`);
        });
        const label = element?.id ? form.querySelector(`label[for="${element.id}"]`) : element?.closest('div')?.querySelector('label');
        const fallbackLabels = {
            base_price: 'Harga dasar/jasa',
            category_id: 'Kategori',
            code: 'Kode ukuran',
            images: 'Foto produk',
            ingredient_id: 'Bahan',
            label: 'Label',
            max_sheets: 'Maks. qty',
            min_sheets: 'Min. qty',
            name: 'Nama produk',
            price_adjustment: 'Penyesuaian harga',
            quantity_needed: 'Qty per buket',
            ratio_per_unit: 'Rasio',
            service_price: 'Jasa',
        };

        return label?.textContent?.trim() || fallbackLabels[field.split('.').at(-1)] || 'Field wajib';
    },
    firstErrorTab() {
        return ['info', 'variants', 'sizes', 'ingredients', 'gallery'].find((tab) => this.hasTabError(tab)) ?? 'info';
    },
    focusFirstInvalidField() {
        this.$nextTick(() => {
            const form = this.form();
            const field = Object.keys(this.validationErrors)[0];
            const element = [...(form?.querySelectorAll('input, select, textarea') ?? [])].find((input) => {
                const inputField = this.fieldKey(input.name);

                return inputField === field || field.startsWith(`${inputField}.`);
            });

            element?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            element?.focus({ preventScroll: true });
        });
    },
    form() {
        return this.$root.closest('form');
    },
    hasTabError(tab) {
        return Object.keys(this.validationErrors).some((field) => this.tabForField(field) === tab);
    },
    isQuantityBasedRecipe(recipe) {
        const selectedVariant = this.variants.find((variant) => String(variant.id) === String(recipe.product_variant_id));

        return [true, 1, '1'].includes(selectedVariant?.is_quantity_based);
    },
    persistedVariants() {
        return this.variants.filter((variant) => variant.id);
    },
    showValidationAlert() {
        if (this.alertIsOpen || Object.keys(this.validationErrors).length === 0) {
            return;
        }

        this.alertIsOpen = true;
        this.section = this.firstErrorTab();

        Swal.fire({
            ...confirmationDefaults,
            confirmButtonText: 'Tampilkan field',
            focusCancel: false,
            focusConfirm: true,
            html: this.errorSummary(),
            showCancelButton: false,
            title: 'Lengkapi data wajib',
        }).then(() => {
            this.alertIsOpen = false;
            this.section = this.firstErrorTab();
            this.focusFirstInvalidField();
        });
    },
    tabForField(field) {
        const rootField = field.split('.')[0];

        if (rootField === 'variants') {
            return 'variants';
        }

        if (rootField === 'bouquet_sizes') {
            return 'sizes';
        }

        if (rootField === 'ingredients') {
            return 'ingredients';
        }

        if (['image_order', 'images', 'primary_image_id'].includes(rootField)) {
            return 'gallery';
        }

        return 'info';
    },
    validateBeforeSubmit(event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const invalidFields = [...form.querySelectorAll('input, select, textarea')]
            .filter((element) => element.name && element.willValidate && !element.checkValidity())
            .map((element) => this.fieldKey(element.name));

        if (invalidFields.length === 0) {
            return;
        }

        event.preventDefault();
        this.validationErrors = invalidFields.reduce((errors, field) => ({ ...errors, [field]: true }), { ...this.validationErrors });
        this.showValidationAlert();
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
