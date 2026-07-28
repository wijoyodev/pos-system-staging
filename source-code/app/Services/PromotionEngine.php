<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Promotion;
use App\Models\Voucher;
use Illuminate\Support\Collection;

class PromotionEngine
{
    protected array $appliedPromos = [];

    protected $cart;

    protected float $subtotal;

    protected ?int $storeId;

    protected ?int $userId;

    protected ?string $userRole;

    protected ?int $customerId;

    public function __construct($cart, float $subtotal, ?int $storeId = null, ?int $userId = null, ?string $userRole = null, ?int $customerId = null)
    {
        $this->cart = $cart;
        $this->subtotal = $subtotal;
        $this->storeId = $storeId;
        $this->userId = $userId;
        $this->userRole = $userRole;
        $this->customerId = $customerId;
    }

    public function getAvailablePromotions(): Collection
    {
        $query = Promotion::where(function ($q) {
            $q->whereNull('store_id')
                ->orWhere('store_id', $this->storeId);
        })
            ->where('is_active', true)
            ->orderBy('priority', 'desc');

        $promos = $query->get();

        // For now, just return all active promos without strict time filtering
        return $promos;
    }

    public function isPromoValid(Promotion $promo): bool
    {
        // Simplified validation - just check if promo is active
        if (! $promo->is_active) {
            return false;
        }

        // Check min purchase
        if ($promo->min_purchase_amount && $this->subtotal < $promo->min_purchase_amount) {
            return false;
        }

        // Check cart eligibility based on type
        return $this->checkCartEligibility($promo);
    }

    protected function checkTimeValidity(Promotion $promo): bool
    {
        $now = now();

        if ($promo->day_of_week) {
            $days = explode(',', $promo->day_of_week);
            if (! in_array($now->dayOfWeek, $days)) {
                return false;
            }
        }

        if ($promo->start_time && $promo->end_time) {
            $currentTime = $now->format('H:i:s');
            if ($currentTime < $promo->start_time || $currentTime > $promo->end_time) {
                return false;
            }
        }

        return true;
    }

    protected function checkUserEligibility(Promotion $promo): bool
    {
        if ($promo->eligibleRoles && $promo->type === 'member') {
            if (! $this->userRole || ! in_array($this->userRole, $promo->eligibleRoles)) {
                return false;
            }
        }

        return true;
    }

    protected function checkCartEligibility(Promotion $promo): bool
    {
        switch ($promo->type) {
            case 'category':
                return $this->hasItemsInCategory($promo->category_id);

            case 'product':
                return $this->hasSpecificProduct($promo->product_id);

            case 'buy_x_get_y':
                // For BOGO, check if buy product is in cart with sufficient qty
                return $this->hasBuyProduct($promo->buy_product_id, $promo->buy_quantity ?? 1);

            case 'bundle':
                return $this->hasBundleProducts($promo->products ?? []);

            case 'member':
                return $this->customerId !== null;

            case 'voucher':
                // Voucher: check if subtotal meets min_purchase_amount (for redemption)
                if ($promo->min_purchase_amount && $this->subtotal < $promo->min_purchase_amount) {
                    return false;
                }

                return true;

            default:
                return true;
        }
    }

    protected function hasBuyProduct(?int $productId, int $minQty = 1): bool
    {
        if (! $productId) {
            return true;
        }

        $qty = 0;
        foreach ($this->cart as $item) {
            $itemId = is_array($item) ? ($item['id'] ?? null) : (is_object($item) ? $item->id : null);
            $itemQty = is_array($item) ? ($item['quantity'] ?? 1) : (is_object($item) ? $item->quantity : 1);

            if ($itemId == $productId) {
                $qty += $itemQty;
            }
        }

        return $qty >= $minQty;
    }

    protected function hasItemsInCategory(?int $categoryId): bool
    {
        if (! $categoryId) {
            return true;
        }

        foreach ($this->cart as $item) {
            $product = Product::find($item['id']);
            if ($product && $product->category_id == $categoryId) {
                return true;
            }
        }

        return false;
    }

    protected function hasSpecificProduct(?int $productId): bool
    {
        if (! $productId) {
            return true;
        }

        foreach ($this->cart as $item) {
            if ($item['id'] == $productId) {
                return true;
            }
        }

        return false;
    }

