---
paths:
  - 'lang/**'
---

# Lang

## Storefront localization
Keep storefront strings in paired lang/id/store.php and lang/en/store.php files with matching semantic keys. Use the session-backed locale middleware and __() in Blade instead of client-side string replacement.
