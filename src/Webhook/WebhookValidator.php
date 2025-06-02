<?php
namespace Nava\Dinlr\Webhook;

use Nava\Dinlr\Exception\WebhookException;

class WebhookValidator
{
    private $signingSecret;
    private $tolerance;

    public function __construct(string $signingSecret, int $tolerance = 300)
    {
        $this->signingSecret = $signingSecret;
        $this->tolerance     = $tolerance;
    }

    public function validateSignature(string $payload, string $signatureHeader): bool
    {
        $elements = $this->parseSignatureHeader($signatureHeader);

        if (! isset($elements['t']) || ! isset($elements['v1'])) {
            throw new WebhookException('Invalid signature header format');
        }

        $timestamp = $elements['t'];

        // Check if timestamp is within tolerance
        if (abs(time() - $timestamp) > $this->tolerance) {
            throw new WebhookException('Webhook timestamp is outside the tolerance zone');
        }

        $signedPayload     = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $this->signingSecret);

        return hash_equals($expectedSignature, $elements['v1']);
    }

    private function parseSignatureHeader(string $header): array
    {
        $elements = [];
        $parts    = explode(',', $header);

        foreach ($parts as $part) {
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) === 2) {
                $elements[trim($keyValue[0])] = trim($keyValue[1]);
            }
        }

        return $elements;
    }

    public function constructEvent(string $payload, string $signatureHeader): \Nava\Dinlr\Models\Webhook
    {
        if (! $this->validateSignature($payload, $signatureHeader)) {
            throw new WebhookException('Invalid webhook signature');
        }

        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new WebhookException('Invalid JSON payload');
        }

        return new \Nava\Dinlr\Models\Webhook($data);
    }
}
