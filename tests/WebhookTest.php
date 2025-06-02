<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Exception\WebhookException;
use Nava\Dinlr\Webhook\WebhookValidator;
use PHPUnit\Framework\TestCase;

class WebhookTest extends TestCase
{
    private $signingSecret = 'test_signing_secret';
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();

        echo "\n\n✅ TEST CASE: Webhook Handling";
        echo "\n==============================================================";
        echo "\nSetting up webhook tests...";

        $this->validator = new WebhookValidator($this->signingSecret);

        echo "\n• Using test signing secret";
        echo "\n--------------------------------------------------------------";
    }

    public function testValidWebhookSignature()
    {
        echo "\n\nSTEP 1: Testing valid webhook signature";
        echo "\n--------------------------------------------------------------";

        $timestamp = time();
        $payload   = json_encode([
            'id'         => 'evt_test123',
            'object'     => 'order',
            'topic'      => 'order.created',
            'restaurant' => 'test_restaurant',
            'location'   => 'test_location',
            'created_at' => date('c'),
            'data'       => [
                'id'    => 'order_123',
                'total' => 100.00,
            ],
        ]);

        $signedPayload = $timestamp . '.' . $payload;
        $signature     = hash_hmac('sha256', $signedPayload, $this->signingSecret);
        $header        = "t={$timestamp},v1={$signature}";

        echo "\n• Timestamp: " . $timestamp;
        echo "\n• Signature: " . $signature;

        $isValid = $this->validator->validateSignature($payload, $header);

        echo "\n✓ Valid signature verified successfully";

        $this->assertTrue($isValid);
    }

    public function testInvalidWebhookSignature()
    {
        echo "\n\nSTEP 2: Testing invalid webhook signature";
        echo "\n--------------------------------------------------------------";

        $timestamp = time();
        $payload   = json_encode(['test' => 'data']);
        $header    = "t={$timestamp},v1=invalid_signature";

        echo "\n• Testing with invalid signature";

        try {
            $this->validator->validateSignature($payload, $header);
            $this->fail('Expected WebhookException was not thrown');
        } catch (WebhookException $e) {
            echo "\n✓ Invalid signature correctly rejected";
            $this->assertStringContainsString('Invalid webhook signature', $e->getMessage());
        }
    }

    public function testExpiredWebhookTimestamp()
    {
        echo "\n\nSTEP 3: Testing expired webhook timestamp";
        echo "\n--------------------------------------------------------------";

        $timestamp     = time() - 400; // 400 seconds ago
        $payload       = json_encode(['test' => 'data']);
        $signedPayload = $timestamp . '.' . $payload;
        $signature     = hash_hmac('sha256', $signedPayload, $this->signingSecret);
        $header        = "t={$timestamp},v1={$signature}";

        echo "\n• Testing with timestamp from 400 seconds ago";

        try {
            $this->validator->validateSignature($payload, $header);
            $this->fail('Expected WebhookException was not thrown');
        } catch (WebhookException $e) {
            echo "\n✓ Expired timestamp correctly rejected";
            $this->assertStringContainsString('outside the tolerance zone', $e->getMessage());
        }
    }

    public function testConstructWebhookEvent()
    {
        echo "\n\nSTEP 4: Testing webhook event construction";
        echo "\n--------------------------------------------------------------";

        $timestamp = time();
        $eventData = [
            'id'         => 'evt_test123',
            'object'     => 'order',
            'topic'      => 'order.created',
            'restaurant' => 'test_restaurant',
            'location'   => 'test_location',
            'created_at' => date('c'),
            'data'       => [
                'id'     => 'order_123',
                'total'  => 100.00,
                'status' => 'open',
            ],
        ];
        $payload = json_encode($eventData);

        $signedPayload = $timestamp . '.' . $payload;
        $signature     = hash_hmac('sha256', $signedPayload, $this->signingSecret);
        $header        = "t={$timestamp},v1={$signature}";

        echo "\n• Constructing webhook event";

        $event = $this->validator->constructEvent($payload, $header);

        echo "\n• Event ID: " . $event->getId();
        echo "\n• Object: " . $event->getObject();
        echo "\n• Topic: " . $event->getTopic();
        echo "\n• Restaurant: " . $event->getRestaurantId();
        echo "\n• Is order event: " . ($event->isOrderEvent() ? 'Yes' : 'No');
        echo "\n• Is create event: " . ($event->isCreateEvent() ? 'Yes' : 'No');

        echo "\n✓ Webhook event constructed successfully";

        $this->assertInstanceOf(\Nava\Dinlr\Models\Webhook::class, $event);
        $this->assertEquals('evt_test123', $event->getId());
        $this->assertEquals('order.created', $event->getTopic());
        $this->assertTrue($event->isOrderEvent());
        $this->assertTrue($event->isCreateEvent());
    }

    public function testWebhookEventTypes()
    {
        echo "\n\nSTEP 5: Testing webhook event type detection";
        echo "\n--------------------------------------------------------------";

        $eventTypes = [
            'order.created'                       => ['object' => 'order', 'isCreate' => true],
            'order.updated'                       => ['object' => 'order', 'isUpdate' => true],
            'order.deleted'                       => ['object' => 'order', 'isDelete' => true],
            'customer.created'                    => ['object' => 'customer', 'isCreate' => true],
            'loyalty_program_transaction.created' => ['object' => 'loyalty_program_transaction', 'isCreate' => true],
        ];

        foreach ($eventTypes as $topic => $expected) {
            $event = new \Nava\Dinlr\Models\Webhook([
                'id'     => 'test',
                'object' => $expected['object'],
                'topic'  => $topic,
                'data'   => [],
            ]);

            echo "\n• Testing topic: " . $topic;
            echo "\n  - Is order event: " . ($event->isOrderEvent() ? 'Yes' : 'No');
            echo "\n  - Is customer event: " . ($event->isCustomerEvent() ? 'Yes' : 'No');
            echo "\n  - Event type: " .
                ($event->isCreateEvent() ? 'Create' :
                ($event->isUpdateEvent() ? 'Update' :
                    ($event->isDeleteEvent() ? 'Delete' : 'Unknown')));

            if ('order' === $expected['object']) {
                $this->assertTrue($event->isOrderEvent());
            }
            if ('customer' === $expected['object']) {
                $this->assertTrue($event->isCustomerEvent());
            }
            if (isset($expected['isCreate'])) {
                $this->assertTrue($event->isCreateEvent());
            }
            if (isset($expected['isUpdate'])) {
                $this->assertTrue($event->isUpdateEvent());
            }
            if (isset($expected['isDelete'])) {
                $this->assertTrue($event->isDeleteEvent());
            }
        }

        echo "\n✓ Event type detection working correctly";
    }
}
