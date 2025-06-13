<?php
namespace Nava\Dinlr\Security;

use Nava\Dinlr\Exception\ValidationException;
use Normalizer;

class InputSanitizer
{
    /**
     * Sanitize string input
     */
    public static function sanitizeString(string $input, string $fieldName = 'input'): string
    {
        // Remove null bytes and control characters
        $input = str_replace("\0", '', $input);
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);

        // Trim whitespace
        $input = trim($input);

        // Check for suspicious patterns BEFORE other processing
        if (self::containsSuspiciousPatterns($input)) {
            throw new ValidationException("Invalid characters detected in {$fieldName}");
        }

        // Normalize unicode
        if (function_exists('normalizer_normalize')) {
            $input = normalizer_normalize($input, Normalizer::FORM_C);
        }

        return $input;
    }

    /**
     * Sanitize for SQL-like contexts (even though using API)
     */
    public static function sanitizeIdentifier(string $input, string $fieldName = 'identifier'): string
    {
        $input = self::sanitizeString($input, $fieldName);

        // Only allow alphanumeric, hyphens, underscores
        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $input)) {
            throw new ValidationException("{$fieldName} can only contain letters, numbers, hyphens, and underscores");
        }

        return $input;
    }

    /**
     * Sanitize email addresses
     */
    public static function sanitizeEmail(string $email, string $fieldName = 'email'): string
    {
        $email = self::sanitizeString($email, $fieldName);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (! $email) {
            throw new ValidationException("Invalid {$fieldName} format");
        }

        return $email;
    }

    /**
     * Detect suspicious patterns
     */
    private static function containsSuspiciousPatterns(string $input): bool
    {
        // Use simpler patterns to avoid ReDoS
        $suspiciousPatterns = [
            // SQL injection - simplified patterns
            '/\b(union|select|insert|delete|update|drop)\s+/i',
            '/[\'";][^-]*--/',

            // XSS patterns
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',

            // Path traversal
            '/\.\.[\\/\\\\]/',

            // Command injection
            '/[;&|`$]/',

            // Excessive length
            '/^.{1000,}$/',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
