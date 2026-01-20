<?php

/**
 * Weather Analytics Configuration for RHD Construction Planning
 * Bangladesh-specific thresholds and settings
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Analysis Parameters
    |--------------------------------------------------------------------------
    */
    'analysis' => [
        'min_years' => 5,                    // Minimum years for statistical significance
        'default_years' => 10,               // Default analysis period
        'reliability_50_zscore' => 0.0,      // Z-score for 50% reliability (mean)
        'reliability_98_zscore' => 2.055,    // Z-score for 98% reliability
    ],

    /*
    |--------------------------------------------------------------------------
    | Rainfall Thresholds (mm) - Bangladesh Monsoon Context
    |--------------------------------------------------------------------------
    */
    'rainfall' => [
        'trace' => 0.1,                      // Trace rainfall
        'light' => 2.5,                      // Light rain - workable
        'moderate' => 10.0,                  // Moderate rain - limited work
        'heavy' => 50.0,                     // Heavy rain - stop outdoor work
        'very_heavy' => 100.0,               // Very heavy - flood risk
        'extreme' => 200.0,                  // Extreme - emergency
        'workable_daily_max' => 5.0,         // Max rain for safe construction
        'monsoon_threshold_monthly' => 200,  // Monthly threshold for monsoon
    ],

    /*
    |--------------------------------------------------------------------------
    | Temperature Thresholds (°C) - Bangladesh Climate
    |--------------------------------------------------------------------------
    */
    'temperature' => [
        // Asphalt work thresholds
        'asphalt_min' => 10.0,               // Minimum for laying asphalt
        'asphalt_max' => 45.0,               // Maximum for compaction
        'asphalt_optimal_min' => 15.0,
        'asphalt_optimal_max' => 35.0,

        // Concrete work thresholds
        'concrete_min' => 5.0,               // No work below this
        'concrete_max' => 40.0,              // Special measures above
        'concrete_optimal_min' => 10.0,
        'concrete_optimal_max' => 30.0,

        // Worker safety (Bangladesh labor standards)
        'heat_warning' => 35.0,              // Reduced hours
        'heat_danger' => 40.0,               // Suspend outdoor work
        'cold_warning' => 10.0,              // Cold weather precautions

        // General construction
        'construction_min' => 5.0,
        'construction_max' => 42.0,
        'optimal_min' => 15.0,
        'optimal_max' => 32.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Humidity Thresholds (%) - Bangladesh High Humidity Context
    |--------------------------------------------------------------------------
    */
    'humidity' => [
        'painting_max' => 85.0,              // No painting above
        'sealing_max' => 80.0,               // Bitumen sealing limit
        'prime_coat_max' => 75.0,            // Prime coat application
        'concrete_curing_optimal' => 70.0,   // Ideal for curing
        'general_work_max' => 90.0,          // General outdoor work
        'excellent_max' => 60.0,             // Excellent conditions
        'good_max' => 75.0,                  // Good conditions
    ],

    /*
    |--------------------------------------------------------------------------
    | Sunshine Thresholds (hours)
    |--------------------------------------------------------------------------
    */
    'sunshine' => [
        'min_work' => 4.0,                   // Minimum for outdoor work
        'productive_min' => 6.0,             // Good productivity
        'excellent_min' => 8.0,              // Excellent conditions
        'curing_min' => 4.0,                 // Concrete curing minimum
        'surface_drying_min' => 5.0,         // Surface treatment
    ],

    /*
    |--------------------------------------------------------------------------
    | Construction Suitability Index (CSI) Weights
    |--------------------------------------------------------------------------
    */
    'csi_weights' => [
        'rainfall' => 0.30,                  // Most critical in Bangladesh
        'temperature' => 0.25,
        'humidity' => 0.20,
        'sunshine' => 0.25,
    ],

    /*
    |--------------------------------------------------------------------------
    | CSI Rating Thresholds
    |--------------------------------------------------------------------------
    */
    'csi_ratings' => [
        'excellent' => 80,                   // 80-100
        'good' => 60,                        // 60-79
        'fair' => 40,                        // 40-59
        'poor' => 20,                        // 20-39
        'not_recommended' => 0,              // 0-19
    ],

    /*
    |--------------------------------------------------------------------------
    | Bangladesh Seasonal Definitions (Month Numbers)
    |--------------------------------------------------------------------------
    */
    'seasons' => [
        'pre_monsoon' => [3, 4, 5],          // March-May (Grisma)
        'monsoon' => [6, 7, 8, 9],           // June-September (Barsha)
        'post_monsoon' => [10, 11],          // October-November (Sharat)
        'winter' => [12, 1, 2],              // December-February (Shit)
    ],

    /*
    |--------------------------------------------------------------------------
    | Season Display Names
    |--------------------------------------------------------------------------
    */
    'season_names' => [
        'pre_monsoon' => 'Pre-Monsoon (Grisma)',
        'monsoon' => 'Monsoon (Barsha)',
        'post_monsoon' => 'Post-Monsoon (Sharat)',
        'winter' => 'Winter (Hemanta/Shit)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Work Type Suitability Criteria
    |--------------------------------------------------------------------------
    */
    'work_types' => [
        'asphalt_laying' => [
            'name' => 'Asphalt Laying',
            'min_sunshine' => 4,
            'max_rain' => 0,
            'temp_min' => 15,
            'temp_max' => 40,
            'humidity_max' => 85,
        ],
        'concrete_pouring' => [
            'name' => 'Concrete Pouring',
            'min_sunshine' => 0,
            'max_rain' => 0,
            'temp_min' => 10,
            'temp_max' => 35,
            'humidity_max' => 90,
        ],
        'prime_coat' => [
            'name' => 'Prime Coat',
            'min_sunshine' => 6,
            'max_rain' => 0,
            'temp_min' => 20,
            'temp_max' => 40,
            'humidity_max' => 75,
        ],
        'tack_coat' => [
            'name' => 'Tack Coat',
            'min_sunshine' => 4,
            'max_rain' => 0,
            'temp_min' => 15,
            'temp_max' => 45,
            'humidity_max' => 80,
        ],
        'earthwork' => [
            'name' => 'Earthwork',
            'min_sunshine' => 2,
            'max_rain' => 5,
            'temp_min' => 5,
            'temp_max' => 40,
            'humidity_max' => 95,
        ],
        'painting_marking' => [
            'name' => 'Road Marking/Painting',
            'min_sunshine' => 6,
            'max_rain' => 0,
            'temp_min' => 15,
            'temp_max' => 35,
            'humidity_max' => 70,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Month Names (English and Bangla)
    |--------------------------------------------------------------------------
    */
    'months' => [
        1 => ['en' => 'January', 'bn' => 'জানুয়ারি', 'short' => 'Jan'],
        2 => ['en' => 'February', 'bn' => 'ফেব্রুয়ারি', 'short' => 'Feb'],
        3 => ['en' => 'March', 'bn' => 'মার্চ', 'short' => 'Mar'],
        4 => ['en' => 'April', 'bn' => 'এপ্রিল', 'short' => 'Apr'],
        5 => ['en' => 'May', 'bn' => 'মে', 'short' => 'May'],
        6 => ['en' => 'June', 'bn' => 'জুন', 'short' => 'Jun'],
        7 => ['en' => 'July', 'bn' => 'জুলাই', 'short' => 'Jul'],
        8 => ['en' => 'August', 'bn' => 'আগস্ট', 'short' => 'Aug'],
        9 => ['en' => 'September', 'bn' => 'সেপ্টেম্বর', 'short' => 'Sep'],
        10 => ['en' => 'October', 'bn' => 'অক্টোবর', 'short' => 'Oct'],
        11 => ['en' => 'November', 'bn' => 'নভেম্বর', 'short' => 'Nov'],
        12 => ['en' => 'December', 'bn' => 'ডিসেম্বর', 'short' => 'Dec'],
    ],
];
