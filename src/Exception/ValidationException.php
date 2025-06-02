<?php
namespace Nava\Dinlr\Exception;

/**
 * Exception for validation errors
 */
class ValidationException extends ApiException
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Create a new validation exception
     *
     * @param string $message Error message
     * @param array $errors Validation errors
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message = "Validation failed", array $errors = [], int $code = 422, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * Get the validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
