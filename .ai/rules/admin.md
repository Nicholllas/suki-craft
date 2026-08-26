---
paths:
  - 'app/{Providers,Http/Controllers/Admin}/**'
---

# Admin

## Admin action-required priorities
Treat Custom Bouquet requests in waiting_review or revision_requested as requiring admin action. The dashboard's urgent bouquet recap is limited to payment-confirmed orders whose delivery date is today or earlier; orders in later stages are managed from the delivery workflow.
