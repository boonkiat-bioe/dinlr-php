<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ValidationException;
use Nava\Dinlr\Models\CartSummary;
use Nava\Dinlr\Models\Order as OrderModel;

class Cart extends AbstractResource
{
    protected $resourcePath = 'onlineorder/cart';

    public function calculate(array $cartData, ?string $restaurantId = null): CartSummary
    {
        $this->validateRequired($cartData, ['location', 'items']);

        if (! is_array($cartData['items']) || empty($cartData['items'])) {
            throw new ValidationException('Items must be a non-empty array');
        }

        $path     = $this->buildPath($restaurantId, 'calculate');
        $response = $this->client->request('POST', $path, $cartData);

        return new CartSummary($response['data'] ?? []);
    }

    public function submit(array $cartData, ?string $restaurantId = null): OrderModel
    {
        $this->validateRequired($cartData, ['location', 'items']);

        if (! is_array($cartData['items']) || empty($cartData['items'])) {
            throw new ValidationException('Items must be a non-empty array');
        }

        $path     = $this->buildPath($restaurantId, 'submit');
        $response = $this->client->request('POST', $path, $cartData);

        return new OrderModel($response['data'] ?? []);
    }
}
