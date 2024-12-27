<?php 

// Utility function to format large numbers
function format_large_number($number) {
    if ($number >= 1_000_000_000_000) {
        // Trillion
        return number_format($number / 1_000_000_000_000, 2) . ' T';
    } elseif ($number >= 1_000_000_000) {
        // Billion
        return number_format($number / 1_000_000_000, 2) . ' B';
    } elseif ($number >= 1_000_000) {
        // Million
        return number_format($number / 1_000_000, 2) . ' M';
    } else {
        // Smaller numbers stay as they are
        return number_format($number, 0);
    }
}

function format_with_minimum_decimals($number, $min_decimals = 2) {
    // Convert the number to a string
    $number_string = (string) $number;

    // Check if there are decimals
    if (strpos($number_string, '.') !== false) {
        // Get the number of decimals
        $decimal_count = strlen(substr(strrchr($number_string, '.'), 1));

        // Use the greater value between the actual decimals and the minimum
        $decimals_to_use = max($min_decimals, $decimal_count);

        return number_format($number, $decimals_to_use);
    }

    // If no decimals, format with minimum decimals
    return number_format($number, $min_decimals);
}