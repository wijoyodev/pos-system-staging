<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>

  <main class="flex-1 flex flex-col min-h-screen relative w-full">

    <header
      class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Tambah Stok Masuk</h1>
      </div>
    </header>

    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
        <div class="mb-6 lg:mb-8">
            <x-report-header title="New Stock In Entry" module="Inventory" submodule="Stock In" description="Record inventory received from suppliers, including quantities and costs." />
        </div>

      <form action="/stock-in" method="POST" id="stockInForm" class="space-y-6">
        @csrf

        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-base font-bold text-on-surface mb-4">Transaction Details</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Supplier <span class="text-red-500">*</span></label>
              <select name="supplier_id" required
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                <option value="">Select Supplier</option>
                @foreach($suppliers as $supplier)
                  <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Store <span class="text-red-500">*</span></label>
              <select name="store_id" required
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                <option value="">Select Store</option>
                @foreach($stores as $store)
                  <option value="{{ $store->id }}" {{ auth()->user()->store_id == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Reference Number</label>
              <input name="reference_no" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="e.g., PO-2026-001" type="text" />
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Date <span class="text-red-500">*</span></label>
              <input name="date" required value="{{ date('Y-m-d') }}"
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" type="date" />
            </div>
            <div class="space-y-1.5 md:col-span-2">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Notes</label>
              <textarea name="notes" rows="2" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none resize-none" placeholder="Additional notes..."></textarea>
            </div>
          </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-base font-bold text-on-surface">Items</h3>
            <button type="button" onclick="addItem()"
              class="flex items-center px-4 py-2 bg-primary text-white font-bold rounded-lg shadow-md hover:bg-primary-container active:scale-95 transition-all text-xs cursor-pointer">
              <span class="material-symbols-outlined mr-1 text-base">add_circle</span>
              Add Item
            </button>
          </div>

          <div id="items-container" class="space-y-3">
          </div>

          <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
            <span class="text-sm font-bold text-slate-400 uppercase tracking-wider">Total Items</span>
            <span id="total-items" class="text-lg font-extrabold text-blue-900">0</span>
          </div>
        </div>

        <div class="flex gap-3">
          <a href="/stock-in" class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all text-center cursor-pointer">Cancel</a>
          <button type="submit" class="flex-2 grow bg-primary text-white py-3 px-6 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container transition-all cursor-pointer">Process Stock In</button>
        </div>
      </form>
    </div>
  </main>

  <template id="item-template">
    <div class="item-row bg-surface-container-low/30 rounded-lg p-4 border border-slate-100">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="md:col-span-2 space-y-1.5">
          <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Product</label>
          <select name="items[__INDEX__][product_id]" required
            class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none product-select">
            <option value="">Select Product</option>
            @foreach($products as $product)
              <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}">{{ $product->name }} ({{ $product->sku }})</option>
            @endforeach
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Quantity</label>
          <input name="items[__INDEX__][quantity]" required min="1" value="1"
            class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none quantity-input" type="number" />
        </div>
        <div class="space-y-1.5">
          <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Cost Price</label>
          <input name="items[__INDEX__][cost_price]" required min="0"
            class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none cost-input" type="number" step="0.01" />
        </div>
        <div class="space-y-1.5">
          <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Expired</label>
          <input name="items[__INDEX__][expired_date]"
            class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" type="date" />
        </div>
      </div>
      <div class="mt-3 flex justify-end">
        <button type="button" onclick="removeItem(this)"
          class="flex items-center px-3 py-1.5 bg-red-50 text-red-600 font-bold rounded-lg hover:bg-red-100 active:scale-95 transition-all text-xs cursor-pointer">
          <span class="material-symbols-outlined mr-1 text-sm">delete</span>
          Remove
        </button>
      </div>
    </div>
  </template>

  <script>
    let itemIndex = 0;

    function addItem() {
      const template = document.getElementById('item-template').innerHTML;
      const container = document.getElementById('items-container');
      const newRow = template.replace(/__INDEX__/g, itemIndex);
      container.insertAdjacentHTML('beforeend', newRow);
      itemIndex++;
      updateTotal();

      const newSelect = container.lastElementChild.querySelector('.product-select');
      newSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const costInput = container.lastElementChild.querySelector('.cost-input');
        if (option.dataset.cost) {
          costInput.value = option.dataset.cost;
        }
      });
    }

    function removeItem(btn) {
      btn.closest('.item-row').remove();
      updateTotal();
    }

    function updateTotal() {
      const count = document.querySelectorAll('.item-row').length;
      document.getElementById('total-items').textContent = count;
    }

    document.addEventListener('DOMContentLoaded', function() {
      addItem();
    });

    document.getElementById('stockInForm').addEventListener('submit', function(e) {
      const itemCount = document.querySelectorAll('.item-row').length;
      if (itemCount === 0) {
        e.preventDefault();
        alert('Please add at least one item.');
      }
    });
  </script>

</x-layout>
