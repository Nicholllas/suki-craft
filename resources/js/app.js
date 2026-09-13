

import Alpine from 'alpinejs';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;

const deliveryMarkerIcon = L.icon({
    iconAnchor: [12, 41],
    iconRetinaUrl: markerIcon2x,
    iconSize: [25, 41],
    iconUrl: markerIcon,
    popupAnchor: [1, -34],
    shadowSize: [41, 41],
    shadowUrl: markerShadow,
    tooltipAnchor: [16, -28],
});

Alpine.data('deliveryLocation', () => ({
    address: '',
    csrfToken: '',
    deliveryFee: 0,
    distanceKm: null,
    error: '',
    latitude: '',
    locating: false,
    longitude: '',
    map: null,
    marker: null,
    messages: {},
    quote: '',
    quoteLoading: false,
    quoteUrl: '',
    reverseUrl: '',
    searchLoading: false,
    searchQuery: '',
    searchResults: [],
    searchUrl: '',
    selectionRevision: 0,
    storeLatitude: 0,
    storeLongitude: 0,
    init() {
        this.address = this.$refs.address.value;
        this.csrfToken = this.$el.dataset.csrfToken;
        this.messages = JSON.parse(this.$el.dataset.messages);
        this.quoteUrl = this.$el.dataset.quoteUrl;
        this.reverseUrl = this.$el.dataset.reverseUrl;
        this.searchUrl = this.$el.dataset.searchUrl;
        this.storeLatitude = Number(this.$el.dataset.storeLatitude);
        this.storeLongitude = Number(this.$el.dataset.storeLongitude);

        this.$nextTick(() => {
            this.map = L.map(this.$refs.map).setView([this.storeLatitude, this.storeLongitude], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | Routing: <a href="https://openrouteservice.org/">openrouteservice.org</a>',
                maxZoom: 19,
            }).addTo(this.map);
            L.marker([this.storeLatitude, this.storeLongitude], { icon: deliveryMarkerIcon })
                .addTo(this.map)
                .bindPopup(this.messages.storeLocation);
            this.map.on('click', (event) => this.selectLocation(event.latlng.lat, event.latlng.lng, true));

            const initialLatitude = Number(this.$el.dataset.initialLatitude);
            const initialLongitude = Number(this.$el.dataset.initialLongitude);

            if (Number.isFinite(initialLatitude) && Number.isFinite(initialLongitude) && initialLatitude !== 0 && initialLongitude !== 0) {
                this.selectLocation(initialLatitude, initialLongitude, false);

                return;
            }

            this.useCurrentLocation();
        });
    },
    async apiRequest(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers,
            },
            ...options,
        });
        const payload = await response.text();
        const data = payload ? JSON.parse(payload) : {};

        if (!response.ok) {
            const message = data.errors?.delivery_location?.[0]
                ?? data.errors?.delivery_quote?.[0]
                ?? data.message
                ?? this.messages.serviceUnavailable;

            throw new Error(message);
        }

        return data;
    },
    async calculateQuote(revision) {
        this.quoteLoading = true;

        try {
            const data = await this.apiRequest(this.quoteUrl, {
                body: JSON.stringify({
                    latitude: this.latitude,
                    longitude: this.longitude,
                }),
                method: 'POST',
            });

            if (revision !== this.selectionRevision) {
                return;
            }

            this.quote = data.quote;
            this.deliveryFee = data.delivery_fee;
            this.distanceKm = data.distance_km;
            this.error = '';
            this.notifyQuoteUpdated({
                deliveryFee: data.delivery_fee,
                distanceKm: data.distance_km,
                quote: data.quote,
            });
        } catch (error) {
            if (revision !== this.selectionRevision) {
                return;
            }

            this.quote = '';
            this.deliveryFee = 0;
            this.distanceKm = null;
            this.error = error.message;
            this.notifyQuoteUpdated({ deliveryFee: 0, quote: '' });
        } finally {
            if (revision === this.selectionRevision) {
                this.quoteLoading = false;
            }
        }
    },
    placeMarker() {
        const coordinates = [Number(this.latitude), Number(this.longitude)];

        if (!this.marker) {
            this.marker = L.marker(coordinates, { draggable: true, icon: deliveryMarkerIcon }).addTo(this.map);
            this.marker.on('dragend', (event) => {
                const position = event.target.getLatLng();
                this.selectLocation(position.lat, position.lng, true);
            });
        } else {
            this.marker.setLatLng(coordinates);
        }

        this.map.setView(coordinates, Math.max(this.map.getZoom(), 15));
    },
    async reverseAddress(revision) {
        try {
            const data = await this.apiRequest(this.reverseUrl, {
                body: JSON.stringify({
                    latitude: this.latitude,
                    longitude: this.longitude,
                }),
                method: 'POST',
            });

            if (revision === this.selectionRevision && data.address) {
                this.address = data.address;
            }
        } catch (error) {
            if (revision === this.selectionRevision && !this.error) {
                this.error = this.messages.reverseFailed;
            }
        }
    },
    async search() {
        const query = this.searchQuery.trim();

        if (query.length < 3) {
            this.searchResults = [];

            return;
        }

        this.searchLoading = true;
        this.error = '';

        try {
            const data = await this.apiRequest(`${this.searchUrl}?query=${encodeURIComponent(query)}`);
            this.searchResults = data.locations;
        } catch (error) {
            this.error = error.message;
            this.searchResults = [];
        } finally {
            this.searchLoading = false;
        }
    },
    selectSearchResult(location) {
        this.address = location.address;
        this.searchQuery = location.address;
        this.searchResults = [];
        this.selectLocation(location.latitude, location.longitude, false);
    },
    notifyQuoteUpdated(detail) {
        window.dispatchEvent(new CustomEvent('delivery-quote-updated', { detail }));
    },
    selectLocation(latitude, longitude, shouldReverse) {
        this.selectionRevision += 1;
        const revision = this.selectionRevision;
        this.latitude = Number(latitude).toFixed(7);
        this.longitude = Number(longitude).toFixed(7);
        this.quote = '';
        this.deliveryFee = 0;
        this.distanceKm = null;
        this.error = '';
        this.placeMarker();
        this.notifyQuoteUpdated({ deliveryFee: 0, quote: '' });

        if (shouldReverse) {
            this.reverseAddress(revision);
        }

        this.calculateQuote(revision);
    },
    useCurrentLocation() {
        if (!navigator.geolocation) {
            this.error = this.messages.geolocationUnavailable;

            return;
        }

        this.locating = true;
        this.error = '';
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.locating = false;
                this.selectLocation(position.coords.latitude, position.coords.longitude, true);
            },
            () => {
                this.locating = false;
                this.error = this.messages.geolocationDenied;
            },
            { enableHighAccuracy: true, maximumAge: 60000, timeout: 10000 },
        );
    },
}));

