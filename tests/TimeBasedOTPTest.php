<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use JiriPudil\OTP\Account\SimpleAccountDescriptor;
use JiriPudil\OTP\Infrastructure\Time\Clock;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
final class TimeBasedOTPTest extends TestCase
{
	public function testTimeBasedOtp(): void
	{
		$totp = new TimeBasedOTP(clock: new class implements Clock {
			public function getTime(): float
			{
				return 1653121000.0;
			}
		});

		$account = new SimpleAccountDescriptor('AccountName', Secret::random(20));

		Assert::same('totp', $totp->describe());
		Assert::same(['period' => 30], $totp->getParameters($account));

		$values = \iterator_to_array($totp->generateValues($account));
		Assert::same([
			55104033, //  1653121000       / 30
			55104032, // (1653121000 - 30) / 30
			55104034, // (1653121000 + 30) / 30
		], $values);
	}
}

(new TimeBasedOTPTest())->run();
