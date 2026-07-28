# Update ARKA POS

## Ringkasan Perubahan

### 1. Fitur Baru: Manajemen Produk Expired
- Halaman baru `/stock-in/expired` untuk semua role (kasir, admin, super_admin)
- Tabel daftar produk dengan stok > 0, status expired, dan batch expired date
- Filter tabs: Semua, Expired, Hampir Expired (30 hari), Tanpa Expiry
- **Search bar** (client-side) — cari berdasarkan nama, PLU (SKU), atau barcode
- Edit tanggal expired (khusus admin+) via modal inline
- Route `POST /stock-in/expired/update` untuk menyimpan perubahan
- Sidebar menu "Produk Expired" untuk semua role

### 2. Expired Date pada Stock In
- Migration menambahkan kolom `expired_date` (date, nullable) ke tabel `stock_in_items` dan `products`
- Form input expired date per-item saat stok masuk
- Tampilan kolom Expired + status badge (EXP / OK / near expiry) di detail stok masuk
- Model `StockInItem`: method `isExpired()`, `isNearExpiry()`, cast `expired_date`
- Model `Product`: method `getEarliestExpiryDate()`, `isExpired()`, `isNearExpiry()`

### 3. Perbaikan Bug
- **Empty string handling**: Semua nullable date field (`expired_date`, `promo_start`, `promo_end`, `primary_supplier_id`) dinormalisasi dari `""` ke `null` sebelum validasi di `ProductController`
- **SoftDeletes conflict**: Duplicate product name check pakai `withTrashed()`; unique validation pakai `deleted_at,NULL`
- **FindOrFail 404**: `updateExpired()` pakai `withTrashed()->findOrFail()` karena produk bisa soft-deleted
- **Error feedback**: Tampilkan `$errors->any()` di halaman produk dan kategori
- **HTML entity**: `&middot;` diganti literal `·`
- **Store name**: Semua pesan feedback (success/error) sekarang menyertakan nama toko

### 4. Perbaikan Lain
- `UnitController@store` return JSON untuk request AJAX
- `StockInController@update` — tambahkan `->with('success', ...)` yang sebelumnya hilang
- `getEarliestExpiryDate()` diubah dari query fresh ke collection-based
- Layout: flash messages pakai `@json()` untuk safe JS embedding
- Sidebar: active state "Stok Masuk" tidak menyala di `/stock-in/expired*`

### 5. Seeder Idempotent
- Semua seeder diubah ke `firstOrCreate()` agar bisa dijalankan berulang kali tanpa error duplikat
- `BranchStoreSeeder`: branch/store idempotent
- `UserSeeder`: NIK explicit untuk akun default (26050001/26050002/26050005), `firstOrCreate` by NIK atau email
- `ProductSeeder`: 15 produk dengan `unit`, `expired_date`, `profit_percentage`, `tax_amount`
- `StockProduct`: `firstOrCreate` by product_id + store_id
- `HistorySeeder`: `firstOrCreate` by invoice_id
- `VouchersSeeder`, `CustomersSeeder`, `PromotionsSeeder`: idempotent

### 6. File Baru
| File | Keterangan |
|------|-----------|
| `resources/views/pages/stock-in-expired.blade.php` | Halaman expired products |
| `database/migrations/2026_06_05_004349_add_expired_date_to_stock_in_items_and_products.php` | Migration expired_date |

### 7. File Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/ProductController.php` | Empty string normalization, withTrashed, store name |
| `app/Http/Controllers/StockInController.php` | expired() + updateExpired() methods |
| `app/Http/Controllers/UnitController.php` | JSON response on AJAX |
| `app/Models/Product.php` | stockInItems(), expiry helpers |
| `app/Models/StockInItem.php` | expired_date cast, isExpired()/isNearExpiry() |
| `app/Models/StockInItem.php` | expired_date fillable + casts |
| `resources/views/pages/product.blade.php` | expired_date field, error display, `·` fix |
| `resources/views/pages/stock-in-create.blade.php` | expired date picker per item |
| `resources/views/pages/stock-in-detail.blade.php` | expired column |
| `resources/views/pages/category.blade.php` | `@error('name')` |
| `resources/views/components/layout.blade.php` | `@json()` for flash messages |
| `resources/views/components/side-bar.blade.php` | Expired link, active state fix |
| `routes/web.php` | Expired routes |
| Database seeders (8 files) | `firstOrCreate()` idempotent |

## Cara Menjalankan
```bash
php artisan migrate --seed
```
Seeder sudah idempotent, aman dijalankan berulang.
