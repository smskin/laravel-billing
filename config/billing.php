<?php

return [
    /**
     * Table for storing billing transactions
     */
    'table' => 'billing_operations',

    /**
     * Rounding
     */
    'rounding' => [
        /**
         * Rounding type
         * NULL - No rounding. The raw value is written to the database
         * PHP_ROUND_HALF_UP - Rounds num away from zero when it is half way there, making 1.5 into 2 and -1.5 into -2.
         * PHP_ROUND_HALF_DOWN - Rounds num towards zero when it is half way there, making 1.5 into 1 and -1.5 into -1.
         * PHP_ROUND_HALF_EVEN - Rounds num towards the nearest even value when it is half way there, making both 1.5 and 2.5 into 2.
         * PHP_ROUND_HALF_ODD - Rounds num towards the nearest odd value when it is half way there, making 1.5 into 1 and 2.5 into 3.
         */
        'mod' => null,
        /**
         * Precision of float value
         */
        'precision' => 2
    ],
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
