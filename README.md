# Dinlr PHP API Client

A comprehensive PHP client library for integrating with the Dinlr Online Order API. This library provides a clean, object-oriented interface to interact with all Dinlr API endpoints including orders, customers, inventory, reservations, loyalty programs, and more.

## Table of Contents

-   [Requirements](#requirements)
-   [Installation](#installation)
-   [Quick Start](#quick-start)
-   [Authentication](#authentication)
-   [Core Features](#core-features)
-   [Available Resources](#available-resources)
-   [Advanced Usage](#advanced-usage)
-   [Laravel Integration](#laravel-integration)
-   [Webhook Handling](#webhook-handling)
-   [Error Handling](#error-handling)
-   [Testing](#testing)
-   [Contributing](#contributing)
-   [Security](#security)
-   [License](#license)
-   [Support](#support)

## Requirements

-   PHP 7.1 or higher
-   Guzzle HTTP library (^6.3|^7.0)
-   JSON extension

## Installation

Install via Composer:

```bash
composer require nava/dinlr-php
```

## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use Nava\Dinlr\Client;

// Initialize the client
$client = new Client([
    'api_key' => 'your_api_key',
    'restaurant_id' => 'your_restaurant_id',
    'debug' => true,
]);

// Get restaurant details
$restaurant = $client->restaurant()->get();
echo "Restaurant: " . $restaurant->getName() . "\n";

// List locations
$locations = $client->locations()->list();
foreach ($locations as $location) {
    echo "Location: " . $location->getName() . "\n";
}

// Get menu items for a location
if (count($locations) > 0) {
    $locationId = $locations->first()->getId();
    $items = $client->items()->list($locationId);

    echo "Found " . count($items) . " menu items\n";
}
```

## Authentication

### API Key Authentication

The simplest way to authenticate with the Dinlr API:

```php
use Nava\Dinlr\Client;

$client = new Client([
    'api_key' => 'your_api_key',
    'restaurant_id' => 'your_restaurant_id',
    'api_url' => 'https://api.dinlr.com/v1', // optional
    'timeout' => 30, // optional, default: 30 seconds
    'debug' => true, // optional, default: false
]);
```

### OAuth 2.0 Authentication

For applications that need to access multiple restaurants or require user authorization:

```php
use Nava\Dinlr\OAuthClient;
use Nava\Dinlr\OAuthConfig;

// Step 1: Initialize OAuth client
$config = new OAuthConfig([
    'client_id' => 'your_client_id',
    'client_secret' => 'your_client_secret',
    'redirect_uri' => 'https://yourapp.com/oauth/callback',
    'api_url' => 'https://api.dinlr.com/v1',
]);

$client = new OAuthClient($config);

// Step 2: Redirect user to authorization URL
$state = bin2hex(random_bytes(16)); // Generate random state
session_start();
$_SESSION['oauth_state'] = $state;

$authUrl = $client->getAuthorizationUrl($state);
header("Location: {$authUrl}");
exit;
```

#### OAuth Callback Handler

```php
// callback.php
session_start();

try {
    // Step 3: Handle the callback
    $callbackData = $client->handleCallback($_GET, $_SESSION['oauth_state']);

    // Step 4: Exchange code for access token
    $tokens = $client->getAccessToken(
        $callbackData['code'],
        $callbackData['restaurant_id']
    );

    // Step 5: Store tokens and use the client
    $_SESSION['access_token'] = $tokens['access_token'];
    $_SESSION['refresh_token'] = $tokens['refresh_token'];
    $_SESSION['restaurant_id'] = $callbackData['restaurant_id'];

    // Now you can use the client with the access token
    $client->setAccessToken($tokens['access_token']);

    // Make API calls
    $restaurant = $client->restaurant()->get();
    echo "Connected to: " . $restaurant->getName();

} catch (\Nava\Dinlr\Exception\ApiException $e) {
    echo "OAuth error: " . $e->getMessage();
}
```

## Core Features

### Restaurant & Settings Management

```php
// Get restaurant information
$restaurant = $client->restaurant()->get();
echo "Restaurant: " . $restaurant->getName() . " (" . $restaurant->getCurrency() . ")\n";
echo "Updated: " . $restaurant->getUpdatedAt() . "\n";

// Get all locations
$locations = $client->locations()->list();
foreach ($locations as $location) {
    echo "Location: " . $location->getName() . " (ID: " . $location->getId() . ")\n";
}

// Get dining options for a location
$locationId = $locations->first()->getId();
$diningOptions = $client->diningOptions()->list($locationId);

foreach ($diningOptions as $option) {
    echo "Dining Option: " . $option->getName() . " (Sort: " . $option->getSort() . ")\n";
}

// Get payment methods
$paymentMethods = $client->paymentMethods()->list($locationId);
foreach ($paymentMethods as $method) {
    echo "Payment Method: " . $method->getName() . "\n";

    // Check for additional inputs required
    $inputs = $method->getPaymentInputs();
    if (!empty($inputs)) {
        echo "  Required inputs: " . count($inputs) . "\n";
    }
}

// Get additional charges
$charges = $client->charges()->list($locationId);
foreach ($charges as $charge) {
    echo "Charge: " . $charge->getName() . "\n";

    // Check which dining options this charge applies to
    $applicableOptions = $charge->getDiningOptions();
    echo "  Applies to " . count($applicableOptions) . " dining options\n";
}
```

### Menu Management

```php
// Get menu items with detailed information
$items = $client->items()->list($locationId);

foreach ($items as $item) {
    echo "Item: " . $item->getName() . "\n";
    echo "  Description: " . ($item->getDescription() ?: 'N/A') . "\n";
    echo "  Category: " . $item->getCategory() . "\n";

    // Display variants with pricing
    $variants = $item->getVariants();
    echo "  Variants (" . count($variants) . "):\n";

    foreach ($variants as $variant) {
        $price = $variant['price'] ? '$' . $variant['price'] : 'Open price';
        echo "    - " . $variant['name'] . ": {$price}\n";

        if (!empty($variant['sku'])) {
            echo "      SKU: " . $variant['sku'] . "\n";
        }
    }

    // Display available modifiers
    $modifiers = $item->getModifiers();
    if (!empty($modifiers)) {
        echo "  Available modifiers: " . count($modifiers) . "\n";
    }
}

// Get categories with hierarchy
$categories = $client->categories()->list();

// Organize categories by hierarchy
$topLevel = [];
$subCategories = [];

foreach ($categories as $category) {
    if ($category->isTopLevel()) {
        $topLevel[] = $category;
    } else {
        $parentId = $category->getParentCategory();
        $subCategories[$parentId][] = $category;
    }
}

// Display hierarchical structure
foreach ($topLevel as $category) {
    echo "Category: " . $category->getName() . "\n";

    $categoryId = $category->getId();
    if (isset($subCategories[$categoryId])) {
        foreach ($subCategories[$categoryId] as $subCategory) {
            echo "  └─ " . $subCategory->getName() . "\n";
        }
    }
}

// Get modifiers with options
$modifiers = $client->modifiers()->list($locationId);

foreach ($modifiers as $modifier) {
    echo "Modifier: " . $modifier->getName() . "\n";
    echo "  Required: " . ($modifier->isRequired() ? 'Yes' : 'No') . "\n";
    echo "  Min selection: " . ($modifier->getMinSelection() ?: 'None') . "\n";
    echo "  Max selection: " . ($modifier->getMaxSelection() ?: 'Unlimited') . "\n";

    $options = $modifier->getModifierOptions();
    echo "  Options (" . count($options) . "):\n";

    foreach ($options as $option) {
        $price = $option['price'] > 0 ? ' (+$' . $option['price'] . ')' : ' (Free)';
        $default = $option['default_selected'] ? ' [Default]' : '';
        echo "    - " . $option['name'] . $price . $default . "\n";
    }
}

// Get complete menu structure with times
$menus = $client->menu()->list($locationId);

foreach ($menus as $menu) {
    echo "Menu: " . $menu->getName() . " (" . $menu->getItemCount() . " items)\n";

    // Display menu availability
    $times = $menu->getTimes();
    if (!empty($times)) {
        echo "  Available:\n";
        foreach ($times as $time) {
            echo "    " . $time['day'] . ": " . $time['start_time'] . " - " . $time['end_time'] . "\n";
        }
    }

    // Check availability for specific day
    $isAvailableToday = $menu->isAvailableOnDay(date('l')); // Current day
    echo "  Available today: " . ($isAvailableToday ? 'Yes' : 'No') . "\n";
}
```

### Customer Management

```php
// Create a new customer with full details
$customerData = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com',
    'phone' => '+1234567890',
    'company_name' => 'Example Corp',
    'dob' => '1990-01-15',
    'gender' => 'M',
    'address1' => '123 Main Street',
    'address2' => 'Apt 4B',
    'city' => 'New York',
    'country' => 'US',
    'postal' => '10001',
    'notes' => 'VIP customer',
    'marketing_consent_email' => true,
    'marketing_consent_text' => false,
    'marketing_consent_phone' => false,
];

$customer = $client->customers()->create($customerData);

echo "Customer created: " . $customer->getId() . "\n";
echo "Full name: " . $customer->getFullName() . "\n";
echo "Display name: " . $customer->getDisplayName() . "\n";

// Use business logic methods
if ($customer->hasCompleteProfile()) {
    echo "✓ Customer has complete profile\n";
} else {
    echo "⚠ Customer profile incomplete\n";
}

if ($customer->canReceiveMarketing('email')) {
    echo "✓ Can receive email marketing\n";
}

$age = $customer->getAge();
if ($age !== null) {
    echo "Customer age: " . $age . " years\n";

    if ($customer->isInAgeRange(18, 65)) {
        echo "✓ Customer is in target age range\n";
    }
}

// Search for customers
$searchResults = $client->customers()->search([
    'email' => 'john.doe@example.com'
]);

if (count($searchResults) > 0) {
    echo "Found customer by email\n";
}

// Search by phone
$phoneResults = $client->customers()->search([
    'phone' => '+1234567890'
]);

// Update customer information
$updatedCustomer = $client->customers()->update($customer->getId(), [
    'notes' => 'VIP customer - updated profile',
    'marketing_consent_email' => false,
]);

// Get all customers with pagination
$customers = $client->customers()->list(null, [
    'limit' => 50,
    'page' => 1,
]);

echo "Total customers retrieved: " . count($customers) . "\n";

// Analyze customer data
foreach ($customers as $customer) {
    $summary = $customer->getSummary();
    echo "Customer: " . $summary['display_name'] . "\n";
    echo "  Complete profile: " . ($summary['complete_profile'] ? 'Yes' : 'No') . "\n";
    echo "  Marketing consents: " . $summary['marketing_consents'] . "\n";
}

// Get customer groups
$customerGroups = $client->customerGroups()->list();
foreach ($customerGroups as $group) {
    echo "Customer Group: " . $group->getName() . " (ID: " . $group->getId() . ")\n";
}
```

### Order Processing & Cart Management

```php
// Calculate cart total before placing order
$cartData = [
    'location' => $locationId,
    'items' => [
        [
            'item' => $itemId,
            'variant' => $variantId,
            'qty' => 2,
            'modifier_options' => [
                [
                    'modifier_option' => $modifierOptionId,
                    'qty' => 1,
                ],
            ],
            'notes' => 'Extra spicy please',
        ],
    ],
    'discounts' => [
        [
            'discount' => $discountId,
            'value' => 10.00, // For open discounts
        ],
    ],
    'charges' => [
        [
            'charge' => $chargeId,
            'amount' => 5.00,
        ],
    ],
];

// Calculate cart summary
$summary = $client->cart()->calculate($cartData);

echo "Cart Summary:\n";
echo "Subtotal: $" . $summary->getSubtotal() . "\n";
echo "Total: $" . $summary->getTotal() . "\n";
echo "Financial status: " . $summary->getFinancialStatus() . "\n";

// Display item details
$items = $summary->getItems();
foreach ($items as $item) {
    echo "Item: " . $item['item'] . " x" . $item['qty'] . " = $" . $item['price'] . "\n";

    // Show modifier options
    if (!empty($item['modifier_options'])) {
        foreach ($item['modifier_options'] as $modOption) {
            echo "  + Modifier x" . $modOption['qty'] . " = $" . $modOption['price'] . "\n";
        }
    }
}

// Display discounts applied
$discounts = $summary->getDiscounts();
foreach ($discounts as $discount) {
    echo "Discount: " . $discount['name'] . " = -$" . $discount['amount'] . "\n";
}

// Display charges
$charges = $summary->getCharges();
foreach ($charges as $charge) {
    echo "Charge: " . $charge['name'] . " = +$" . $charge['amount'] . "\n";
}

// Place the order
$orderData = $cartData;
$orderData['order_info'] = [
    'dining_option' => $diningOptionId,
    'order_no' => 'ORD' . time(),
    'pax' => 2,
    'customer' => $customerId,
    'notes' => 'Please prepare quickly',
    'status' => 'pending',
];

$order = $client->cart()->submit($orderData);

echo "Order placed successfully!\n";
echo "Order ID: " . $order->getId() . "\n";
echo "Order Number: " . $order->getOrderNumber() . "\n";
echo "Total: $" . $order->getTotal() . "\n";
echo "Status: " . $order->getStatus() . "\n";
```

### Advanced Order Management

```php
// List orders with filtering
$orders = $client->orders()->list(null, [
    'status' => 'open',
    'financial_status' => 'paid',
    'location_id' => $locationId,
    'detail' => 'all', // Include full order details
    'limit' => 25,
]);

echo "Found " . count($orders) . " orders\n";

// Analyze orders using collection methods
$openOrders = $orders->getByStatus('open');
$paidOrders = $orders->getByFinancialStatus('paid');
$totalRevenue = $orders->getTotalRevenue();

echo "Open orders: " . count($openOrders) . "\n";
echo "Paid orders: " . count($paidOrders) . "\n";
echo "Total revenue: $" . $totalRevenue . "\n";

// Get detailed order information
$order = $client->orders()->get($orderId);

echo "Order Details:\n";
echo "Customer: " . ($order->getCustomerId() ?: 'Walk-in') . "\n";
echo "Dining Option: " . $order->getDiningOptionName() . "\n";
echo "Pax: " . $order->getPax() . "\n";
echo "Notes: " . ($order->getNotes() ?: 'None') . "\n";

// Check order status
if ($order->isPaid()) {
    echo "✓ Order is fully paid\n";
} elseif ($order->isPartiallyPaid()) {
    echo "⚠ Order is partially paid\n";
} else {
    echo "❌ Order is unpaid\n";
}

// Display order items
$items = $order->getItems();
foreach ($items as $item) {
    echo "Item: " . $item['name'] . " x" . $item['qty'] . "\n";
    echo "  Price: $" . $item['price'] . "\n";
    echo "  Notes: " . ($item['notes'] ?: 'None') . "\n";
}

// Add payment to order
$paymentData = [
    'payment' => $paymentMethodId,
    'amount' => 50.00,
    'receipt_no' => 'RCP' . time(),
    'payment_inputs' => [
        [
            'payment_input' => $paymentInputId,
            'value' => 'Additional payment info',
        ],
    ],
];

$updatedOrder = $client->orders()->addPayment($orderId, $paymentData);

// Manage order status
$client->orders()->close($orderId);          // Close order
$client->orders()->reopen($orderId);         // Reopen if needed
$client->orders()->setPending($orderId);     // Set to pending
$client->orders()->setPendingPayment($orderId); // Waiting for payment

// Kitchen and expedite management (requires KDS subscription)
$orderItemId = $items[0]['id'];

// Kitchen workflow
$client->orders()->setItemKitchenStatusPending($orderId, $orderItemId);
$client->orders()->setItemKitchenStatusFulfilled($orderId, $orderItemId);
$client->orders()->setItemKitchenStatusDefault($orderId, $orderItemId);

// Expedite workflow
$client->orders()->setItemExpediteStatusPending($orderId, $orderItemId);
$client->orders()->setItemExpediteStatusExpedited($orderId, $orderItemId);
$client->orders()->setItemExpediteStatusDefault($orderId, $orderItemId);

// Date range filtering
$startDate = (new DateTime('-7 days'))->format('c');
$endDate = (new DateTime())->format('c');

$recentOrders = $client->orders()->listByDateRange($startDate, $endDate);
echo "Orders in last 7 days: " . count($recentOrders) . "\n";
```

### Loyalty Program Management

```php
// Get all loyalty programs
$programs = $client->loyalty()->getPrograms();

foreach ($programs as $program) {
    echo "Loyalty Program: " . $program->getName() . "\n";
    echo "  Terms: " . $program->getTermSingle() . "/" . $program->getTermPlural() . "\n";

    // Get program rewards
    $rewards = $client->loyalty()->getRewards($program->getId());
    echo "  Rewards available: " . count($rewards) . "\n";

    foreach ($rewards as $reward) {
        echo "    - " . $reward->getName() . ": " . $reward->getPoint() . " " .
             $program->getPointTerm($reward->getPoint()) . "\n";
    }
}

// Work with a specific program
$programId = $programs->first()->getId();

// Enroll a customer
$memberData = ['customer' => $customerId];
$member = $client->loyalty()->enrolMember($programId, $memberData);

echo "Member enrolled:\n";
echo "Member ID: " . $member->getId() . "\n";
echo "Starting points: " . $member->getPoint() . "\n";

// Award points for an order
$transaction = $client->loyalty()->awardPointsForOrder(
    $programId,
    $member->getId(),
    $orderId,
    100, // points to award
    $locationId
);

echo "Points awarded: " . $transaction->getPoint() . "\n";

// Add points manually
$addTransaction = $client->loyalty()->addPoints(
    $programId,
    $member->getId(),
    50,
    'Welcome bonus',
    $locationId
);

// Redeem points for reward
$reward = $rewards->first();
if ($member->hasSufficientPoints($reward->getPoint())) {
    $redeemTransaction = $client->loyalty()->redeemReward(
        $programId,
        $member->getId(),
        $reward->getId(),
        $reward->getPoint(),
        $locationId
    );

    echo "Reward redeemed: " . $reward->getName() . "\n";
}

// Get member transactions
$transactions = $client->loyalty()->getMemberTransactions($programId, $member->getId());

echo "Member transaction history:\n";
foreach ($transactions as $transaction) {
    $type = $transaction->isPointAddition() ? 'Added' : 'Redeemed';
    echo "  " . $type . ": " . $transaction->getAbsolutePoints() . " points\n";
    echo "    Date: " . $transaction->getCreatedAt() . "\n";
    echo "    Notes: " . ($transaction->getNotes() ?: 'N/A') . "\n";
}

// Get all members
$members = $client->loyalty()->getMembers($programId);
$totalPoints = $members->getTotalPoints();

echo "Total members: " . count($members) . "\n";
echo "Total points in circulation: " . $totalPoints . "\n";
```

### Store Credit Management

```php
// Get customer store credit balance
$balance = $client->storeCredit()->getCustomerBalance($customerId);

echo "Store Credit Balance:\n";
echo "Customer: " . $balance->getId() . "\n";
echo "Balance: $" . $balance->getStoreCredit() . "\n";
echo "Has credit: " . ($balance->hasStoreCredit() ? 'Yes' : 'No') . "\n";

// Check if customer can afford a purchase
$purchaseAmount = 25.00;
if ($balance->hasSufficientCredit($purchaseAmount)) {
    echo "✓ Customer can afford $" . $purchaseAmount . " purchase\n";
} else {
    echo "❌ Insufficient store credit\n";
}

// Add store credit
$creditTransaction = $client->storeCredit()->addCredit(
    $customerId,
    100.00,
    'Refund for cancelled order',
    $locationId
);

echo "Credit added:\n";
echo "Amount: $" . $creditTransaction->getAmount() . "\n";
echo "Transaction ID: " . $creditTransaction->getId() . "\n";

// Deduct store credit
$debitTransaction = $client->storeCredit()->deductCredit(
    $customerId,
    25.50,
    'Used for order payment',
    $locationId
);

// Create store credit topup
$topupData = [
    'customer' => $customerId,
    'topup_no' => 'TOP' . time(),
    'topup_amount' => 100.00,
    'payment' => $paymentMethodId,
    'payment_amount' => 90.00, // 10% bonus
];

$topup = $client->storeCredit()->createTopup($topupData);

echo "Topup created:\n";
echo "Topup amount: $" . $topup->getTopupAmount() . "\n";
echo "Payment amount: $" . $topup->getPaymentAmount() . "\n";
echo "Bonus amount: $" . $topup->getBonusAmount() . "\n";
echo "Has bonus: " . ($topup->hasBonus() ? 'Yes' : 'No') . "\n";

// Search store credit transactions
$transactions = $client->storeCredit()->searchTransactions([
    'customer_id' => $customerId,
    'limit' => 10,
]);

echo "Customer transaction history:\n";
foreach ($transactions as $transaction) {
    $type = $transaction->isCreditAddition() ? 'Credit' : 'Debit';
    echo "  " . $type . ": $" . $transaction->getAbsoluteAmount() . "\n";
    echo "    Date: " . $transaction->getCreatedAt() . "\n";
    echo "    Notes: " . ($transaction->getNotes() ?: 'N/A') . "\n";
}

// Analyze transactions using collection methods
$creditAdditions = $transactions->getCreditAdditions();
$creditDeductions = $transactions->getCreditDeductions();
$totalCreditAmount = $transactions->getTotalCreditAmount();

echo "Statistics:\n";
echo "  Credit additions: " . count($creditAdditions) . "\n";
echo "  Credit deductions: " . count($creditDeductions) . "\n";
echo "  Net amount: $" . $totalCreditAmount . "\n";
```

### Reservations Management

```php
// Get available reservation times
$date = (new DateTime('+1 day'))->format('Y-m-d');
$adult = 4;
$children = 2;

$services = $client->reservations()->getAvailableServices($locationId, $date, $adult, $children);

echo "Available services for " . $date . ":\n";
foreach ($services as $service) {
    echo "Service: " . $service->getName() . "\n";
    echo "  Experience: " . $service->getExperienceId() . "\n";
    echo "  Table Section: " . $service->getTableSectionId() . "\n";

    $availableTimes = $service->getAvailableTimes();
    echo "  Available times: " . count($availableTimes) . "\n";

    foreach ($availableTimes as $time) {
        echo "    - " . $time['time'] . "\n";
    }
}

// Book a reservation
if (count($services) > 0 && $services->first()->hasAvailability()) {
    $service = $services->first();
    $availableTimes = $service->getAvailableTimes();

    $reservationData = [
        'location' => $locationId,
        'objects' => [
            [
                'object' => 'table_1',
                'pax' => 6,
            ],
        ],
        'reservation_info' => [
            'reservation_no' => 'RES' . time(),
            'reservation_time' => $availableTimes[0]['time'],
            'service' => $service->getId(),
            'customer' => $customerId,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'pax' => 6,
            'adult' => $adult,
            'children' => $children,
            'notes' => 'Special occasion dinner',
            'confirm_by' => 'restaurant',
        ],
    ];

    $reservation = $client->reservations()->book($reservationData);

    echo "Reservation booked:\n";
    echo "Reservation ID: " . $reservation->getId() . "\n";
    echo "Reservation Number: " . $reservation->getReservationNumber() . "\n";
    echo "Time: " . $reservation->getReservationTime() . "\n";
    echo "Pax: " . $reservation->getPax() . "\n";
    echo "Status: " . $reservation->getStatus() . "\n";
    echo "Requires deposit: " . ($reservation->requiresDeposit() ? 'Yes' : 'No') . "\n";
}

// List all reservations
$reservations = $client->reservations()->list();

echo "All reservations: " . count($reservations) . "\n";

// Filter reservations using collection methods
$bookedReservations = $reservations->getByStatus('booked');
$upcomingReservations = $reservations->getUpcoming();
$totalPax = $reservations->getTotalPax();

echo "Booked: " . count($bookedReservations) . "\n";
echo "Upcoming: " . count($upcomingReservations) . "\n";
echo "Total guests expected: " . $totalPax . "\n";

// Get experiences and table sections
$experiences = $client->experiences()->list($locationId);
foreach ($experiences as $experience) {
    echo "Experience: " . $experience->getName() . " (Sort: " . $experience->getSort() . ")\n";
}

$tableSections = $client->tableSections()->list($locationId);
foreach ($tableSections as $section) {
    echo "Table Section: " . $section->getName() . "\n";
}
```

### Discounts, Promotions & Vouchers

```php
// Get discounts
$discounts = $client->discounts()->list($locationId);

foreach ($discounts as $discount) {
    echo "Discount: " . $discount->getName() . "\n";
    echo "  Type: " . $discount->getType() . "\n";
    echo "  Stackable: " . ($discount->isStackable() ? 'Yes' : 'No') . "\n";
    echo "  Manufacturer discount: " . ($discount->isManufacturerDiscount() ? 'Yes' : 'No') . "\n";

    if ($discount->isOpenDiscount()) {
        echo "  Value: Open (custom value)\n";
    } else {
        echo "  Value: " . $discount->getValue();
        echo $discount->isPercentDiscount() ? "%" : " (fixed amount)\n";
    }

    if ($discount->getMaxValue()) {
        echo "  Max value: " . $discount->getMaxValue() . "\n";
    }
}

// Get promotions
$promotions = $client->promotions()->list($locationId);

foreach ($promotions as $promotion) {
    echo "Promotion: " . $promotion->getName() . "\n";
    echo "  Summary: " . $promotion->getSummary() . "\n";
    echo "  Type: " . $promotion->getType() . "\n";
    echo "  Start: " . $promotion->getStartDate() . "\n";
    echo "  End: " . ($promotion->getEndDate() ?: 'No end date') . "\n";
    echo "  Active: " . ($promotion->isActive() ? 'Yes' : 'No') . "\n";
    echo "  Auto promotion: " . ($promotion->isAutoPromotion() ? 'Yes' : 'No') . "\n";
}

// Create a voucher
$voucherData = [
    'voucher_code' => 'SAVE20',
    'type' => 'discount',
    'discount' => $discounts->first()->getId(),
    'max_redemptions' => 100,
    'start_date' => (new DateTime())->format('c'),
    'end_date' => (new DateTime('+30 days'))->format('c'),
];

$voucher = $client->vouchers()->create($voucherData);

echo "Voucher created:\n";
echo "Code: " . $voucher->getVoucherCode() . "\n";
echo "Type: " . $voucher->getType() . "\n";
echo "Max redemptions: " . $voucher->getMaxRedemptions() . "\n";

// Search for vouchers
$voucherSearch = $client->vouchers()->search([
    'voucher_code' => 'SAVE20',
]);

if (count($voucherSearch) > 0) {
    $foundVoucher = $voucherSearch->first();
    echo "Found voucher: " . $foundVoucher->getVoucherCode() . "\n";
    echo "Can be redeemed: " . ($foundVoucher->canBeRedeemed() ? 'Yes' : 'No') . "\n";
    echo "Redeemed: " . $foundVoucher->getRedeemed() . " times\n";
}

// Create customer-specific voucher
$customerVoucherData = [
    'voucher_code' => 'VIP' . $customerId,
    'applicable' => 'customer',
    'customer' => $customerId,
    'type' => 'discount',
    'discount' => $discounts->first()->getId(),
    'max_redemptions' => 1,
    'start_date' => (new DateTime())->format('c'),
    'end_date' => (new DateTime('+7 days'))->format('c'),
];

$customerVoucher = $client->vouchers()->create($customerVoucherData);
echo "Customer-specific voucher created: " . $customerVoucher->getVoucherCode() . "\n";
```

### Inventory Management

```php
// Get materials
$materials = $client->materials()->list($locationId);

echo "Materials (" . count($materials) . "):\n";
foreach ($materials as $material) {
    echo "Material: " . $material->getName() . "\n";
    echo "  SKU: " . ($material->getSku() ?: 'N/A') . "\n";
    echo "  Unit: " . $material->getUnit() . "\n";
    echo "  Has SKU: " . ($material->hasSku() ? 'Yes' : 'No') . "\n";
}

// Get current stock levels
$stockLevels = $client->materials()->getStockLevels($locationId);

echo "Stock Analysis:\n";
echo "Total entries: " . count($stockLevels) . "\n";
echo "Total quantity: " . $stockLevels->getTotalQuantity() . "\n";

// Analyze stock status
$outOfStock = $stockLevels->getOutOfStock();
$lowStock = $stockLevels->getLowStock(10); // Items with 10 or fewer units

echo "Out of stock: " . count($outOfStock) . " items\n";
echo "Low stock (≤10): " . count($lowStock) . " items\n";

// Display stock details
foreach ($stockLevels as $stock) {
    echo "Material " . $stock->getMaterialId() . ": " . $stock->getQuantity() . " units\n";

    if ($stock->isOutOfStock()) {
        echo "  ❌ OUT OF STOCK\n";
    } elseif ($stock->isLowStock(10)) {
        echo "  ⚠ LOW STOCK\n";
    } else {
        echo "  ✓ In stock\n";
    }
}

// Get stock takes
$stockTakes = $client->materials()->getStockTakes();

echo "Stock Takes:\n";
$ongoing = $stockTakes->getOngoing();
$completed = $stockTakes->getCompleted();

echo "Ongoing: " . count($ongoing) . "\n";
echo "Completed: " . count($completed) . "\n";

foreach ($stockTakes as $stockTake) {
    echo "Stock Take: " . $stockTake->getId() . "\n";
    echo "  Location: " . $stockTake->getLocationId() . "\n";
    echo "  Status: " . ($stockTake->isOngoing() ? 'Ongoing' : 'Completed') . "\n";
    echo "  Start: " . $stockTake->getStartDate() . "\n";
    echo "  End: " . ($stockTake->getEndDate() ?: 'In progress') . "\n";
    echo "  Materials counted: " . $stockTake->getMaterialCount() . "\n";

    if ($stockTake->isCompleted()) {
        $duration = $stockTake->getDuration();
        echo "  Duration: " . round($duration / 3600, 2) . " hours\n";
    }

    echo "  Notes: " . ($stockTake->getNotes() ?: 'None') . "\n";
}

// Find materials by unit type
$materialsByUnit = [];
foreach ($materials as $material) {
    $unit = $material->getUnit();
    $materialsByUnit[$unit] = ($materialsByUnit[$unit] ?? 0) + 1;
}

echo "Materials by unit type:\n";
foreach ($materialsByUnit as $unit => $count) {
    echo "  " . $unit . ": " . $count . " items\n";
}
```

## Available Resources

The Dinlr PHP client provides access to all API resources through dedicated classes:

### Core Resources

| Resource      | Description                              | Key Methods                              |
| ------------- | ---------------------------------------- | ---------------------------------------- |
| Restaurant    | Restaurant information                   | get()                                    |
| Location      | Restaurant locations                     | list(), get($locationId)                 |
| DiningOption  | Dining options (dine-in, takeaway, etc.) | list($locationId), get($diningOptionId)  |
| PaymentMethod | Available payment methods                | list($locationId), get($paymentMethodId) |
| Charge        | Additional charges                       | list($locationId), get($chargeId)        |

### Menu & Inventory

| Resource  | Description             | Key Methods                                    |
| --------- | ----------------------- | ---------------------------------------------- |
| Item      | Menu items              | list($locationId), get($itemId)                |
| Category  | Item categories         | list(), get($categoryId)                       |
| Modifier  | Item modifiers          | list($locationId), get($modifierId)            |
| Menu      | Complete menu structure | list($locationId)                              |
| Material  | Inventory materials     | list($locationId), getStockLevels($locationId) |
| Floorplan | Table and seating plans | list($locationId), get($floorplanId)           |

### Customer & Loyalty

| Resource      | Description             | Key Methods                                                                                                      |
| ------------- | ----------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Customer      | Customer management     | list(), get($customerId), create($data), update($customerId, $data), search($params)                             |
| CustomerGroup | Customer segmentation   | list(), get($groupId)                                                                                            |
| Loyalty       | Loyalty programs        | getPrograms(), getRewards($programId), enrolMember($programId, $data), addPoints($programId, $memberId, $points) |
| StoreCredit   | Store credit management | getCustomerBalance($customerId), addCredit($customerId, $amount), createTopup($data)                             |

### Orders & Transactions

| Resource  | Description                          | Key Methods                                                                                  |
| --------- | ------------------------------------ | -------------------------------------------------------------------------------------------- |
| Cart      | Cart calculation and order placement | calculate($cartData), submit($cartData)                                                      |
| Order     | Order management                     | list(), get($orderId), update($orderId, $data), addPayment($orderId, $data), close($orderId) |
| Discount  | Discount management                  | list($locationId), get($discountId)                                                          |
| Promotion | Promotional campaigns                | list($locationId), get($promotionId)                                                         |
| Voucher   | Voucher creation and management      | list(), get($voucherId), create($data), search($params)                                      |

### Reservations

| Resource     | Description              | Key Methods                                                                                           |
| ------------ | ------------------------ | ----------------------------------------------------------------------------------------------------- |
| Experience   | Dining experiences       | list($locationId), get($experienceId)                                                                 |
| TableSection | Table section management | list($locationId), get($tableSectionId)                                                               |
| Reservation  | Reservation booking      | getAvailableServices($locationId, $date, $adult, $children), book($data), list(), get($reservationId) |

## Advanced Usage

### Custom Configuration

```php
use Nava\Dinlr\Config;
use Nava\Dinlr\Client;

$config = new Config([
    'api_key' => 'your_key',
    'restaurant_id' => 'your_id',
    'api_url' => 'https://api.dinlr.com/v1',
    'timeout' => 60,
    'debug' => true,
]);

$client = new Client($config);
```

### Using Different Restaurant IDs

```php
// Override restaurant ID for specific calls
$orders = $client->orders()->list('different_restaurant_id');
$customer = $client->customers()->get($customerId, 'different_restaurant_id');
```

### Model Helper Methods

Models include business logic methods beyond basic getters:

```php
// Customer business logic
$customer = $client->customers()->get($customerId);

if (!$customer->hasCompleteProfile()) {
    // Request missing information
}

if ($customer->canReceiveMarketing('email')) {
    // Send promotional emails
}

if ($customer->isInAgeRange(18, 65)) {
    // Show age-appropriate content
}

// Order business logic
$order = $client->orders()->get($orderId);
if ($order->isPaid() && $order->isOpen()) {
    // Ready for fulfillment
}

// Voucher validation
$voucher = $client->vouchers()->get($voucherId);
if ($voucher->canBeRedeemed()) {
    // Apply voucher to cart
}
```

### Pagination and Filtering

```php
// Paginated results
$customers = $client->customers()->list(null, [
    'limit' => 50,
    'page' => 2,
]);

// Date filtering
$orders = $client->orders()->list(null, [
    'created_at_min' => '2024-01-01T00:00:00Z',
    'created_at_max' => '2024-12-31T23:59:59Z',
    'status' => 'open',
    'detail' => 'all',
]);

// Location-specific data
$items = $client->items()->list($locationId);
$orders = $client->orders()->listForLocation($locationId);
```

### Collection Methods

Collections provide convenient methods for data analysis:

```php
// Customer collection
$customers = $client->customers()->list();
$vipCustomers = $customers->filter(function($customer) {
    return $customer->hasAttribute('vip_status');
});

// Order collection analysis
$orders = $client->orders()->list();
$openOrders = $orders->getByStatus('open');
$totalRevenue = $orders->getTotalRevenue();

// Loyalty member collection
$members = $client->loyalty()->getMembers($programId);
$totalPoints = $members->getTotalPoints();
```

## Laravel Integration

### Installation

Add the service provider to `config/app.php`:

```php
'providers' => [
    // Other service providers...
    Nava\Dinlr\Laravel\DinlrServiceProvider::class,
],

'aliases' => [
    // Other aliases...
    'Dinlr' => Nava\Dinlr\Laravel\Facades\Dinlr::class,
],
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="Nava\Dinlr\Laravel\DinlrServiceProvider"
```

### Configuration

Add to your `.env` file:

```
DINLR_API_KEY=your_api_key
DINLR_RESTAURANT_ID=your_restaurant_id
DINLR_API_URL=https://api.dinlr.com/v1
DINLR_TIMEOUT=30
DINLR_DEBUG=false
```

### Usage in Laravel

```php
use Dinlr;

class OrderController extends Controller
{
    public function index()
    {
        // Use the facade
        $restaurant = Dinlr::restaurant()->get();
        $customers = Dinlr::customers()->list();

        return view('orders.index', compact('restaurant', 'customers'));
    }

    public function placeOrder(Request $request)
    {
        $cartData = [
            'location' => $request->location_id,
            'items' => $request->items,
            'order_info' => [
                'order_no' => 'WEB' . time(),
                'customer' => $request->customer_id,
                'notes' => $request->notes,
            ],
        ];

        try {
            $order = Dinlr::cart()->submit($cartData);

            return response()->json([
                'success' => true,
                'order_id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
            ]);
        } catch (\Nava\Dinlr\Exception\ApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
```

### OAuth Configuration for Laravel

For OAuth, publish the OAuth config:

```bash
php artisan vendor:publish --provider="Nava\Dinlr\Laravel\OAuthServiceProvider"
```

Add OAuth settings to `.env`:

```
DINLR_CLIENT_ID=your_client_id
DINLR_CLIENT_SECRET=your_client_secret
DINLR_REDIRECT_URI=https://yourapp.com/oauth/callback
```

### OAuth Controller Example

```php
use Nava\Dinlr\Laravel\Facades\DinlrOAuth;

class OAuthController extends Controller
{
    public function authorize()
    {
        $state = Str::random(40);
        session(['oauth_state' => $state]);

        $authUrl = DinlrOAuth::getAuthorizationUrl($state);

        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        try {
            $callbackData = DinlrOAuth::handleCallback(
                $request->all(),
                session('oauth_state')
            );

            $tokens = DinlrOAuth::getAccessToken(
                $callbackData['code'],
                $callbackData['restaurant_id']
            );

            // Store tokens in session or database
            session([
                'dinlr_access_token' => $tokens['access_token'],
                'dinlr_refresh_token' => $tokens['refresh_token'],
                'dinlr_restaurant_id' => $callbackData['restaurant_id'],
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Successfully connected to Dinlr!');

        } catch (\Nava\Dinlr\Exception\ApiException $e) {
            return redirect()->route('oauth.authorize')
                ->with('error', 'OAuth failed: ' . $e->getMessage());
        }
    }
}
```

## Webhook Handling

The library includes comprehensive webhook handling capabilities:

```php
use Nava\Dinlr\Webhook\WebhookValidator;
use Nava\Dinlr\Exception\WebhookException;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $validator = new WebhookValidator(config('dinlr.webhook_secret'));

        try {
            $event = $validator->constructEvent(
                $request->getContent(),
                $request->header('Dinlr-Signature')
            );

            // Handle different event types
            if ($event->isOrderEvent()) {
                $this->handleOrderEvent($event);
            } elseif ($event->isCustomerEvent()) {
                $this->handleCustomerEvent($event);
            }

            return response('Webhook handled', 200);

        } catch (WebhookException $e) {
            Log::warning('Invalid webhook received', [
                'error' => $e->getMessage(),
                'signature' => $request->header('Dinlr-Signature'),
            ]);

            return response('Invalid webhook', 400);
        }
    }

    private function handleOrderEvent($event)
    {
        $orderData = $event->getData();

        if ($event->isCreateEvent()) {
            // Handle new order
            Log::info('New order received', ['order_id' => $orderData['id']]);

            // Trigger notifications, update inventory, etc.
            event(new OrderCreated($orderData));

        } elseif ($event->isUpdateEvent()) {
            // Handle order update
            Log::info('Order updated', ['order_id' => $orderData['id']]);

            // Update local database, notify staff, etc.
            event(new OrderUpdated($orderData));
        }
    }

    private function handleCustomerEvent($event)
    {
        $customerData = $event->getData();

        if ($event->isCreateEvent()) {
            // Handle new customer registration
            event(new CustomerRegistered($customerData));
        }
    }
}
```

## Error Handling

The library provides comprehensive error handling with specific exception types:

```php
use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Exception\ValidationException;
use Nava\Dinlr\Exception\ConfigException;
use Nava\Dinlr\Exception\RateLimitException;
use Nava\Dinlr\Exception\WebhookException;

try {
    // API operations that might fail
    $customer = $client->customers()->create($customerData);
    $order = $client->cart()->submit($cartData);

} catch (ValidationException $e) {
    // Handle validation errors
    echo "Validation failed: " . $e->getMessage() . "\n";

    $errors = $e->getErrors();
    foreach ($errors as $field => $error) {
        echo "  {$field}: {$error}\n";
    }

} catch (RateLimitException $e) {
    // Handle rate limiting
    echo "Rate limit exceeded. Waiting before retry...\n";
    sleep(60); // Wait 60 seconds

    // Implement exponential backoff
    $retryAfter = $e->getErrorData()['retry_after'] ?? 60;
    sleep($retryAfter);

} catch (ApiException $e) {
    // Handle general API errors
    echo "API Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getCode() . "\n";

    $errorData = $e->getErrorData();
    if ($errorData) {
        echo "Additional info: " . json_encode($errorData, JSON_PRETTY_PRINT) . "\n";
    }

    // Handle specific status codes
    switch ($e->getCode()) {
        case 401:
            echo "Authentication failed. Check your API key.\n";
            break;
        case 403:
            echo "Access forbidden. Check your permissions.\n";
            break;
        case 404:
            echo "Resource not found.\n";
            break;
        case 422:
            echo "Validation error. Check your data.\n";
            break;
        case 500:
            echo "Server error. Try again later.\n";
            break;
    }

} catch (ConfigException $e) {
    // Handle configuration errors
    echo "Configuration Error: " . $e->getMessage() . "\n";
    echo "Please check your API credentials and settings.\n";

} catch (WebhookException $e) {
    // Handle webhook validation errors
    echo "Webhook Error: " . $e->getMessage() . "\n";

} catch (\Exception $e) {
    // Handle unexpected errors
    echo "Unexpected Error: " . $e->getMessage() . "\n";
    error_log("Dinlr API Error: " . $e->getTraceAsString());
}
```

### Error Recovery and Retry Logic

```php
function makeApiCallWithRetry($callable, $maxRetries = 3, $baseDelay = 1)
{
    $attempt = 0;

    while ($attempt < $maxRetries) {
        try {
            return $callable();

        } catch (RateLimitException $e) {
            $attempt++;
            if ($attempt >= $maxRetries) {
                throw $e;
            }

            // Exponential backoff
            $delay = $baseDelay * pow(2, $attempt - 1);
            sleep($delay);

        } catch (ApiException $e) {
            // Only retry on certain error codes
            if (in_array($e->getCode(), [500, 502, 503, 504])) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                sleep($baseDelay);
            } else {
                throw $e; // Don't retry on client errors
            }
        }
    }
}

// Usage
$orders = makeApiCallWithRetry(function() use ($client) {
    return $client->orders()->list();
});
```

## Testing

### Running Tests

Run the full test suite:

```bash
composer test

# Run with verbose output
./vendor/bin/phpunit --verbose

# Run specific test files
./vendor/bin/phpunit tests/CustomerApiTest.php
./vendor/bin/phpunit tests/OrderTest.php
./vendor/bin/phpunit tests/LoyaltyTest.php

# Run with coverage report
./vendor/bin/phpunit --coverage-html coverage/
```

### Test Configuration

Create a test configuration file at `tests/config.php`:

```php
<?php
return [
    'api_key' => getenv('DINLR_TEST_API_KEY') ?: 'your_test_api_key',
    'api_url' => getenv('DINLR_TEST_API_URL') ?: 'https://api.dinlr.com/v1',
    'restaurant_id' => getenv('DINLR_TEST_RESTAURANT_ID') ?: 'your_test_restaurant_id',
    'timeout' => (int) (getenv('DINLR_TEST_TIMEOUT') ?: 30),
    'debug' => (bool) (getenv('DINLR_TEST_DEBUG') ?: true),
];
```

### Environment Variables for Testing

Set these environment variables for testing:

```bash
export DINLR_TEST_API_KEY="your_test_api_key"
export DINLR_TEST_RESTAURANT_ID="your_test_restaurant_id"
export DINLR_TEST_API_URL="https://api.dinlr.com/v1"
export DINLR_TEST_DEBUG="true"
```

### Writing Custom Tests

```php
<?php
namespace YourApp\Tests;

use Nava\Dinlr\Client;
use PHPUnit\Framework\TestCase;

class CustomDinlrTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = new Client([
            'api_key' => 'test_key',
            'restaurant_id' => 'test_restaurant',
            'api_url' => 'https://api.dinlr.com/v1',
        ]);
    }

    public function testCustomerCreation()
    {
        $customerData = [
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'test@example.com',
        ];

        $customer = $this->client->customers()->create($customerData);

        $this->assertInstanceOf(\Nava\Dinlr\Models\Customer::class, $customer);
        $this->assertEquals('Test Customer', $customer->getFullName());
    }
}
```

### Static Analysis

Run static analysis with PHPStan:

```bash
composer analyse

# Or directly
./vendor/bin/phpstan analyse src tests --level=7
```

## Configuration Options

### Complete Configuration Reference

| Option        | Type   | Description               | Default                  | Required |
| ------------- | ------ | ------------------------- | ------------------------ | -------- |
| api_key       | string | Your Dinlr API key        | -                        | Yes      |
| restaurant_id | string | Your restaurant ID        | -                        | Yes      |
| api_url       | string | Base URL for Dinlr API    | https://api.dinlr.com/v1 | No       |
| timeout       | int    | Request timeout (seconds) | 30                       | No       |
| debug         | bool   | Enable debug mode         | false                    | No       |

### OAuth Configuration Options

| Option        | Type   | Description                | Default | Required |
| ------------- | ------ | -------------------------- | ------- | -------- |
| client_id     | string | OAuth client ID            | -       | Yes      |
| client_secret | string | OAuth client secret        | -       | Yes      |
| redirect_uri  | string | OAuth redirect URI         | -       | Yes      |
| access_token  | string | Current access token       | -       | No       |
| refresh_token | string | Current refresh token      | -       | No       |
| expires_at    | int    | Token expiration timestamp | -       | No       |

## Contributing

We welcome contributions to the Dinlr PHP client library! Here's how you can help:

### Development Setup

1. Fork the repository
2. Clone your fork:

```bash
git clone https://github.com/NavanithanS/dinlr-php.git
cd dinlr-php
```

3. Install dependencies:

```bash
composer install
```

4. Create a branch for your feature:

```bash
git checkout -b feature/amazing-feature
```

### Coding Standards

-   Follow PSR-4 autoloading standards
-   Use PSR-12 coding style
-   Write comprehensive PHPDoc comments
-   Include unit tests for new features
-   Maintain backward compatibility

### Running Quality Checks

```bash
# Run tests
composer test

# Check coding standards
composer cs

# Fix coding standards
composer cs-fix

# Run static analysis
composer analyse
```

### Submitting Changes

1. Make your changes
2. Add tests for new functionality
3. Ensure all tests pass
4. Update documentation if needed
5. Commit your changes:

```bash
git commit -am 'Add amazing feature'
```

6. Push to your fork:

```bash
git push origin feature/amazing-feature
```

7. Open a Pull Request

### Pull Request Guidelines

-   Provide a clear description of the changes
-   Include relevant tests
-   Update documentation if necessary
-   Ensure CI checks pass
-   Link to any related issues

### Reporting Issues

When reporting issues, please include:

-   PHP version
-   Library version
-   Detailed error messages
-   Code examples that reproduce the issue
-   Expected vs actual behavior

## Security

Security is a top priority for the Dinlr PHP client library.

### Reporting Security Vulnerabilities

Please do not report security vulnerabilities through public GitHub issues. Instead, email security@dinlr.com with:

-   Description of the vulnerability
-   Steps to reproduce
-   Potential impact
-   Suggested fix (if any)

We will respond to security reports within 24 hours and provide updates on our progress.

### Security Features

The library includes several security features:

-   Input Validation: All user inputs are validated and sanitized
-   Rate Limiting: Built-in handling for API rate limits
-   Webhook Validation: Cryptographic verification of webhook signatures
-   Secure Authentication: Support for OAuth 2.0 with secure token handling
-   Error Sanitization: Sensitive data is removed from logs and error messages

### Security Best Practices

When using this library:

-   Store credentials securely: Never commit API keys to version control
-   Use environment variables: Store sensitive configuration in environment variables
-   Validate webhook signatures: Always verify webhook authenticity
-   Handle errors gracefully: Don't expose sensitive error details to end users
-   Keep dependencies updated: Regularly update the library and its dependencies
-   Use HTTPS: Always communicate with the API over HTTPS
-   Implement rate limiting: Respect API rate limits to avoid being blocked

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Support

### Documentation and Resources

-   [Dinlr API Documentation](https://docs.dinlr.com) - Complete API reference
-   [Support Portal](https://support.dinlr.com) - Official support and help center
-   [GitHub Issues](https://github.com/dinlr/dinlr-php/issues) - Report bugs and request features

### Getting Help

For library-specific issues:

-   Check the GitHub Issues page
-   Search existing issues before creating new ones
-   Provide detailed information when reporting bugs

For API and business questions:

-   Visit https://support.dinlr.com
-   Contact the Dinlr support team directly

### Community

-   GitHub Discussions: Share ideas and get help from the community
