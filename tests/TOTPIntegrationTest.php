<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use JiriPudil\OTP\Account\SimpleAccountDescriptor;
use JiriPudil\OTP\Infrastructure\Time\Clock;
use Tester\Assert;
use Tester\TestCase;
use function str_repeat;

require __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
final class TOTPIntegrationTest extends TestCase
{
	public function testGenerate(): void
	{
		$authenticator = new OTP(
			'MyIssuer',
			new TimeBasedOTP(clock: new class implements Clock {
				public function getTime(): float
				{
					return 1653121000.0;
				}
			}),
		);

		$account = new SimpleAccountDescriptor('AccountName', Secret::fromBinary(str_repeat("\x42", 20)));
		Assert::same('930232', $authenticator->generate($account));

		// short secret is ok here
		$account = new SimpleAccountDescriptor('AccountName', Secret::fromBinary(str_repeat("\x42", 10)));
		Assert::noError(fn() => $authenticator->generate($account));
	}

	public function testVerify(): void
	{
		$authenticator = new OTP(
			'MyIssuer',
			new TimeBasedOTP(clock: new class implements Clock {
				public function getTime(): float
				{
					return 1653121000.0;
				}
			}),
		);

		$account = new SimpleAccountDescriptor('AccountName', Secret::fromBinary(str_repeat("\x42", 20)));
		Assert::false($authenticator->verify($account, '027585'));
		Assert::true($authenticator->verify($account, '714222'));
		Assert::true($authenticator->verify($account, '930232'));
		Assert::true($authenticator->verify($account, '605509'));
		Assert::false($authenticator->verify($account, '570599'));

		// short secret means error
		$account = new SimpleAccountDescriptor('AccountName', Secret::fromBinary(str_repeat("\x42", 10)));
		Assert::throws(
			fn() => $authenticator->verify($account, '111111'),
			\InvalidArgumentException::class,
			'Secret is too short (10 bytes), at least 20 bytes should be provided for algorithm sha1.',
		);
	}
}

(new TOTPIntegrationTest())->run();
