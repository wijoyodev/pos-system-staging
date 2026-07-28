<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <!-- Main Content Canvas -->
    <main class="flex-1 flex flex-col min-h-screen relative w-full">

        <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
            <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
                <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Low Stock Alerts</h1>
            </div>
        </header>

        <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
            <div class="mb-6 lg:mb-8">
                <x-report-header title="Low Stock Alerts" module="Inventory" submodule="Stock Management" description="{{ $low_stocks->count() }} items require immediate attention to maintain operations.">
                    <x-slot name="actions">
                        <form action="/stock/import" method="POST" enctype="multipart/form-data" class="flex gap-2 flex-wrap">
                            @csrf
                            <a href="/template-import-stock.csv" download class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg font-bold text-sm hover:bg-green-700 transition-colors">
                                <span class="material-symbols-outlined text-lg">download</span>
                                Template
                            </a>
                            <label class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-bold text-sm cursor-pointer hover:bg-primary-container transition-colors">
                                <span class="material-symbols-outlined text-lg">upload_file</span>
                                Import Excel/CSV
                                <input type="file" name="file" class="hidden" accept=".csv,.txt,.xls,.xlsx" onchange="this.form.submit()">
                            </label>
                        </form>
                    </x-slot>
                </x-report-header>
            </div>

        @if(session('import_results'))
        <div class="mb-6 p-4 rounded-xl {{ session('import_results')['success'] > 0 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined {{ session('import_results')['success'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ session('import_results')['success'] > 0 ? 'check_circle' : 'error' }}
                </span>
                <span class="font-bold {{ session('import_results')['success'] > 0 ? 'text-green-700' : 'text-red-700' }}">
                    {{ session('import_results')['success'] }} berhasil, {{ session('import_results')['failed'] }} gagal
                </span>
            </div>
            @if(count(session('import_results')['errors']) > 0)
            <ul class="text-xs text-red-600 space-y-1">
                @foreach(array_slice(session('import_results')['errors'], 0, 5) as $error)
                <li>- {{ $error }}</li>
                @endforeach
            </ul>
            @endif
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6 mb-6 lg:mb-8 max-w-4xl">
            <!-- Highlight Card 1 -->
            <div class="col-span-1 bg-error-container p-4 lg:p-6 rounded-xl flex flex-col justify-between min-h-[120px] lg:min-h-[160px]">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-on-error-container/10 rounded-lg">
                        <span class="material-symbols-outlined text-on-error-container text-lg lg:text-xl" style="font-variation-settings: 'FILL' 1;">error</span>
                    </div>
                </div>
                <div>
                    <div class="text-2xl lg:text-4xl font-extrabold text-on-error-container mb-1">{{ $critical_count }}</div>
                    <div class="text-xs lg:text-sm font-semibold text-on-error-container opacity-80 uppercase tracking-widest">Out of Stock</div>
                </div>
            </div>
            <!-- Highlight Card 2 -->
            <div class="col-span-1 bg-surface-container-lowest p-4 lg:p-6 rounded-xl flex flex-col justify-between min-h-[120px] lg:min-h-[160px] shadow-sm">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-primary/5 rounded-lg">
                        <span class="material-symbols-outlined text-primary text-lg lg:text-xl">trending_down</span>
                    </div>
                </div>
                <div>
                    <div class="text-2xl lg:text-4xl font-extrabold text-primary mb-1">{{ $warning_count }}</div>
                    <div class="text-xs lg:text-sm font-semibold text-on-surface-variant uppercase tracking-widest">Below Threshold</div>
                </div>
            </div>
        </div>

        <!-- List Section: Asymmetric & Layered -->
        <div class="space-y-3 lg:space-y-4 max-w-6xl">
            <!-- Header for list -->
            <div class="hidden lg:grid grid-cols-12 px-6 py-3 text-xs font-bold text-on-surface-variant uppercase tracking-widest">
                <div class="col-span-5">Product Details</div>
                <div class="col-span-2 text-center">Status</div>
                <div class="col-span-2 text-right">Current Stock</div>
                <div class="col-span-1 text-right">Threshold</div>
            </div>

            @foreach($low_stocks as $product)
            @php
                $stockQty = isset($storeId) && $storeId ? $product->getStockForStore($storeId) : $product->getStockTotal();
            @endphp
            <div class="grid grid-cols-12 items-center bg-surface-container-lowest p-4 lg:p-6 rounded-xl hover:shadow-lg transition-shadow duration-300 border-l-4 {{ $stockQty == 0 ? 'border-error bg-error/5' : 'border-tertiary' }}">
                <div class="col-span-5 flex items-center gap-3 lg:gap-4">
                    <div class="w-10 h-10 lg:w-14 lg:h-14 rounded-lg overflow-hidden bg-surface flex items-center justify-center {{ $stockQty == 0 ? 'grayscale' : '' }}">
                        @if($product->image)
                        <img class="w-full h-full object-cover" src="{{ asset('storage/' . $product->image) }}" />
                        @else
                        <span class="material-symbols-outlined text-slate-400 text-lg lg:text-xl">image</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-sm lg:text-lg {{ $stockQty == 0 ? 'text-error' : 'text-primary' }}">{{ $product->name }}</h3>
                        <p class="text-on-surface-variant text-xs font-medium">SKU: {{ $product->sku }}</p>
                    </div>
                </div>
                <div class="col-span-2 flex justify-center">
                    @if($stockQty == 0)
                    <span class="px-2 lg:px-3 py-1 bg-error text-white text-[10px] font-extrabold uppercase rounded-full">Out</span>
                    @else
                    <span class="px-2 lg:px-3 py-1 bg-tertiary-container text-on-tertiary-container text-[10px] font-extrabold uppercase rounded-full">Low</span>
                    @endif
                </div>
                <div class="col-span-2 text-right">
                    <span class="text-base lg:text-xl font-bold {{ $stockQty == 0 ? 'text-error' : 'text-tertiary' }}">{{ $stockQty }}</span>
                </div>
                <div class="col-span-1 text-right">
                    <span class="text-xs lg:text-sm font-semibold text-on-surface-variant">{{ $product->threshold }}</span>
                </div>
            </div>
            @endforeach

            @if($low_stocks->isEmpty())
            <div class="p-6 lg:p-8 text-center text-on-surface-variant bg-surface-container-lowest rounded-xl">
                All stocks are currently above the threshold. Well done!
            </div>
            @endif
        </div>
        </div>
    </main>
</x-layout>