Alpine.data('deliverySchedule', (slots, today, currentTime, sameDayPreparationHours, selectedDate, selectedSlot) => ({
    clockTimer: null,
    currentTime,
    sameDayPreparationHours,
    selectedDate,
    selectedSlot,
    slots,
    today,
    init() {
        this.clearUnavailableSlot();
        this.clockTimer = window.setInterval(() => this.refreshClock(), 60000);
    },
    destroy() {
        window.clearInterval(this.clockTimer);
    },
    addDay(date) {
        const [year, month, day] = date.split('-').map(Number);

        return new Date(Date.UTC(year, month - 1, day + 1)).toISOString().slice(0, 10);
    },
    isSlotAvailableToday(slot) {
        const [startHours, startMinutes] = slot.start_time.split(':').map(Number);
        const [currentHours, currentMinutes] = this.currentTime.split(':').map(Number);
        const cutoffMinutes = (startHours * 60) + startMinutes - (this.sameDayPreparationHours * 60);

        return (currentHours * 60) + currentMinutes < cutoffMinutes;
    },
    minimumDeliveryDate() {
        return Object.values(this.slots).some((slot) => this.isSlotAvailableToday(slot)) ? this.today : this.addDay(this.today);
    },
    isSlotAvailable(slot) {
        if (!this.selectedDate || this.selectedDate < this.minimumDeliveryDate()) {
            return false;
        }

        return this.selectedDate !== this.today || this.isSlotAvailableToday(slot);
    },
    refreshClock() {
        const formatter = new Intl.DateTimeFormat('en-GB', {
            hour: '2-digit',
            hourCycle: 'h23',
            minute: '2-digit',
            timeZone: 'Asia/Jakarta',
        });

        this.currentTime = formatter.format(new Date());
        this.clearUnavailableSlot();
    },
    clearUnavailableSlot() {
        if (this.selectedDate && this.selectedDate < this.minimumDeliveryDate()) {
            this.selectedDate = '';
            this.selectedSlot = '';

            return;
        }

        if (this.selectedSlot && !this.isSlotAvailable(this.slots[this.selectedSlot])) {
            this.selectedSlot = '';
        }
    },
}));

