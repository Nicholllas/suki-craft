---
paths:
  - 'app/**'
---

# App

## Derive recipe mode from the linked variant
Do not store a recipe-mode flag on product_ingredients. A row linked to a ProductVariant with is_quantity_based=true uses ratio_per_unit and keeps quantity_needed null; default and ordinary-variant rows use quantity_needed and keep ratio_per_unit null. Inventory deduction must derive the mode from the linked ProductVariant.
