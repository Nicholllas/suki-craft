---
paths:
  - 'app/**'
---

# App

## Derive recipe mode from the linked variant
Do not store a recipe-mode flag on product_ingredients. A row linked to a ProductVariant with is_quantity_based=true uses ratio_per_unit and keeps quantity_needed null; default and ordinary-variant rows use quantity_needed and keep ratio_per_unit null. Inventory deduction must derive the mode from the linked ProductVariant.

## Hitung ongkir dari rute OpenRouteService
Ongkir checkout memakai jarak rute berkendara dari koordinat toko, bukan jarak garis lurus. Tarif dasar Rp17.000 mencakup sampai 5 km; kelebihannya dibulatkan ke atas per km dan dikenai Rp3.000/km; radius maksimum 20 km. Quote harus tervalidasi di server dan snapshot koordinat, jarak, provider, waktu kalkulasi, serta ongkir disimpan pada order.
