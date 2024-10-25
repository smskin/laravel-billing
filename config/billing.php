<?php

return [
    /**
     * Table for storing billing transactions
     */
    'table' => 'billing_operations',
    /**
     * Single-threaded queue options for billing operations
     */
    'queue' => [
        /**
         * Connection
         * - sync - asynchronous execution on the main thread (sync)
         * - другое - asynchronous execution in another thread (horizon\supervisor)
         */
        'connection' => 'sync',
        'queue' => 'billing'
    ]
];
