const DB_NAME = 'pos-offline-db';
const DB_VERSION = 1;

class OfflineDB {
    constructor() {
        this.db = null;
    }

    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve();
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Products store
                if (!db.objectStoreNames.contains('products')) {
                    db.createObjectStore('products', { keyPath: 'id' });
                }

                // Stock store
                if (!db.objectStoreNames.contains('stocks')) {
                    const stockStore = db.createObjectStore('stocks', { keyPath: 'id' });
                    stockStore.createIndex('product_id', 'product_id', { unique: false });
                    stockStore.createIndex('store_id', 'store_id', { unique: false });
                }

                // Transactions store (pending sync)
                if (!db.objectStoreNames.contains('transactions')) {
                    const txStore = db.createObjectStore('transactions', { keyPath: 'id', autoIncrement: true });
                    txStore.createIndex('status', 'status', { unique: false });
                    txStore.createIndex('created_at', 'created_at', { unique: false });
                }

                // Sync queue store
                if (!db.objectStoreNames.contains('sync_queue')) {
                    const syncStore = db.createObjectStore('sync_queue', { keyPath: 'id', autoIncrement: true });
                    syncStore.createIndex('type', 'type', { unique: false });
                    syncStore.createIndex('status', 'status', { unique: false });
                }

                // Settings store
                if (!db.objectStoreNames.contains('settings')) {
                    db.createObjectStore('settings', { keyPath: 'key' });
                }

                // Last sync timestamp
                if (!db.objectStoreNames.contains('sync_meta')) {
                    db.createObjectStore('sync_meta', { keyPath: 'key' });
                }
            };
        });
    }

    // Generic CRUD
    async add(storeName, data) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.add(data);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async put(storeName, data) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.put(data);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async get(storeName, key) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(storeName, 'readonly');
            const store = tx.objectStore(storeName);
            const request = store.get(key);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async getAll(storeName) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(storeName, 'readonly');
            const store = tx.objectStore(storeName);
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async delete(storeName, key) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.delete(key);
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async clear(storeName) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.clear();
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    // Product methods
    async saveProduct(product) {
        return this.put('products', product);
    }

    async getProduct(id) {
        return this.get('products', id);
    }

    async getAllProducts() {
        return this.getAll('products');
    }

    async saveProducts(products) {
        for (const product of products) {
            await this.put('products', product);
        }
    }

    // Stock methods
    async saveStock(stock) {
        return this.put('stocks', stock);
    }

    async getStock(productId, storeId) {
        const stocks = await this.getAll('stocks');
        return stocks.find(s => s.product_id === productId && s.store_id === storeId);
    }

    async getAllStocks() {
        return this.getAll('stocks');
    }

    async saveStocks(stocks) {
        for (const stock of stocks) {
            await this.put('stocks', stock);
        }
    }

    // Transaction methods
    async saveTransaction(transaction) {
        transaction.status = 'pending';
        transaction.created_at = new Date().toISOString();
        transaction.offline_id = Date.now() + Math.random().toString(36).substr(2, 9);
        return this.add('transactions', transaction);
    }

    async getPendingTransactions() {
        const all = await this.getAll('transactions');
        return all.filter(t => t.status === 'pending');
    }

    async updateTransactionStatus(id, status) {
        const tx = await this.get('transactions', id);
        if (tx) {
            tx.status = status;
            tx.synced_at = new Date().toISOString();
            return this.put('transactions', tx);
        }
    }

    // Sync methods
    async getLastSyncTime(key) {
        const meta = await this.get('sync_meta', key);
        return meta ? meta.value : null;
    }

    async setLastSyncTime(key, value) {
        return this.put('sync_meta', { key, value });
    }

    async addToSyncQueue(action) {
        action.status = 'pending';
        action.created_at = new Date().toISOString();
        return this.add('sync_queue', action);
    }

    async getPendingSyncActions() {
        return this.getAll('sync_queue');
    }

    async removeSyncAction(id) {
        return this.delete('sync_queue', id);
    }

    // Settings
    async saveSetting(key, value) {
        return this.put('settings', { key, value });
    }

    async getSetting(key) {
        const setting = await this.get('settings', key);
        return setting ? setting.value : null;
    }
}

// Create global instance
window.offlineDB = new OfflineDB();

// Initialize on load
window.addEventListener('DOMContentLoaded', async () => {
    try {
        await window.offlineDB.init();
        console.log('OfflineDB initialized');
    } catch (e) {
        console.error('Failed to initialize OfflineDB:', e);
    }
});