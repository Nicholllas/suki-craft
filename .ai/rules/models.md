---
paths:
  - 'app/Models/**'
  - app/Models/Customer.php
---

# Models

## Payment deadline follows delivery slot start
An unpaid order is payable only before the selected delivery time slot begins. At the slot start it must transition from pending_payment to cancelled, retaining the cancellation reason and status history.

## Saved delivery address
Customer.address is an optional checkout default, not an order address source of truth. Checkout must keep delivery_address editable and each order continues to snapshot its own delivery address.
