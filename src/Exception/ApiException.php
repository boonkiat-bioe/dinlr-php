<?php
namespace Nava\Dinlr\Exception;

use Exception;

/**
 * Base exception for API errors
 */
class ApiException extends Exception
{
    /**
     * @var array|null
     */
    protected $errorData;

    /**
     * Create a new API exception
     *
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     * @param array|null $errorData Additional error data
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, ?array $errorData = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorData = $errorData;
    }

    /**
     * Get additional error data
     *
     * @return array|null
     */
    public function getErrorData(): ?array
    {
        return $this->errorData;
    }
}
