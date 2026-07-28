class OfflineSyncManager {
    constructor() {
        this.isOnline = navigator.onLine;
        this.isSyncing = false;
        this.syncInterval = null;
        this.listeners = [];
        
        this.init();
    }

    async init() {
        // Listen for online/offline events
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
        
        // Initial sync if online
        if (this.isOnline) {
            await this.fullSync();
        }
        
        // Periodic sync every 30 seconds when online
        this.startPeriodicSync();
    }

    handleOffline() {
        this.isOnline = false;
        this.notifyListeners({ type: 'offline' });
        console.log('App is now OFFLINE');
    }

    handleOnline() {
        this.isOnline = true;
        this.notifyListeners({ type: 'online' });
        console.log('App is now ONLINE - starting sync');
        this.syncPendingTransactions();
    }

    startPeriodicSync() {
        if (this.syncInterval) clearInterval(this.syncInterval);
        this.syncInterval = setInterval(() => {
            if (this.isOnline && !this.isSyncing) {
                this.syncPendingTransactions();
            }
        }, 30000); // 30 seconds
    }

    onSyncStatusChange(callback) {
        this.listeners.push(callback);
    }

    notifyListeners(data) {
        this.listeners.forEach(cb => cb(data));
    }

    // Sync all data from server
    async fullSync() {
        if (!this.isOnline || this.isSyncing) return;
        
        this.isSyncing = true;
        this.notifyListeners({ type: 'sync_start' });

        try {
            // Get products - skip if offline
            if (!this.isOnline) {
                console.log('Offline mode - skipping full sync');
                this.notifyListeners({ type: 'sync_skipped' });
                return;
            }
            
            const productsRes = await fetch('/api/offline/products', {
                headers: { 'Accept': 'application/json' }
            });
            if (productsRes.ok) {
                const products = await productsRes.json();
                await window.offlineDB.saveProducts(products);
            }

            // Get stocks
            const stocksRes = await fetch('/api/offline/stocks', {
                headers: { 'Accept': 'application/json' }
            });
            if (stocksRes.ok) {
                const stocks = await stocksRes.json();
                await window.offlineDB.saveStocks(stocks);
            }

            // Get categories
            const categoriesRes = await fetch('/api/offline/categories', {
                headers: { 'Accept': 'application/json' }
            });
            if (categoriesRes.ok) {
                const categories = await categoriesRes.json();
                await window.offlineDB.saveSetting('categories', JSON.stringify(categories));
            }

            // Update last sync time
            await window.offlineDB.setLastSyncTime('last_full_sync', new Date().toISOString());

            console.log('Full sync completed');
            this.notifyListeners({ type: 'sync_complete' });
        } catch (e) {
            console.error('Full sync failed:', e);
            this.notifyListeners({ type: 'sync_error', error: e.message });
        } finally {
            this.isSyncing = false;
        }
    }

    // Save transaction locally (works offline)
    async saveTransaction(transaction) {
        const saved = await window.offlineDB.saveTransaction(transaction);
        
        // Try to sync immediately if online
        if (this.isOnline) {
            this.syncTransaction(saved);
        } else {
            this.notifyListeners({ 
                type: 'transaction_saved_offline', 
                offline_id: saved.offline_id 
            });
        }
        
        return saved;
    }

    // Sync a single transaction
    async syncTransaction(transaction) {
        if (!this.isOnline) {
            await window.offlineDB.addToSyncQueue({
                type: 'transaction',
                data: transaction
            });
            return false;
        }

        try {
            const response = await fetch('/api/offline/transactions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify(transaction)
            });

            if (response.ok) {
                const result = await response.json();
                await window.offlineDB.updateTransactionStatus(transaction.id, 'synced');
                this.notifyListeners({ 
                    type: 'transaction_synced', 
                    server_id: result.id 
                });
                return true;
            }
        } catch (e) {
            console.error('Transaction sync failed:', e);
        }

        // Add to queue for retry
        await window.offlineDB.addToSyncQueue({
            type: 'transaction',
            data: transaction
        });
        
        return false;
    }

    // Sync all pending transactions
    async syncPendingTransactions() {
        if (!this.isOnline || this.isSyncing) return;

        const pending = await window.offlineDB.getPendingTransactions();
        if (pending.length === 0) return;

        this.isSyncing = true;
        this.notifyListeners({ type: 'sync_start', count: pending.length });

        let synced = 0;
        let failed = 0;

        for (const tx of pending) {
            const success = await this.syncTransaction(tx);
            if (success) {
                synced++;
            } else {
                failed++;
            }
        }

        this.isSyncing = false;
        this.notifyListeners({ 
            type: 'sync_complete', 
            synced, 
            failed 
        });

        return { synced, failed };
    }

    // Save stock deduction locally
    async saveStockDeduction(productId, storeId, quantity) {
        const action = {
            type: 'stock_deduction',
            product_id: productId,
            store_id: storeId,
            quantity: quantity,
            timestamp: new Date().toISOString()
        };

        // Update local stock
        const localStock = await window.offlineDB.getStock(productId, storeId);
        if (localStock) {
            localStock.quantity = Math.max(0, localStock.quantity - quantity);
            await window.offlineDB.saveStock(localStock);
        }

        // Queue for sync
        if (this.isOnline) {
            await this.syncStockDeduction(action);
        } else {
            await window.offlineDB.addToSyncQueue(action);
        }
    }

    async syncStockDeduction(action) {
        if (!this.isOnline) return false;

        try {
            await fetch('/api/offline/stock-deduction', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify(action)
            });
            return true;
        } catch (e) {
            console.error('Stock sync failed:', e);
            return false;
        }
    }

    async getStatus() {
        const pending = await window.offlineDB.getPendingTransactions();
        const pendingSync = await window.offlineDB.getPendingSyncActions();
        const lastSync = await window.offlineDB.getLastSyncTime('last_full_sync');
        
        return {
            isOnline: this.isOnline,
            isSyncing: this.isSyncing,
            pendingTransactions: pending.length,
            pendingSync: pendingSync.length,
            lastSync: lastSync
        };
    }
}

window.offlineSyncManager = new OfflineSyncManager();