    protected function hasBundleProducts(array $productIds): bool
    {
        if (empty($productIds)) {
            return true;
        }

        $cartProductIds = collect($this->cart)->pluck('id')->toArray();
        foreach ($productIds as $pid) {
            if (! in_array((int) $pid, $cartProductIds)) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount(Promotion $promo): float
    {
        $discount = 0;

        switch ($promo->type) {
            case 'percentage':
                $discount = $this->subtotal * ($promo->discount_percentage / 100);
                if ($promo->max_discount_amount && $discount > $promo->max_discount_amount) {
                    $discount = $promo->max_discount_amount;
                }
                break;

            case 'nominal':
                $discount = $promo->discount_nominal;
                break;

            case 'min_purchase':
                if ($this->subtotal >= $promo->min_purchase_amount) {
                    $discount = $promo->discount_nominal ?? ($this->subtotal * ($promo->discount_percentage / 100));
                }
                break;

            case 'member':
                $discount = $promo->discount_nominal;
                if (! $discount && $promo->discount_percentage) {
                    $discount = $this->subtotal * ($promo->discount_percentage / 100);
                    if ($promo->max_discount_amount && $discount > $promo->max_discount_amount) {
                        $discount = $promo->max_discount_amount;
                    }
                }
                break;

            case 'time_based':
                if ($this->checkTimeValidity($promo)) {
                    $discount = $promo->discount_nominal ?? ($this->subtotal * ($promo->discount_percentage / 100));
                }
                break;

            case 'category':
                $categoryTotal = $this->getCategoryTotal($promo->category_id);
                if ($promo->discount_nominal) {
                    $discount = $promo->discount_nominal;
                } else {
                    $discount = $categoryTotal * ($promo->discount_percentage / 100);
                }
                break;

            case 'product':
                $productTotal = $this->getProductTotal($promo->product_id);
                if ($promo->discount_nominal) {
                    $qty = $this->getProductQuantity($promo->product_id);
                    $discount = $promo->discount_nominal * $qty;
                } else {
                    $discount = $productTotal * ($promo->discount_percentage / 100);
                }
                break;

            case 'tiered':
                $discount = $this->calculateTieredDiscount($promo);
                break;

            case 'bundle':
                $discount = $this->calculateBundleDiscount($promo);
                break;

            case 'buy_x_get_y':
                $discount = $this->calculateBogoDiscount($promo);
                break;

            case 'voucher':
                // Voucher promo: check if cart meets threshold to earn voucher
                // OR check if a voucher code is being redeemed
                $discount = $this->calculateVoucherDiscount($promo);
                break;
        }

        return max(0, $discount);
    }

    protected function getCategoryTotal(?int $categoryId): float
    {
        $total = 0;
        foreach ($this->cart as $item) {
            $product = Product::find($item['id']);
            if ($product && $product->category_id == $categoryId) {
                $total += $item['price'] * $item['quantity'];
            }
        }

        return $total;
    }

    protected function getProductTotal(?int $productId): float
    {
        $total = 0;
        foreach ($this->cart as $item) {
            if ($item['id'] == $productId) {
                $total += $item['price'] * $item['quantity'];
            }
        }

        return $total;
    }

    protected function getProductQuantity(?int $productId): int
    {
        $qty = 0;
        foreach ($this->cart as $item) {
            if ($item['id'] == $productId) {
                $qty += $item['quantity'];
            }
        }

        return $qty;
    }

    protected function calculateTieredDiscount(Promotion $promo): float
    {
        $tiers = $promo->tiers ?? [];
        if (empty($tiers)) {
            return 0;
        }

        // Sort tiers by min_amount descending
        usort($tiers, fn ($a, $b) => $b['min_amount'] - $a['min_amount']);

        $eligibleTier = null;
        foreach ($tiers as $tier) {
            if ($this->subtotal >= ($tier['min_amount'] ?? 0)) {
                $eligibleTier = $tier;
                break;
            }
        }

        if (! $eligibleTier) {
            return 0;
        }

        if (isset($eligibleTier['percentage'])) {
            return $this->subtotal * ($eligibleTier['percentage'] / 100);
        }

        return $eligibleTier['discount'] ?? 0;
    }

    protected function calculateBundleDiscount(Promotion $promo): float
    {
        $productsInBundle = $promo->products ?? [];
        $bundlePrice = $promo->bundle_price ?? 0;

        if (empty($productsInBundle) || ! $bundlePrice) {
            \Log::info('Bundle Discount skipped', [
                'promo' => $promo->name,
                'products' => $productsInBundle,
                'bundle_price' => $bundlePrice,
            ]);

            return 0;
        }

        $cartProductQtys = [];
        foreach ($this->cart as $item) {
            $itemId = is_array($item) ? ($item['id'] ?? null) : (is_object($item) ? $item->id : null);
            $itemQty = is_array($item) ? ($item['quantity'] ?? 1) : (is_object($item) ? $item->quantity : 1);
            if ($itemId !== null) {
                $cartProductQtys[$itemId] = ($cartProductQtys[$itemId] ?? 0) + $itemQty;
            }
        }

        \Log::info('Bundle Debug', [
            'promo' => $promo->name,
            'productsInBundle' => $productsInBundle,
            'cartProductQtys' => $cartProductQtys,
        ]);

        $possibleSets = [];
        foreach ($productsInBundle as $pid) {
            $possibleSets[] = $cartProductQtys[(int) $pid] ?? 0;
        }

        $numSets = empty($possibleSets) ? 0 : min($possibleSets);
        if ($numSets <= 0) {
            \Log::info('Bundle Debug: no complete sets', ['numSets' => $numSets]);

            return 0;
        }

        $originalPriceOfOneSet = 0;
        foreach ($productsInBundle as $pid) {
            $product = Product::find((int) $pid);
            if ($product) {
                $originalPriceOfOneSet += $product->selling_price;
            }
        }

        $setDiscount = max(0, $originalPriceOfOneSet - $bundlePrice);
        $totalDiscount = $setDiscount * $numSets;

        \Log::info('Bundle Discount Result', [
            'originalPriceOfOneSet' => $originalPriceOfOneSet,
            'bundlePrice' => $bundlePrice,
            'setDiscount' => $setDiscount,
            'numSets' => $numSets,
            'totalDiscount' => $totalDiscount,
        ]);

        return $totalDiscount;
    }

    protected function calculateBogoDiscount(Promotion $promo): float
    {
        $buyQty = $promo->buy_quantity ?? 1;
        $getQty = $promo->get_quantity ?? 1;
        $buyProductId = $promo->buy_product_id;
        $getProductId = $promo->get_product_id;

        // If no get_product_id, assume same as buy_product (self-BOGO)
        if (! $getProductId) {
            $getProductId = $buyProductId;
        }

        if (! $buyProductId) {
            \Log::info('BOGO Debug: missing product ID', [
                'buyProductId' => $buyProductId,
                'getProductId' => $getProductId,
            ]);

            return 0;
        }

        // Count quantities in cart
        $buyQtyInCart = 0;
        $getQtyInCart = 0;

        foreach ($this->cart as $item) {
            $itemId = is_array($item) ? ($item['id'] ?? null) : (is_object($item) ? $item->id : null);
            $itemQty = is_array($item) ? ($item['quantity'] ?? 1) : (is_object($item) ? $item->quantity : 1);

            if ($itemId == $buyProductId) {
                $buyQtyInCart += $itemQty;
            }
            if ($itemId == $getProductId) {
                $getQtyInCart += $itemQty;
            }
        }

        \Log::info('BOGO Debug', [
            'buyProductId' => $buyProductId,
            'getProductId' => $getProductId,
            'buyQtyInCart' => $buyQtyInCart,
            'getQtyInCart' => $getQtyInCart,
            'cart' => $this->cart,
        ]);

        // Calculate eligible free items
        if ($buyProductId == $getProductId) {
            // Self-BOGO (same product): Beli X Gratis Y
            // Setiap (X+Y) item, Y item gratis
            $totalQty = $buyQtyInCart; // same product, same count
            $eligibleSets = intdiv($totalQty, $buyQty + $getQty);
            $freeItems = $eligibleSets * $getQty;
        } else {
            // Cross-product BOGO: Beli X produk A, Gratis Y produk B
            $eligibleSets = intdiv($buyQtyInCart, $buyQty);
            $freeItems = $eligibleSets * $getQty;
            $freeItems = min($freeItems, $getQtyInCart);
        }

        if ($freeItems == 0) {
            return 0;
        }

        // Get the get_product price
        $getProductPrice = 0;
        foreach ($this->cart as $item) {
            $itemId = is_array($item) ? ($item['id'] ?? null) : (is_object($item) ? $item->id : null);
            if ($itemId == $getProductId) {
                $getProductPrice = is_array($item) ? ($item['price'] ?? 0) : (is_object($item) ? $item->price : 0);
                break;
            }
        }

        if ($getProductPrice == 0) {
            return 0;
        }

        // Discount = free items * price
        $discount = $freeItems * $getProductPrice;

        \Log::info('BOGO Discount Result', [
            'freeItems' => $freeItems,
            'getProductPrice' => $getProductPrice,
            'discount' => $discount,
        ]);

        return $discount;
    }

    protected function calculateVoucherDiscount(Promotion $promo): float
    {
        // Check if this is a voucher code redemption (has code)
        // Or a voucher earning threshold promo

        // If promo has a code, it's a voucher that can be redeemed
        if ($promo->code) {
            // Check if minimum purchase is met
            if ($promo->min_purchase_amount && $this->subtotal < $promo->min_purchase_amount) {
                return 0;
            }

            // Check usage limit
            if ($promo->usage_limit && $promo->usage_count >= $promo->usage_limit) {
                return 0;
            }

            // Apply discount
            $discount = $promo->discount_nominal;
            if (! $discount && $promo->discount_percentage) {
                $discount = $this->subtotal * ($promo->discount_percentage / 100);
                if ($promo->max_discount_amount && $discount > $promo->max_discount_amount) {
                    $discount = $promo->max_discount_amount;
                }
            }

            return max(0, $discount);
        }

        // If no code, this is a "earn voucher" threshold setting
        // Return 0 discount (voucher earning is handled in PaymentController::process)
        return 0;
    }

    public function applyPromo(Promotion $promo): bool
    {
        if (! $this->isPromoValid($promo)) {
            return false;
        }

        $discount = $this->calculateDiscount($promo);

        if ($discount <= 0) {
            return false;
        }

        $this->appliedPromos[] = [
            'promotion_id' => $promo->id,
            'name' => $promo->name,
            'type' => $promo->type,
            'discount' => $discount,
        ];

        return true;
    }

    public function applyAutoPromos(): array
    {
        $available = $this->getAvailablePromotions();

        // Apply Member discounts first (highest priority)
        $memberPromos = $available->where('type', 'member');
        foreach ($memberPromos as $promo) {
            $this->applyPromo($promo);
        }

        // Apply percentage promos
        $percentagePromos = $available->whereIn('type', ['percentage', 'category', 'product', 'tiered']);
        foreach ($percentagePromos as $promo) {
            if (! $this->isStackable($promo)) {
                continue;
            }
            $this->applyPromo($promo);
        }

        // Apply nominal promos
        $nominalPromos = $available->whereIn('type', ['nominal', 'min_purchase', 'time_based']);
        foreach ($nominalPromos as $promo) {
            if (! $this->isStackable($promo)) {
                continue;
            }
            $this->applyPromo($promo);
        }

        // Apply special promos (BOGO, Bundle)
        $specialPromos = $available->whereIn('type', ['buy_x_get_y', 'bundle']);
        foreach ($specialPromos as $promo) {
            $this->applyPromo($promo);
        }

        return $this->appliedPromos;
    }

    protected function isStackable(Promotion $promo): bool
    {
        if (empty($this->appliedPromos)) {
            return true;
        }

        if ($promo->stackable) {
            return true;
        }

        foreach ($this->appliedPromos as $applied) {
            $existingPromo = Promotion::find($applied['promotion_id']);
            if (! $existingPromo) {
                continue;
            }

            if ($existingPromo->stackable) {
                continue;
            }

            if ($this->promosOverlap($promo, $existingPromo)) {
                return false;
            }
        }

        return true;
    }

    protected function promosOverlap(Promotion $a, Promotion $b): bool
    {
        if ($a->product_id && $b->product_id && $a->product_id === $b->product_id) {
            return true;
        }
        if ($a->category_id && $b->category_id && $a->category_id === $b->category_id) {
            return true;
        }
        if ($a->buy_product_id && $b->buy_product_id && $a->buy_product_id === $b->buy_product_id) {
            return true;
        }
        if ($a->buy_product_id && $b->product_id && $a->buy_product_id === $b->product_id) {
            return true;
        }
        if ($a->product_id && $b->buy_product_id && $a->product_id === $b->buy_product_id) {
            return true;
        }
        if ($a->get_product_id && $b->get_product_id && $a->get_product_id === $b->get_product_id) {
            return true;
        }
        if ($a->get_product_id && $b->product_id && $a->get_product_id === $b->product_id) {
            return true;
        }
        if ($a->product_id && $b->get_product_id && $a->product_id === $b->get_product_id) {
            return true;
        }
        if ($a->buy_product_id && $b->get_product_id && $a->buy_product_id === $b->get_product_id) {
            return true;
        }
        if ($a->get_product_id && $b->buy_product_id && $a->get_product_id === $b->buy_product_id) {
            return true;
        }

        return false;
    }

    public function getTotalDiscount(): float
    {
        return collect($this->appliedPromos)->sum('discount');
    }

    public function getAppliedPromos(): array
    {
        return $this->appliedPromos;
    }

    public static function validateVoucher(string $code, ?int $storeId = null): array
    {
        $promo = Promotion::where('code', $code)
            ->where('type', 'voucher')
            ->where('is_active', true)
            ->first();

        if (! $promo) {
            return ['valid' => false, 'message' => 'Voucher tidak valid'];
        }

        if (! $promo->isValid()) {
            return ['valid' => false, 'message' => 'Voucher sudah expired atau sudah mencapai limit'];
        }

        return [
            'valid' => true,
            'promotion' => $promo,
            'discount' => $promo->discount_nominal ?? $promo->discount_percentage,
        ];
    }
}
