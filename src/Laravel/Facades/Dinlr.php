<?php
namespace Nava\Dinlr\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Nava\Dinlr\Resources\Restaurant restaurant()
 * @method static \Nava\Dinlr\Resources\Location locations()
 * @method static \Nava\Dinlr\Resources\DiningOption diningOptions()
 * @method static \Nava\Dinlr\Resources\PaymentMethod paymentMethods()
 * @method static \Nava\Dinlr\Resources\Charge charges()
 * @method static \Nava\Dinlr\Resources\Item items()
 * @method static \Nava\Dinlr\Resources\Modifier modifiers()
 * @method static \Nava\Dinlr\Resources\Category categories()
 * @method static \Nava\Dinlr\Resources\Discount discounts()
 * @method static \Nava\Dinlr\Resources\Promotion promotions()
 * @method static \Nava\Dinlr\Resources\Voucher vouchers()
 * @method static \Nava\Dinlr\Resources\Menu menu()
 * @method static \Nava\Dinlr\Resources\Customer customers()
 * @method static \Nava\Dinlr\Resources\CustomerGroup customerGroups()
 * @method static \Nava\Dinlr\Resources\Loyalty loyalty()
 * @method static \Nava\Dinlr\Resources\StoreCredit storeCredit()
 * @method static \Nava\Dinlr\Resources\Cart cart()
 * @method static \Nava\Dinlr\Resources\Order orders()
 * @method static \Nava\Dinlr\Resources\Experience experiences()
 * @method static \Nava\Dinlr\Resources\TableSection tableSections()
 * @method static \Nava\Dinlr\Resources\Reservation reservations()
 * @method static \Nava\Dinlr\Resources\Material materials()
 * @method static array request(string $method, string $endpoint, array $params = [])
 */
class Dinlr extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dinlr';
    }
}
