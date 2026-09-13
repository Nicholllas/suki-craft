---
paths:
  - 'app/Services/**'
---

# Services

## Generate QRIS locally from the static payload
QRIS images are generated locally from PAYMENT_QRIS_PAYLOAD and each order total. Do not send the payload or order amount to an external QR generator; keep PAYMENT_QRIS_PATH only as the fallback when the payload is unset.

## Limit QRIS to Rp500.000
QRIS is available only when the persisted order total is at most config('payment.qris_max_order_amount'), default Rp500.000. Above that amount, show bank transfer as the available payment method and reject direct access to the generated QRIS endpoint.
