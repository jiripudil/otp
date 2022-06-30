<?php /** @noinspection TestCaseIsRun */

declare(strict_types=1);

namespace JiriPudil\OTP;

use Generator;
use JiriPudil\OTP\Account\AccountDescriptor;
use JiriPudil\OTP\Account\SimpleAccountDescriptor;
use JiriPudil\OTP\HmacBasedOTP\CounterRepository;
use Tester\Assert;
use Tester\TestCase;
use function str_repeat;
use function strlen;

require __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
final class OTPTest extends TestCase
{
	public function testGenerateSecret(): void
	{
		$sha1 = new OTP('TestIssuer', new TimeBasedOTP(), Algorithm::Sha1);
		$sha1Secret = $sha1->generateSecret();
		Assert::same(20, strlen($sha1Secret->asBinary()));

		$sha256 = new OTP('TestIssuer', new TimeBasedOTP(), Algorithm::Sha256);
		$sha256Secret = $sha256->generateSecret();
		Assert::same(32, strlen($sha256Secret->asBinary()));

		$sha512 = new OTP('TestIssuer', new TimeBasedOTP(), Algorithm::Sha512);
		$sha512Secret = $sha512->generateSecret();
		Assert::same(64, strlen($sha512Secret->asBinary()));
	}

	public function testGetTotpProvisioningUri(): void
	{
		$account = new SimpleAccountDescriptor('AccountName', Secret::fromBase32('JBSWY3DPEHPK3PXP'));

		$totp = new OTP('TestIssuer', new TimeBasedOTP());
		$totpUri = $totp->getProvisioningUri($account);
		Assert::same(
			'otpauth://totp/TestIssuer:AccountName?secret=JBSWY3DPEHPK3PXP&issuer=TestIssuer&algorithm=sha1&digits=6&period=30',
			$totpUri,
		);
	}

	public function testGetHotpProvisioningUri(): void
	{
		$account = new SimpleAccountDescriptor('AccountName', Secret::fromBase32('JBSWY3DPEHPK3PXP'));
		$counterRepository = new class /** @implements CounterRepository<SimpleAccountDescriptor> */ implements CounterRepository {
			public function get(AccountDescriptor $account): int
			{
				return 42;
			}

			public function set(AccountDescriptor $account, int $counter): void
			{
				// no-op
			}
		};

		$hotp = new OTP('TestIssuer', new HmacBasedOTP($counterRepository));
		$hotpUri = $hotp->getProvisioningUri($account);
		Assert::same(
			'otpauth://hotp/TestIssuer:AccountName?secret=JBSWY3DPEHPK3PXP&issuer=TestIssuer&algorithm=sha1&digits=6&counter=42',
			$hotpUri,
		);
	}

	public function testGenerate(): void
	{
		$otp = new class implements OTPType {
			public function describe(): string
			{
				return 'test';
			}

			public function getParameters(AccountDescriptor $account): array
			{
				return [];
			}

			public function generateValues(AccountDescriptor $account): Generator
			{
				yield 42;
			}
		};

		$account = new SimpleAccountDescriptor('AccountName', Secret::fromBinary(str_repeat("\x42", 20)));

		$authenticator = new OTP('TestIssuer', $otp);
		Assert::same('189342', $authenticator->generate($account));
		Assert::same('0189342', $authenticator->generate($account, 7));
		Assert::same('30189342', $authenticator->generate($account, 8));
	}

	public function testVerify(): void
	{
		$otp = new class implements OTPType {
			public function describe(): string
			{
				return 'test';
			}

			public function getParameters(AccountDescriptor $account): array
			{
				return [];
			}

			public function generateValues(AccountDescriptor $account): Generator
			{
				yield 42;
			}
		};

		$account = new SimpleAccountDescriptor('AccountName', Secret::fromBinary(str_repeat("\x42", 20)));

		$authenticator = new OTP('TestIssuer', $otp);
		Assert::true($authenticator->verify($account, '30189342', 8));
		Assert::true($authenticator->verify($account, '0189342', 7));
		Assert::true($authenticator->verify($account, '189342'));
	}
}

(new OTPTest())->run();
