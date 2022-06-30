<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use JiriPudil\OTP\Infrastructure\Random\RandomBytesGenerator;
use Tester\Assert;
use Tester\TestCase;
use function str_repeat;

require __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
final class SecretTest extends TestCase
{
	private RandomBytesGenerator $randomBytesGenerator;

	protected function setUp(): void
	{
		parent::setUp();
		$this->randomBytesGenerator = new class implements RandomBytesGenerator {
			public function generate(int $length): string
			{
				return str_repeat("\x42", $length);
			}
		};
	}

	public function testSecret(): void
	{
		$secret = Secret::random(20, $this->randomBytesGenerator);
		Assert::same('BBBBBBBBBBBBBBBBBBBB', $secret->asBinary());
		Assert::same('IJBEEQSCIJBEEQSCIJBEEQSCIJBEEQSC', $secret->asBase32());
		Assert::same('4242424242424242424242424242424242424242', $secret->asHex());

		$fromBinary = Secret::fromBinary('BBBBBBBBBBBBBBBBBBBB');
		Assert::same($secret->asBinary(), $fromBinary->asBinary());

		$fromBase32 = Secret::fromBase32('IJBEEQSCIJBEEQSCIJBEEQSCIJBEEQSC');
		Assert::same($secret->asBinary(), $fromBase32->asBinary());

		$fromHex = Secret::fromHex('4242424242424242424242424242424242424242');
		Assert::same($secret->asBinary(), $fromHex->asBinary());
	}
}

(new SecretTest())->run();
