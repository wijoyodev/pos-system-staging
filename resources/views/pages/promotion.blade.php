<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full">

        <header
            class="top-0 z-40 sticky bg-white/70 backdrop-blur-xl border-b border-slate-200/20 shadow-sm flex justify-between items-start sm:items-center gap-2 flex-wrap w-full px-4 lg:px-6 py-3">
            <div class="flex items-center gap-4">
                <span class="text-base lg:text-xl font-bold text-blue-900 font-manrope">{{ $title }}</span>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openAddModal()"
                    class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary-container transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">add</span>
                    Add Promo
                </button>
            </div>
        </header>

        <div class="w-full px-4 lg:px-8 py-6">
        <!-- Report Header Section -->
        <div class="mb-6 lg:mb-8">
            <x-report-header title="{{ $title ?? 'Page' }}" />
        </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filters --}}
            <div class="mb-6 flex gap-4 flex-wrap">
                <form method="GET" class="flex gap-3 flex-wrap">
                    <input name="search" value="{{ request('search') }}"
                        class="px-4 py-2 bg-surface-container rounded-lg border-none text-sm"
                        placeholder="Search promotions...">
                    <select name="type"
                        class="px-4 py-2 bg-surface-container rounded-lg border-none text-sm">
                        <option value="">All Types</option>
                        @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-bold">
                        Filter
                    </button>
                </form>
            </div>

            {{-- Promotions Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($promotions as $promo)
                <div class="bg-surface-container rounded-xl p-4 border {{ $promo->is_active ? 'border-primary/20' : 'border-slate-200' }}">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-bold text-blue-900">{{ $promo->name }}</h3>
                            <span class="text-xs px-2 py-0.5 rounded {{ $promo->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $promo->getTypeLabel() }}
                            </span>
                            @if($promo->code)
                            <span class="text-xs ml-2 px-2 py-0.5 rounded bg-blue-100 text-blue-700 font-mono">
                                {{ $promo->code }}
                            </span>
                            @endif
                        </div>
                        <form action="/promotions/{{ $promo->id }}/toggle" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-10 h-6 rounded-full transition-colors {{ $promo->is_active ? 'bg-green-500' : 'bg-slate-300' }}">
                                <span class="block w-4 h-4 bg-white rounded-full transform transition-transform {{ $promo->is_active ? 'translate-x-5' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </div>

                    <p class="text-xs text-slate-500 mb-3">{{ $promo->description ?? 'No description' }}</p>

                    <div class="text-sm mb-3">
                        @if($promo->discount_percentage)
                        <span class="font-bold text-primary">{{ $promo->discount_percentage }}% OFF</span>
                        @elseif($promo->discount_nominal)
                        <span class="font-bold text-primary">Rp{{ number_format($promo->discount_nominal) }} OFF</span>
                        @elseif($promo->type === 'tiered')
                        <span class="font-bold text-primary">Tiered Discount</span>
                        @elseif($promo->type === 'buy_x_get_y')
                        <span class="font-bold text-primary">Buy {{ $promo->buy_quantity ?? 1 }} Get {{ $promo->get_quantity ?? 1 }}</span>
                        @elseif($promo->type === 'bundle')
                        <span class="font-bold text-primary">Bundle: Rp{{ number_format($promo->bundle_price) }}</span>
                        @endif

                        @if($promo->min_purchase_amount)
                        <span class="text-slate-500 text-xs ml-2">Min Rp{{ number_format($promo->min_purchase_amount) }}</span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
                        <span>
                            @if($promo->start_date || $promo->end_date)
                            {{ $promo->start_date?->format('d/m') }} - {{ $promo->end_date?->format('d/m/y') }}
                            @else
                            Always
                            @endif
                        </span>
                        @if($promo->usage_limit)
                        <span>{{ $promo->usage_count }}/{{ $promo->usage_limit }} used</span>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <button onclick="openEditModal({{ $promo->id }})"
                            class="flex-1 py-2 bg-primary/10 text-primary rounded-lg text-xs font-bold hover:bg-primary/20 transition-colors">
                            Edit
                        </button>
                        <form action="/promotions/{{ $promo->id }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-3 py-2 bg-error/10 text-error rounded-lg text-xs font-bold hover:bg-error/20 transition-colors"
                                onclick="return confirm('Delete this promotion?')">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            @if($promotions->isEmpty())
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-6xl text-slate-300">sell</span>
                <h3 class="mt-4 text-lg font-bold text-slate-600">No Promotions Yet</h3>
                <p class="text-sm text-slate-400 mt-2">Create your first promotion to boost sales!</p>
            </div>
            @endif
        </div>
    </main>

    <style>
        .field-hidden { display: none !important; }
    </style>

    {{-- Add/Edit Promo Modal --}}
    <div id="promoModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-surface-container-lowest rounded-2xl shadow-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modalTitle" class="text-lg font-bold text-blue-900">Add Promotion</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="promoForm" action="/promotions" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Promo Name *</label>
                        <input name="name" id="promoName" required
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm"
                            placeholder="Enter promo name">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Type *</label>
                        <select name="type" id="promoType" required onchange="togglePromoFields()"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                            @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Promo Code (Voucher)</label>
                        <input name="code" id="promoCode"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm font-mono uppercase"
                            placeholder="e.g., DISCOUNT10">
                    </div>

                    <div class="field-group field-percentage field-voucher">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Discount (%)</label>
                        <input name="discount_percentage" id="discountPercentage" type="number" step="0.01" min="0" max="100"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm"
                            placeholder="0">
                    </div>

                    <div class="field-group field-nominal field-voucher">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Discount (Rp)</label>
                        <input name="discount_nominal" id="discountNominal" type="number" min="0"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm"
                            placeholder="0">
                    </div>

                    <div class="field-group field-min_purchase field-voucher">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Min Purchase (Rp)</label>
                        <input name="min_purchase_amount" id="minPurchaseAmount" type="number" min="0"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm"
                            placeholder="0">
                    </div>

                    <div class="field-group field-voucher">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Voucher Threshold (Min. Belanja untuk Dapat Voucher)</label>
                        <input name="voucher_threshold" id="voucherThreshold" type="number" min="0"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm"
                            placeholder="0">
                    </div>

                    <div class="field-group field-min_purchase field-percentage field-member field-time_based field-tiered field-voucher">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Max Discount (Rp)</label>
                        <input name="max_discount_amount" id="maxDiscountAmount" type="number" min="0"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm"
                            placeholder="No limit">
                    </div>

                    <div class="field-group field-product">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Specific Product</label>
                        <select name="product_id" id="selectProduct"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                            <option value="">-- Select Product --</option>
                            @foreach(\App\Models\Product::all() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field-group field-category">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Specific Category</label>
                        <select name="category_id" id="selectCategory"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                            <option value="">-- Select Category --</option>
                            @foreach(\App\Models\Category::all() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field-group field-buy_x_get_y col-span-2 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Buy Product *</label>
                            <select name="buy_product_id" id="selectBuyProduct"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                                <option value="">-- Select Product --</option>
                                @foreach(\App\Models\Product::all() as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Buy Quantity</label>
                            <input name="buy_quantity" id="buyQuantity" type="number" min="1" value="1"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Get Product *</label>
                            <select name="get_product_id" id="selectGetProduct"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                                <option value="">-- Select Product --</option>
                                @foreach(\App\Models\Product::all() as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Get Quantity</label>
                            <input name="get_quantity" id="getQuantity" type="number" min="1" value="1"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                        </div>
                    </div>

                    <div class="field-group field-bundle col-span-2 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Bundle Price (Rp) *</label>
                            <input name="bundle_price" id="bundlePrice" type="number" min="0"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm"
                                placeholder="Total price for the whole set">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Select Products for Bundle *</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-3 bg-surface-container rounded-lg border border-outline-variant/10">
                                @foreach(\App\Models\Product::all() as $p)
                                <label class="flex items-center gap-2 p-2 hover:bg-white/50 rounded-md cursor-pointer transition-colors">
                                    <input type="checkbox" name="products[]" value="{{ $p->id }}" class="bundle-product-checkbox rounded text-primary focus:ring-primary/20">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-bold text-on-surface leading-tight">{{ $p->name }}</span>
                                        <span class="text-[9px] text-slate-400">Rp{{ number_format($p->selling_price) }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="field-group field-tiered col-span-2">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Tiers (JSON)</label>
                        <textarea name="tiers" id="tiersJson" rows="3"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm font-mono"
                            placeholder='[{"min_amount": 50000, "discount": 5000}]'></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Start Date</label>
                        <input name="start_date" id="startDate" type="date"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">End Date</label>
                        <input name="end_date" id="endDate" type="date"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                    </div>

                    <div class="field-group field-time_based">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Start Time</label>
                        <input name="start_time" id="startTime" type="time"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                    </div>

                    <div class="field-group field-time_based">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">End Time</label>
                        <input name="end_time" id="endTime" type="time"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                    </div>

                    <div class="field-group field-time_based col-span-2">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Days (0=Sun, 1=Mon)</label>
                        <input name="day_of_week" id="dayOfWeek" type="text"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm"
                            placeholder="0,6">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Usage Limit</label>
                        <input name="usage_limit" id="usageLimit" type="number" min="1"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Priority</label>
                        <input name="priority" id="priority" type="number" min="0" value="0"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Description</label>
                        <textarea name="description" id="description" rows="2"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/20 py-2 px-3 text-sm"></textarea>
                    </div>

                    <div class="col-span-2 flex items-center gap-6">
                        <label class="flex items-center gap-2">
                            <input name="is_active" type="checkbox" value="1" id="isActive" checked class="rounded">
                            <span class="text-sm font-bold">Active</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input name="stackable" type="checkbox" value="1" id="stackable" class="rounded">
                            <span class="text-sm font-bold">Stackable</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeModal()" class="flex-1 py-3 bg-surface-container font-bold rounded-lg">Cancel</button>
                    <button type="submit" class="flex-1 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary-container">Save Promo</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let promotions = @json($promotions);

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Promotion';
            document.getElementById('promoForm').action = '/promotions';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('promoForm').reset();
            document.querySelectorAll('.bundle-product-checkbox').forEach(cb => cb.checked = false);
            togglePromoFields();
            document.getElementById('promoModal').classList.remove('hidden');
        }

        function openEditModal(id) {
            let promo = promotions.find(p => p.id === id);
            if (!promo) return;

            document.getElementById('modalTitle').textContent = 'Edit Promotion';
            document.getElementById('promoForm').action = '/promotions/' + id;
            document.getElementById('formMethod').value = 'PUT';

            document.getElementById('promoName').value = promo.name || '';
            document.getElementById('promoType').value = promo.type || '';
            document.getElementById('promoCode').value = promo.code || '';
            document.getElementById('discountPercentage').value = promo.discount_percentage || '';
            document.getElementById('discountNominal').value = promo.discount_nominal || '';
            document.getElementById('minPurchaseAmount').value = promo.min_purchase_amount || '';
            document.getElementById('voucherThreshold').value = promo.voucher_threshold || '';
            document.getElementById('maxDiscountAmount').value = promo.max_discount_amount || '';
            document.getElementById('startDate').value = promo.start_date || '';
            document.getElementById('endDate').value = promo.end_date || '';
            document.getElementById('startTime').value = promo.start_time || '';
            document.getElementById('endTime').value = promo.end_time || '';
            document.getElementById('dayOfWeek').value = promo.day_of_week || '';
            document.getElementById('usageLimit').value = promo.usage_limit || '';
            document.getElementById('priority').value = promo.priority || 0;
            document.getElementById('description').value = promo.description || '';
            document.getElementById('isActive').checked = promo.is_active;
            document.getElementById('stackable').checked = promo.stackable;

            if (promo.type === 'buy_x_get_y') {
                document.getElementById('selectBuyProduct').value = promo.buy_product_id || '';
                document.getElementById('buyQuantity').value = promo.buy_quantity || 1;
                document.getElementById('selectGetProduct').value = promo.get_product_id || '';
                document.getElementById('getQuantity').value = promo.get_quantity || 1;
            }
            if (promo.type === 'bundle') {
                document.getElementById('bundlePrice').value = promo.bundle_price || '';
                
                // Clear and Set checkboxes
                document.querySelectorAll('.bundle-product-checkbox').forEach(cb => {
                    cb.checked = (promo.products || []).includes(parseInt(cb.value));
                });
            }
            if (promo.type === 'tiered') {
                document.getElementById('tiersJson').value = JSON.stringify(promo.tiers || []);
            }
            if (promo.product_id) {
                document.getElementById('selectProduct').value = promo.product_id || '';
            }
            if (promo.category_id) {
                document.getElementById('selectCategory').value = promo.category_id || '';
            }

            togglePromoFields();
            document.getElementById('promoModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('promoModal').classList.add('hidden');
        }

        function togglePromoFields() {
            const type = document.getElementById('promoType').value;
            
            document.querySelectorAll('.field-group').forEach(el => {
                el.classList.add('field-hidden');
                // Remove required from all inputs inside hidden groups
                el.querySelectorAll('input, select, textarea').forEach(input => {
                    input.removeAttribute('required');
                });
            });
            
            const fieldMap = {
                'percentage': ['field-percentage'],
                'nominal': ['field-nominal'],
                'buy_x_get_y': ['field-buy_x_get_y'],
                'bundle': ['field-bundle'],
                'min_purchase': ['field-min_purchase'],
                'member': ['field-member'],
                'time_based': ['field-time_based', 'field-nominal'],
                'category': ['field-category', 'field-percentage'],
                'product': ['field-product', 'field-percentage'],
                'tiered': ['field-tiered', 'field-percentage'],
                'voucher': ['field-voucher', 'field-nominal', 'field-min_purchase']
            };

            const activeGroups = fieldMap[type] || [];
            activeGroups.forEach(field => {
                document.querySelectorAll('.' + field).forEach(el => {
                    el.classList.remove('field-hidden');
                    // Add required back to specific important fields if visible
                    if (type === 'buy_x_get_y') {
                        document.getElementById('selectBuyProduct').setAttribute('required', 'required');
                        document.getElementById('selectGetProduct').setAttribute('required', 'required');
                    }
                    if (type === 'bundle') {
                        document.getElementById('bundlePrice').setAttribute('required', 'required');
                    }
                    if (['percentage', 'category', 'product'].includes(type)) {
                        document.getElementById('discountPercentage').setAttribute('required', 'required');
                    }
                    if (['nominal', 'min_purchase', 'voucher'].includes(type)) {
                        document.getElementById('discountNominal').setAttribute('required', 'required');
                    }
                    if (type === 'voucher') {
                        document.getElementById('discountNominal').setAttribute('required', 'required');
                    }
                });
            });
        }

        togglePromoFields();
    </script>
</x-layout>