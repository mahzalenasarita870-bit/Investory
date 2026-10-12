<?php
function validateInt($value, int $min = 1): ?int {
    $val = filter_var($value, FILTER_VALIDATE_INT);
    if ($val === false || $val < $min) {
        return null;
    }
    return $val;
}

function sanitizeString(?string $value): string {
    return trim($value ?? '');
}