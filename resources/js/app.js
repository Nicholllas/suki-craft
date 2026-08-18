

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;

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

Alpine.data('checkoutShipping', (config) => ({
    areaError: '',
    areaLoading: false,
    areaQuery: '',
    areas: [],
    code: config.promotionCode || '',
    discount: 0,
    error: '',
    loading: false,
    rates: [],
    ratesError: '',
    ratesFallback: false,
    ratesLoading: false,
    searchTimer: null,
    selectedArea: null,
    selectionLoading: false,
    shippingFee: Number(config.initialSelection?.delivery_fee ?? config.deliveryFee ?? 0),
    shippingOption: config.initialSelection || null,
    showItems: window.innerWidth >= 1024,

    init() {
        this.$watch('areaQuery', (value) => {
            window.clearTimeout(this.searchTimer);

            if (value.trim().length < 3) {
                this.areas = [];
                this.areaError = '';

                return;
            }

            this.searchTimer = window.setTimeout(() => this.searchAreas(), 400);
        });
    },

    async applyPromotion() {
        this.loading = true;
        this.error = '';

        try {
            const response = await this.request(config.promotionUrl, 'POST', {
                code: this.code,
                customer_phone: document.getElementById('customer-phone').value,
            });

            this.code = response.code;
            this.discount = Number(response.discount_amount);
        } catch (error) {
            this.discount = 0;
            this.error = error.message;
        } finally {
            this.loading = false;
        }
    },

    async fetchRates() {
        if (!this.selectedArea || this.ratesLoading) {
            return;
        }

        this.ratesLoading = true;
        this.ratesError = '';
        this.rates = [];
        this.shippingOption = null;
        this.shippingFee = 0;

        try {
            const response = await this.request(config.ratesUrl, 'POST', {
                destination_area_id: this.selectedArea.id,
                destination_postal_code: this.selectedArea.postal_code,
            });

            this.rates = response.rates;
            this.ratesFallback = response.fallback;

            if (this.ratesFallback && this.rates.length === 1) {
                await this.selectRate(this.rates[0]);
            }
        } catch (error) {
            this.ratesError = error.message;
        } finally {
            this.ratesLoading = false;
        }
    },

    format(value) {
        return new Intl.NumberFormat('id-ID').format(Number(value));
    },

    async request(url, method, body = null) {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body ? JSON.stringify(body) : null,
        });
        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(payload.errors?.shipping?.[0] || payload.message || 'Permintaan tidak dapat diproses. Silakan coba lagi.');
        }

        return payload;
    },

    async searchAreas() {
        this.areaLoading = true;
        this.areaError = '';

        try {
            const response = await this.request(`${config.areasUrl}?input=${encodeURIComponent(this.areaQuery.trim())}`, 'GET');
            this.areas = response.areas;
        } catch (error) {
            this.areas = [];
            this.areaError = error.message;
        } finally {
            this.areaLoading = false;
        }
    },

    async selectArea(area) {
        this.selectedArea = area;
        this.areaQuery = area.name;
        this.areas = [];
        await this.fetchRates();
    },

    async selectRate(rate) {
        if (this.selectionLoading) {
            return;
        }

        this.selectionLoading = true;
        this.ratesError = '';

        try {
            const response = await this.request(config.selectionUrl, 'POST', {
                courier_company: rate.company,
                courier_service: rate.service,
            });

            this.shippingOption = response.shipping;
            this.shippingFee = Number(response.shipping.delivery_fee);
        } catch (error) {
            this.ratesError = error.message;
        } finally {
            this.selectionLoading = false;
        }
    },
}));

Alpine.start();
