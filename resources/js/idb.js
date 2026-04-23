/**
 * SRM IndexedDB Manager
 * Handles offline data storage and local caching
 * Version 1.0.0
 */

const IDB = (() => {
    const DB_NAME = "SRM_Database";
    const DB_VERSION = 1;

    let db = null;

    // Object stores definition
    const STORES = {
        REQUETES: "requetes", // Draft and pending requêtes
        USER_DATA: "user_data", // User profile cached data
        NOTIFICATIONS: "notifications", // Local notification queue
        SYNC_QUEUE: "sync_queue", // Items pending sync
    };

    /**
     * Initialize IndexedDB
     */
    async function init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = () => {
                console.error("[IDB] Failed to open database:", request.error);
                reject(request.error);
            };

            request.onsuccess = () => {
                db = request.result;
                console.log("[IDB] Database initialized");
                resolve(db);
            };

            request.onupgradeneeded = (event) => {
                const database = event.target.result;
                console.log("[IDB] Upgrading database schema");

                // Requêtes store - Draft and pending requêtes
                if (!database.objectStoreNames.contains(STORES.REQUETES)) {
                    const requeteStore = database.createObjectStore(
                        STORES.REQUETES,
                        {
                            keyPath: "id",
                            autoIncrement: true,
                        },
                    );
                    requeteStore.createIndex("status", "status", {
                        unique: false,
                    });
                    requeteStore.createIndex("type", "type", { unique: false });
                    requeteStore.createIndex("dateCreated", "dateCreated", {
                        unique: false,
                    });
                    requeteStore.createIndex("synced", "synced", {
                        unique: false,
                    });
                    console.log("[IDB] Created requetes store");
                }

                // User data store - Cache user profile and settings
                if (!database.objectStoreNames.contains(STORES.USER_DATA)) {
                    const userStore = database.createObjectStore(
                        STORES.USER_DATA,
                        {
                            keyPath: "key",
                        },
                    );
                    console.log("[IDB] Created user_data store");
                }

                // Notifications store - Queue for notifications
                if (!database.objectStoreNames.contains(STORES.NOTIFICATIONS)) {
                    const notifStore = database.createObjectStore(
                        STORES.NOTIFICATIONS,
                        {
                            keyPath: "id",
                            autoIncrement: true,
                        },
                    );
                    notifStore.createIndex("read", "read", { unique: false });
                    notifStore.createIndex("dateReceived", "dateReceived", {
                        unique: false,
                    });
                    console.log("[IDB] Created notifications store");
                }

                // Sync queue store - Items pending sync
                if (!database.objectStoreNames.contains(STORES.SYNC_QUEUE)) {
                    const syncStore = database.createObjectStore(
                        STORES.SYNC_QUEUE,
                        {
                            keyPath: "id",
                            autoIncrement: true,
                        },
                    );
                    syncStore.createIndex("type", "type", { unique: false });
                    syncStore.createIndex("timestamp", "timestamp", {
                        unique: false,
                    });
                    syncStore.createIndex("synced", "synced", {
                        unique: false,
                    });
                    console.log("[IDB] Created sync_queue store");
                }
            };
        });
    }

    /**
     * Get database instance
     */
    async function getDB() {
        if (!db) {
            await init();
        }
        return db;
    }

    /**
     * Save a draft requête
     */
    async function saveDraftRequete(requeteData) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.REQUETES],
                "readwrite",
            );
            const store = transaction.objectStore(STORES.REQUETES);

            const data = {
                ...requeteData,
                status: "draft",
                dateCreated: Date.now(),
                dateModified: Date.now(),
                synced: false,
            };

            const request = requeteData.id ? store.put(data) : store.add(data);

            request.onsuccess = () => {
                console.log("[IDB] Draft requête saved:", request.result);
                resolve(request.result);
            };

            request.onerror = () => {
                console.error("[IDB] Error saving draft:", request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Get all draft requêtes
     */
    async function getDraftRequetes() {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.REQUETES],
                "readonly",
            );
            const store = transaction.objectStore(STORES.REQUETES);
            const index = store.index("status");

            const request = index.getAll("draft");

            request.onsuccess = () => {
                console.log(
                    "[IDB] Retrieved draft requêtes:",
                    request.result.length,
                );
                resolve(request.result);
            };

            request.onerror = () => {
                console.error("[IDB] Error retrieving drafts:", request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Get a specific requête by ID
     */
    async function getRequeteById(id) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.REQUETES],
                "readonly",
            );
            const store = transaction.objectStore(STORES.REQUETES);
            const request = store.get(id);

            request.onsuccess = () => {
                console.log("[IDB] Retrieved requête:", id);
                resolve(request.result);
            };

            request.onerror = () => {
                console.error("[IDB] Error retrieving requête:", request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Delete a requête
     */
    async function deleteRequete(id) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.REQUETES],
                "readwrite",
            );
            const store = transaction.objectStore(STORES.REQUETES);
            const request = store.delete(id);

            request.onsuccess = () => {
                console.log("[IDB] Requête deleted:", id);
                resolve();
            };

            request.onerror = () => {
                console.error("[IDB] Error deleting requête:", request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Mark requête as synced
     */
    async function markAsSynced(id, serverId) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.REQUETES],
                "readwrite",
            );
            const store = transaction.objectStore(STORES.REQUETES);

            const getRequest = store.get(id);

            getRequest.onsuccess = () => {
                const requete = getRequest.result;
                if (requete) {
                    requete.synced = true;
                    requete.serverId = serverId;
                    requete.dateSynced = Date.now();
                    requete.status = "submitted";

                    const updateRequest = store.put(requete);

                    updateRequest.onsuccess = () => {
                        console.log("[IDB] Requête marked as synced:", id);
                        resolve();
                    };

                    updateRequest.onerror = () => {
                        console.error(
                            "[IDB] Error marking as synced:",
                            updateRequest.error,
                        );
                        reject(updateRequest.error);
                    };
                } else {
                    reject(new Error("Requête not found"));
                }
            };

            getRequest.onerror = () => {
                console.error(
                    "[IDB] Error retrieving requête:",
                    getRequest.error,
                );
                reject(getRequest.error);
            };
        });
    }

    /**
     * Save user data (profile, preferences, etc.)
     */
    async function saveUserData(key, data) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.USER_DATA],
                "readwrite",
            );
            const store = transaction.objectStore(STORES.USER_DATA);

            const request = store.put({
                key,
                data,
                timestamp: Date.now(),
            });

            request.onsuccess = () => {
                console.log("[IDB] User data saved:", key);
                resolve();
            };

            request.onerror = () => {
                console.error("[IDB] Error saving user data:", request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Get user data
     */
    async function getUserData(key) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.USER_DATA],
                "readonly",
            );
            const store = transaction.objectStore(STORES.USER_DATA);
            const request = store.get(key);

            request.onsuccess = () => {
                const item = request.result;
                console.log("[IDB] Retrieved user data:", key);
                resolve(item?.data || null);
            };

            request.onerror = () => {
                console.error(
                    "[IDB] Error retrieving user data:",
                    request.error,
                );
                reject(request.error);
            };
        });
    }

    /**
     * Add to sync queue
     */
    async function addToSyncQueue(type, data) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.SYNC_QUEUE],
                "readwrite",
            );
            const store = transaction.objectStore(STORES.SYNC_QUEUE);

            const request = store.add({
                type,
                data,
                timestamp: Date.now(),
                synced: false,
                retries: 0,
            });

            request.onsuccess = () => {
                console.log("[IDB] Added to sync queue:", type, request.result);
                resolve(request.result);
            };

            request.onerror = () => {
                console.error(
                    "[IDB] Error adding to sync queue:",
                    request.error,
                );
                reject(request.error);
            };
        });
    }

    /**
     * Get pending sync items
     */
    async function getPendingSyncItems(type = null) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.SYNC_QUEUE],
                "readonly",
            );
            const store = transaction.objectStore(STORES.SYNC_QUEUE);
            const index = store.index("synced");

            const request = index.getAll(false);

            request.onsuccess = () => {
                let items = request.result;
                if (type) {
                    items = items.filter((item) => item.type === type);
                }
                console.log(
                    "[IDB] Retrieved pending sync items:",
                    items.length,
                );
                resolve(items);
            };

            request.onerror = () => {
                console.error(
                    "[IDB] Error retrieving sync items:",
                    request.error,
                );
                reject(request.error);
            };
        });
    }

    /**
     * Mark sync item as synced
     */
    async function markSyncItemAsSynced(id) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.SYNC_QUEUE],
                "readwrite",
            );
            const store = transaction.objectStore(STORES.SYNC_QUEUE);

            const getRequest = store.get(id);

            getRequest.onsuccess = () => {
                const item = getRequest.result;
                if (item) {
                    item.synced = true;
                    item.syncedAt = Date.now();

                    const updateRequest = store.put(item);

                    updateRequest.onsuccess = () => {
                        console.log("[IDB] Sync item marked as synced:", id);
                        resolve();
                    };

                    updateRequest.onerror = () => {
                        console.error(
                            "[IDB] Error updating sync item:",
                            updateRequest.error,
                        );
                        reject(updateRequest.error);
                    };
                } else {
                    reject(new Error("Sync item not found"));
                }
            };

            getRequest.onerror = () => {
                console.error(
                    "[IDB] Error retrieving sync item:",
                    getRequest.error,
                );
                reject(getRequest.error);
            };
        });
    }

    /**
     * Delete sync item
     */
    async function deleteSyncItem(id) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.SYNC_QUEUE],
                "readwrite",
            );
            const store = transaction.objectStore(STORES.SYNC_QUEUE);
            const request = store.delete(id);

            request.onsuccess = () => {
                console.log("[IDB] Sync item deleted:", id);
                resolve();
            };

            request.onerror = () => {
                console.error("[IDB] Error deleting sync item:", request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Add notification
     */
    async function addNotification(notificationData) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.NOTIFICATIONS],
                "readwrite",
            );
            const store = transaction.objectStore(STORES.NOTIFICATIONS);

            const request = store.add({
                ...notificationData,
                read: false,
                dateReceived: Date.now(),
            });

            request.onsuccess = () => {
                console.log("[IDB] Notification added:", request.result);
                resolve(request.result);
            };

            request.onerror = () => {
                console.error(
                    "[IDB] Error adding notification:",
                    request.error,
                );
                reject(request.error);
            };
        });
    }

    /**
     * Get unread notifications
     */
    async function getUnreadNotifications() {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.NOTIFICATIONS],
                "readonly",
            );
            const store = transaction.objectStore(STORES.NOTIFICATIONS);
            const index = store.index("read");

            const request = index.getAll(false);

            request.onsuccess = () => {
                console.log(
                    "[IDB] Retrieved unread notifications:",
                    request.result.length,
                );
                resolve(request.result);
            };

            request.onerror = () => {
                console.error(
                    "[IDB] Error retrieving notifications:",
                    request.error,
                );
                reject(request.error);
            };
        });
    }

    /**
     * Mark notification as read
     */
    async function markNotificationAsRead(id) {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [STORES.NOTIFICATIONS],
                "readwrite",
            );
            const store = transaction.objectStore(STORES.NOTIFICATIONS);

            const getRequest = store.get(id);

            getRequest.onsuccess = () => {
                const notification = getRequest.result;
                if (notification) {
                    notification.read = true;

                    const updateRequest = store.put(notification);

                    updateRequest.onsuccess = () => {
                        console.log("[IDB] Notification marked as read:", id);
                        resolve();
                    };

                    updateRequest.onerror = () => {
                        console.error(
                            "[IDB] Error marking notification as read:",
                            updateRequest.error,
                        );
                        reject(updateRequest.error);
                    };
                }
            };

            getRequest.onerror = () => {
                console.error(
                    "[IDB] Error retrieving notification:",
                    getRequest.error,
                );
                reject(getRequest.error);
            };
        });
    }

    /**
     * Clear all data (for testing/debugging)
     */
    async function clearAll() {
        const database = await getDB();
        return new Promise((resolve, reject) => {
            const transaction = database.transaction(
                [
                    STORES.REQUETES,
                    STORES.USER_DATA,
                    STORES.NOTIFICATIONS,
                    STORES.SYNC_QUEUE,
                ],
                "readwrite",
            );

            const stores = [
                transaction.objectStore(STORES.REQUETES),
                transaction.objectStore(STORES.USER_DATA),
                transaction.objectStore(STORES.NOTIFICATIONS),
                transaction.objectStore(STORES.SYNC_QUEUE),
            ];

            let completed = 0;

            stores.forEach((store) => {
                const request = store.clear();
                request.onsuccess = () => {
                    completed++;
                    if (completed === stores.length) {
                        console.log("[IDB] All data cleared");
                        resolve();
                    }
                };
                request.onerror = () => {
                    console.error("[IDB] Error clearing store:", request.error);
                    reject(request.error);
                };
            });
        });
    }

    // Public API
    return {
        init,
        getDB,
        saveDraftRequete,
        getDraftRequetes,
        getRequeteById,
        deleteRequete,
        markAsSynced,
        saveUserData,
        getUserData,
        addToSyncQueue,
        getPendingSyncItems,
        markSyncItemAsSynced,
        deleteSyncItem,
        addNotification,
        getUnreadNotifications,
        markNotificationAsRead,
        clearAll,
    };
})();

// Initialize IndexedDB when window loads
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        IDB.init().catch((error) => {
            console.error("[IDB] Initialization failed:", error);
        });
    });
} else {
    IDB.init().catch((error) => {
        console.error("[IDB] Initialization failed:", error);
    });
}

// Export for use in other scripts
window.IDB = IDB;
