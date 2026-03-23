<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Infrastructure\Gateways\Stripe;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionCycle;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionStatus;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Subscriptions\StripeSubscriptionMapper;

class StripeSubscriptionMapperTest extends TestCase
{
    private StripeSubscriptionMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new StripeSubscriptionMapper();
    }

    public function testToSubscriptionMapsStripeDataCorrectly(): void
    {
        $data = [
            'id'                 => 'sub_1234567890',
            'customer'           => 'cus_1234567890',
            'status'             => 'active',
            'description'        => 'Premium Plan',
            'current_period_end' => 1713546000,
            'metadata'           => ['externalReference' => 789],
            'items'              => [
                'data' => [
                    [
                        'price' => [
                            'unit_amount' => 2999,
                            'recurring'   => [
                                'interval'       => 'month',
                                'interval_count' => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $subscription = $this->mapper->toSubscription($data);

        $this->assertEquals('sub_1234567890', $subscription->id);
        $this->assertEquals('cus_1234567890', $subscription->customerId->getValue());
        $this->assertEquals(29.99, $subscription->value->getAmount());
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertEquals(SubscriptionCycle::MONTHLY, $subscription->cycle);
        $this->assertEquals('Premium Plan', $subscription->description);
        $this->assertEquals(789, $subscription->externalReference);
        $this->assertTrue($subscription->isActive());
    }

    public function testBillingCycleMappingFromStripe(): void
    {
        $testCases = [
            'weekly'       => ['interval' => 'week', 'interval_count' => 1, 'expected' => SubscriptionCycle::WEEKLY],
            'biweekly'     => ['interval' => 'day', 'interval_count' => 14, 'expected' => SubscriptionCycle::BIWEEKLY],
            'monthly'      => ['interval' => 'month', 'interval_count' => 1, 'expected' => SubscriptionCycle::MONTHLY],
            'quarterly'    => ['interval' => 'month', 'interval_count' => 3, 'expected' => SubscriptionCycle::QUARTERLY],
            'semiannually' => ['interval' => 'month', 'interval_count' => 6, 'expected' => SubscriptionCycle::SEMIANNUALLY],
            'yearly'       => ['interval' => 'year', 'interval_count' => 1, 'expected' => SubscriptionCycle::YEARLY],
        ];

        foreach ($testCases as $caseName => $caseData) {
            $expectedCycle = $caseData['expected'];
            unset($caseData['expected']);

            $data = [
                'id'       => 'sub_test',
                'customer' => 'cus_test',
                'status'   => 'active',
                'items'    => [
                    'data' => [
                        [
                            'price' => [
                                'unit_amount' => 1000,
                                'recurring'   => $caseData,
                            ],
                        ],
                    ],
                ],
            ];

            $subscription = $this->mapper->toSubscription($data);
            $this->assertEquals($expectedCycle, $subscription->cycle, "Failed for cycle: $caseName");
        }
    }

    public function testSubscriptionStatusMappingFromStripe(): void
    {
        $statuses = [
            'active'   => SubscriptionStatus::ACTIVE,
            'past_due' => SubscriptionStatus::ACTIVE,
            'paused'   => SubscriptionStatus::INACTIVE,
            'canceled' => SubscriptionStatus::INACTIVE,
        ];

        foreach ($statuses as $stripeStatus => $expectedStatus) {
            $data = [
                'id'       => 'sub_test',
                'customer' => 'cus_test',
                'status'   => $stripeStatus,
                'items'    => [
                    'data' => [
                        [
                            'price' => [
                                'unit_amount' => 1000,
                                'recurring'   => [
                                    'interval'       => 'month',
                                    'interval_count' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $subscription = $this->mapper->toSubscription($data);
            $this->assertEquals($expectedStatus, $subscription->status);
        }
    }
}
