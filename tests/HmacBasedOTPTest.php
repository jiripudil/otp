<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use JiriPudil\OTP\Account\AccountDescriptor;
use JiriPudil\OTP\Account\SimpleAccountDescriptor;
use JiriPudil\OTP\HmacBasedOTP\CounterRepository;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
final class HmacBasedOTPTest extends TestCase
{
	public function testHmacBasedOtp(): void
	{
		$hotp = new HmacBasedOTP($counterRepository = new class /** @implements CounterRepository<SimpleAccountDescriptor> */ implements CounterRepository {
			public int|null $setCalledWith = null;

			public function get(AccountDescriptor $account): int
			{
				return 42;
			}

			public function set(AccountDescriptor $account, int $counter): void
			{
				$this->setCalledWith = $counter;
			}
		});

		$account = new SimpleAccountDescriptor('AccountName', Secret::random(20));

		Assert::same('hotp', $hotp->describe());
		Assert::same(['counter' => 42], $hotp->getParameters($account));

		$iteration = 0;
		foreach (($generator = $hotp->generateValues($account)) as $value) {
			Assert::same(42 + $iteration++, $value);
			if ($value === 45) {
				$generator->send(true);
			}
		}

		Assert::same(46, $counterRepository->setCalledWith);
	}
}

(new HmacBasedOTPTest())->run();
