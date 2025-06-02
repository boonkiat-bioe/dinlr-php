<?php
// src/Security/DateValidator.php
namespace Nava\Dinlr\Security;

use Nava\Dinlr\Exception\ValidationException;

class DateValidator
{
    /**
     * Secure date validation with range checking
     */
    public static function validateDate(string $date, string $fieldName, string $format = 'Y-m-d'): \DateTime
    {
        // First sanitize the input
        $date = InputSanitizer::sanitizeString($date, $fieldName);

        // Check for obviously malicious patterns
        if (self::containsMaliciousDatePatterns($date)) {
            throw new ValidationException("Invalid {$fieldName} format");
        }

        // Validate format strictly
        $dateObj = \DateTime::createFromFormat($format, $date);

        if (! $dateObj || $dateObj->format($format) !== $date) {
            throw new ValidationException("{$fieldName} must be in {$format} format");
        }

        // Validate reasonable date ranges
        self::validateSingleDateBounds($dateObj, $fieldName);

        return $dateObj;
    }

    /**
     * Validate ISO 8601 datetime with timezone
     */
    public static function validateDateTime(string $datetime, string $fieldName): \DateTime
    {
        $datetime = InputSanitizer::sanitizeString($datetime, $fieldName);

        if (self::containsMaliciousDatePatterns($datetime)) {
            throw new ValidationException("Invalid {$fieldName} format");
        }

        // Try to parse as ISO 8601
        $dateObj = \DateTime::createFromFormat(\DateTime::ATOM, $datetime);

        if (! $dateObj) {
            // Try alternative ISO 8601 formats
            $formats = [
                'Y-m-d\TH:i:sP',  // 2024-12-25T19:00:00+00:00
                'Y-m-d\TH:i:s\Z', // 2024-12-25T19:00:00Z
                'Y-m-d H:i:s',    // 2024-12-25 19:00:00
            ];

            foreach ($formats as $format) {
                $dateObj = \DateTime::createFromFormat($format, $datetime);
                if ($dateObj && $dateObj->format($format) === $datetime) {
                    break;
                }
                $dateObj = null;
            }
        }

        if (! $dateObj) {
            throw new ValidationException("{$fieldName} must be a valid ISO 8601 datetime");
        }

        self::validateSingleDateBounds($dateObj, $fieldName);

        return $dateObj;
    }

    /**
     * Check for malicious date patterns
     */
    private static function containsMaliciousDatePatterns(string $input): bool
    {
        $maliciousPatterns = [
            // Potential injection attempts
            '/[<>"\'\\\]/',
            // Excessive length
            '/^.{100,}$/',
            // Null bytes
            '/\x00/',
            // Control characters (except space, tab, newline)
            '/[\x01-\x08\x0B\x0C\x0E-\x1F\x7F]/',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * RENAMED: Validate single date is within reasonable bounds
     */
    private static function validateSingleDateBounds(\DateTime $date, string $fieldName): void
    {
        $now     = new \DateTime();
        $minDate = new \DateTime('1900-01-01');
        $maxDate = new \DateTime('+100 years');

        if ($date < $minDate) {
            throw new ValidationException("{$fieldName} cannot be before 1900-01-01");
        }

        if ($date > $maxDate) {
            throw new ValidationException("{$fieldName} cannot be more than 100 years in the future");
        }
    }

    /**
     * Validate date range (start before end) with duration limits
     */
    public static function validateDateRange(\DateTime $startDate, \DateTime $endDate, string $fieldPrefix = 'date', int $maxDays = 365): void
    {
        if ($startDate >= $endDate) {
            throw new ValidationException("{$fieldPrefix} start date must be before end date");
        }

        $diffDays = $startDate->diff($endDate)->days;
        if ($diffDays > $maxDays) {
            throw new ValidationException("{$fieldPrefix} range cannot exceed {$maxDays} days");
        }
    }

    /**
     * NEW: Convenience method for API date range validation
     */
    public static function validateApiDateRange(string $startDate, string $endDate, string $fieldPrefix = 'date', int $maxDays = 32): void
    {
        $start = self::validateDateTime($startDate, "{$fieldPrefix} start");
        $end   = self::validateDateTime($endDate, "{$fieldPrefix} end");

        self::validateDateRange($start, $end, $fieldPrefix, $maxDays);
    }
}
