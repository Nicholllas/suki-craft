---
paths:
  - 'app/Models/**'
---

# Models

## Payment deadline follows delivery slot start
An unpaid order is payable only before the selected delivery time slot begins. At the slot start it must transition from pending_payment to cancelled, retaining the cancellation reason and status history.
