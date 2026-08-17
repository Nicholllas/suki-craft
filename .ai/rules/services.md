---
paths:
  - 'app/Services/**'
---

# Services

## Generate QRIS locally from the static payload
QRIS images are generated locally from PAYMENT_QRIS_PAYLOAD and each order total. Do not send the payload or order amount to an external QR generator; keep PAYMENT_QRIS_PATH only as the fallback when the payload is unset.
