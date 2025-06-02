<?php
namespace Nava\Dinlr\Exception;

/**
 * Exception for rate limit errors
 */
class RateLimitException extends ApiException
{
    /**
     * Create a new rate limit exception
     *
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message = "Rate limit exceeded", int $code = 429, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
