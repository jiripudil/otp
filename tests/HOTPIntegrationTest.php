<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use JiriPudil\OTP\Account\AccountDescriptor;
use JiriPudil\OTP\Account\SimpleAccountDescriptor;
use JiriPudil\OTP\HmacBasedOTP\CounterRepository;
use Tester\Assert;
use Tester\TestCase;
use function str_repeat;

require __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
final class HOTPIntegrationTest extends TestCase
{
	public function testGenerate(): void
	{
		$authenticator = new OTP(
			'MyIssuer',
			new HmacBasedOTP($counterRepository = new class /** @implements CounterRepository<SimpleAccountDescriptor> */ implements CounterRepository {
				public int|null $setCalledWith = null;

				public function get(AccountDescriptor $account): int
				{
					return 42;
				}

				public function set(AccountDescriptor $account, int $counter): void
				{
					$this->setCalledWith = $counter;
				}
			}),
		);

		$account = new SimpleAccountDescriptor('AccountName', Secret::fromBinary(str_repeat("\x42", 20)));

		Assert::same('189342', $authenticator->generate($account));
		Assert::same(43, $counterRepository->setCalledWith);
	}

	/**
	 * @dataProvider provideVerifyData
	 */
	public function testVerify(
		string $code,
		bool $expectedResult,
		int|null $expectedSetCalledWith,
	): void
	{
		$authenticator = new OTP(
			'MyIssuer',
			new HmacBasedOTP($counterRepository = new class /** @implements CounterRepository<SimpleAccountDescriptor> */ implements CounterRepository {
				public int|null $setCalledWith = null;

				public function get(AccountDescriptor $account): int
				{
					return 42;
				}

				public function set(AccountDescriptor $account, int $counter): void
				{
					$this->setCalledWith = $counter;
				}
			}),
		);

		$account = new SimpleAccountDescriptor('AccountName', Secret::fromBinary(str_repeat("\x42", 20)));

		Assert::same($expectedResult, $authenticator->verify($account, $code));
		Assert::same($expectedSetCalledWith, $counterRepository->setCalledWith);
	}

	/**
	 * @return iterable<array{string, bool, int|null}>
	 */
	public function provideVerifyData(): iterable
	{
		yield ['557011', false, null];
		yield ['189342', true, 43];
		yield ['003549', true, 44];
		yield ['251355', true, 45];
		yield ['265328', true, 46];
		yield ['189549', false, null];
	}
}

(new HOTPIntegrationTest())->run();