Alpine.data('checkoutForm', () => ({
    isSubmitting: false,
    quoteReady: false,
    quoteUpdatedListener: null,
    init() {
        this.quoteUpdatedListener = (event) => {
            this.quoteReady = Boolean(event.detail.quote);
        };
        window.addEventListener('delivery-quote-updated', this.quoteUpdatedListener);
    },
    destroy() {
        window.removeEventListener('delivery-quote-updated', this.quoteUpdatedListener);
    },
    submitCheckout(event) {
        if (this.isSubmitting || !this.quoteReady) {
            event.preventDefault();

            if (!this.quoteReady) {
                window.dispatchEvent(new CustomEvent('delivery-quote-required'));
            }

            return;
        }

        this.isSubmitting = true;
    },
}));

Alpine.data('checkoutSummary', (initial, initialDeliveryFee, validationUrl, csrfToken, messages) => ({
    code: initial.code ?? '',
    deliveryFee: initialDeliveryFee,
    discount: initial.discount_amount,
    error: initial.message ?? '',
    loading: false,
    promotionType: initial.promotion_type,
    promotionValue: initial.promotion_value,
    quoteUpdatedListener: null,
    showItems: window.innerWidth >= 1024,
    subtotal: initial.total_before_discount - initialDeliveryFee,
    total: initial.total,
    totalBeforeDiscount: initial.total_before_discount,
    init() {
        this.quoteUpdatedListener = (event) => {
            this.deliveryFee = Number(event.detail.deliveryFee);
            this.totalBeforeDiscount = this.subtotal + this.deliveryFee;
            this.total = this.totalBeforeDiscount - this.discount;
        };
        window.addEventListener('delivery-quote-updated', this.quoteUpdatedListener);
    },
    destroy() {
        window.removeEventListener('delivery-quote-updated', this.quoteUpdatedListener);
    },
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
        const deliveryQuote = document.getElementById('delivery-quote').value;

        if (!deliveryQuote) {
            this.error = messages.locationRequired;

            return;
        }

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
                    delivery_latitude: document.getElementById('delivery-latitude').value,
                    delivery_longitude: document.getElementById('delivery-longitude').value,
                    delivery_quote: deliveryQuote,
                }),
            });
            const payload = await response.text();
            const data = payload ? JSON.parse(payload) : {};

            if (!response.ok) {
                this.resetPricing();
                this.error = data.errors?.promotion_code?.[0]
                    ?? data.errors?.delivery_quote?.[0]
                    ?? data.message
                    ?? messages.promoFailed;

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
            this.error = messages.promoNetworkFailed;
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

const copyFeedbackTimers = new WeakMap();

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-account]');

    if (!(button instanceof HTMLButtonElement) || button.disabled || !button.dataset.copyValue) {
        return;
    }

    try {
        await navigator.clipboard.writeText(button.dataset.copyValue);
    } catch {
        return;
    }

    const label = button.querySelector('[data-copy-account-label]');

    if (!label) {
        return;
    }

    window.clearTimeout(copyFeedbackTimers.get(button));
    label.textContent = button.dataset.copiedLabel;

    const timer = window.setTimeout(() => {
        label.textContent = button.dataset.copyLabel;
        copyFeedbackTimers.delete(button);
    }, 1800);

    copyFeedbackTimers.set(button, timer);
});

Alpine.start();
