<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>

  @if(session('success'))
    <div id="success-alert" class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-xl shadow-lg">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div id="error-alert" class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg">
      {{ session('error') }}
    </div>
  @endif

  <main class="flex-1 flex flex-col min-h-screen relative w-full">

    <header
      class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Supplier</h1>
        <form method="GET" action="/suppliers" class="relative group">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-lg text-slate-400">search</span>
          <input name="search" value="{{ request('search') }}"
            class="w-full pl-10 pr-12 py-2.5 bg-slate-100/50 border-none rounded-xl focus:ring-2 focus:ring-primary/10 transition-all text-sm outline-none"
            placeholder="Search supplier..." type="text" />
        </form>
      </div>
    </header>

    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
        <div class="mb-6 lg:mb-8">
            <x-report-header title="Supplier List" module="Inventory" submodule="Supplier Management" description="Manage your suppliers, track contact information, and view stock-in history.">
                <x-slot name="actions">
                    <button onclick="document.getElementById('addSupplierModal').classList.remove('hidden')" class="flex items-center px-4 lg:px-5 py-2 lg:py-2.5 bg-primary text-white font-bold rounded-lg shadow-md hover:bg-primary-container active:scale-95 transition-all text-xs lg:text-sm cursor-pointer">
                        <span class="material-symbols-outlined mr-1 lg:mr-2 text-base lg:text-lg">person_add</span>
                        Add Supplier
                    </button>
                </x-slot>
            </x-report-header>
        </div>

      <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-6 mb-6 lg:mb-8">
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Suppliers</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-blue-900 font-headline">{{ $suppliers->count() }}</span>
          </div>
        </div>
      </div>

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low/50">
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Supplier Name</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden md:table-cell">Contact Person</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden md:table-cell">Phone</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden lg:table-cell">Email</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              @forelse($suppliers as $supplier)
                <tr class="hover:bg-blue-50/30 transition-colors group">
                  <td class="px-3 lg:px-6 py-3 lg:py-5">
                    <div class="flex items-center gap-3 lg:gap-4">
                      <div class="w-10 lg:w-12 h-10 lg:h-12 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-primary text-lg lg:text-xl">local_shipping</span>
                      </div>
                      <div>
                        <div class="text-xs lg:text-sm font-bold text-on-surface">{{ $supplier->name }}</div>
                        @if($supplier->address)
                          <div class="text-[10px] lg:text-[11px] text-slate-400 font-medium truncate max-w-xs">{{ $supplier->address }}</div>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden md:table-cell">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">{{ $supplier->contact_name ?? '-' }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden md:table-cell">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">{{ $supplier->phone ?? '-' }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden lg:table-cell">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">{{ $supplier->email ?? '-' }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <div class="flex items-center justify-end gap-1">
                      <button onclick="openEditModal({{ $supplier->id }}, '{{ addslashes($supplier->name) }}', '{{ addslashes($supplier->contact_name ?? '') }}', '{{ addslashes($supplier->phone ?? '') }}', '{{ addslashes($supplier->email ?? '') }}', '{{ addslashes($supplier->address ?? '') }}')"
                        class="p-1.5 lg:p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                        title="Edit">
                        <span class="material-symbols-outlined text-base lg:text-sm">edit</span>
                      </button>
                      <button onclick="confirmDelete({{ $supplier->id }}, '{{ addslashes($supplier->name) }}')"
                        class="p-1.5 lg:p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                        title="Delete">
                        <span class="material-symbols-outlined text-base lg:text-sm">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-6 py-8 text-center text-slate-500">No suppliers found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div id="addSupplierModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('addSupplierModal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-lg bg-surface-container-lowest rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-outline-variant/20">
      <div class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low/30">
        <h3 class="text-lg font-bold text-on-surface">Add New Supplier</h3>
        <button onclick="document.getElementById('addSupplierModal').classList.add('hidden')" class="text-slate-400 hover:text-on-surface transition-colors cursor-pointer">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="p-6 overflow-y-auto">
        <form action="/suppliers" method="POST" class="space-y-5">
          @csrf
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Supplier Name</label>
            <input name="name" required class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="Enter supplier name" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Contact Person</label>
            <input name="contact_name" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="Enter contact person name" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Phone</label>
            <input name="phone" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="Enter phone number" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Email</label>
            <input name="email" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="Enter email address" type="email" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Address</label>
            <textarea name="address" rows="3" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none resize-none" placeholder="Enter address"></textarea>
          </div>

          <div class="pt-4 flex gap-3">
            <button type="button" onclick="document.getElementById('addSupplierModal').classList.add('hidden')" class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all cursor-pointer">Cancel</button>
            <button type="submit" class="flex-2 grow bg-primary text-white py-3 px-6 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container transition-all cursor-pointer">Save Supplier</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div id="editSupplierModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('editSupplierModal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-lg bg-surface-container-lowest rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-outline-variant/20">
      <div class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low/30">
        <h3 class="text-lg font-bold text-on-surface">Edit Supplier</h3>
        <button onclick="document.getElementById('editSupplierModal').classList.add('hidden')" class="text-slate-400 hover:text-on-surface transition-colors cursor-pointer">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="p-6 overflow-y-auto">
        <form id="editSupplierForm" method="POST" class="space-y-5">
          @csrf
          @method('PUT')
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Supplier Name</label>
            <input id="edit-name" name="name" required class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Contact Person</label>
            <input id="edit-contact_name" name="contact_name" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Phone</label>
            <input id="edit-phone" name="phone" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Email</label>
            <input id="edit-email" name="email" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" type="email" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Address</label>
            <textarea id="edit-address" name="address" rows="3" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none resize-none"></textarea>
          </div>

          <div class="pt-4 flex gap-3">
            <button type="button" onclick="document.getElementById('editSupplierModal').classList.add('hidden')" class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all cursor-pointer">Cancel</button>
            <button type="submit" class="flex-2 grow bg-primary text-white py-3 px-6 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container transition-all cursor-pointer">Update Supplier</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <form id="deleteSupplierForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
  </form>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function openEditModal(id, name, contactName, phone, email, address) {
      document.getElementById('editSupplierForm').action = '/suppliers/' + id;
      document.getElementById('edit-name').value = name;
      document.getElementById('edit-contact_name').value = contactName;
      document.getElementById('edit-phone').value = phone;
      document.getElementById('edit-email').value = email;
      document.getElementById('edit-address').value = address;
      document.getElementById('editSupplierModal').classList.remove('hidden');
    }

    function confirmDelete(id, name) {
      Swal.fire({
        title: 'Delete Supplier?',
        text: `Are you sure you want to delete "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.getElementById('deleteSupplierForm');
          form.action = '/suppliers/' + id;
          form.submit();
        }
      });
    }

    setTimeout(() => {
      const successAlert = document.getElementById('success-alert');
      const errorAlert = document.getElementById('error-alert');
      if (successAlert) successAlert.style.display = 'none';
      if (errorAlert) errorAlert.style.display = 'none';
    }, 3000);
  </script>

</x-layout>
