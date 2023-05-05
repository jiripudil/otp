<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use JiriPudil\OTP\Account\AccountDescriptor;
use JiriPudil\OTP\Infrastructure\Random\NativeRandomBytesGenerator;
use JiriPudil\OTP\Infrastructure\Random\RandomBytesGenerator;
use function hash_equals;
use function http_build_query;
use function ord;
use function pack;
use function rawurlencode;
use function sprintf;
use function str_pad;
use function strlen;
use function strtoupper;
use const PHP_QUERY_RFC3986;
use const STR_PAD_LEFT;

final class OTP
{
	private const MIN_DIGITS = 6;
	private const MAX_DIGITS = 8;

	public function __construct(
		private readonly string $issuer,
		private readonly OTPType $otp,
		private readonly Algorithm $algorithm = Algorithm::Sha1,
	)
	{
	}

	public function generateSecret(
		RandomBytesGenerator $randomBytesGenerator = new NativeRandomBytesGenerator(),
	): Secret
	{
		return Secret::random(
			$this->algorithm->minSecretLength(),
			$randomBytesGenerator,
		);
	}

	public function getProvisioningUri(
		AccountDescriptor $account,
		int $digits = self::MIN_DIGITS,
	): string
	{
		$this->checkDigits($digits);

		$type = $this->otp->describe();
		$accountName = rawurlencode($account->getAccountName());
		$issuer = rawurlencode($this->issuer);
		$label = "$issuer:$accountName";

		$query = http_build_query([
			'secret' => $account->getSecret()->asBase32(),
			'issuer' => $this->issuer,
			'algorithm' => strtoupper($this->algorithm->value),
			'digits' => $digits,
			...$this->otp->getParameters($account),
		], encoding_type: PHP_QUERY_RFC3986);

		return "otpauth://$type/$label?$query";
	}

	/**
	 * Generates a one-time password for given account and of given length.
	 *
	 * Only use this method if you're implementing an OTP client/application.
	 * This method MUST NOT be used for verification.
	 */
	public function generate(
		AccountDescriptor $account,
		int $digits = self::MIN_DIGITS,
	): string
	{
		$this->checkDigits($digits);

		$generator = $this->otp->generateValues($account);
		$value = $generator->current();
		$generator->send(true);

		return $this->generateOneTimePassword($value, $account->getSecret(), $digits);
	}

	/**
	 * Verifies given one-time password against the expected code for given user.
	 */
	public function verify(
		AccountDescriptor $account,
		string $oneTimePassword,
		int $expectedDigits = self::MIN_DIGITS,
	): bool
	{
		$this->checkDigits($expectedDigits);

		$digits = strlen($oneTimePassword);
		if ($digits !== $expectedDigits) {
			return false;
		}

		$generator = $this->otp->generateValues($account);
		$value = $generator->current();
		while ($generator->valid()) {
			$success = hash_equals($oneTimePassword, $this->generateOneTimePassword($value, $account->getSecret(), $digits));
			$value = $generator->send($success);

			if ($success) {
				return true;
			}
		}

		return false;
	}

	private function checkDigits(int $digits): void
	{
		if ($digits < self::MIN_DIGITS || $digits > self::MAX_DIGITS) {
			throw new InvalidArgumentException(sprintf(
				"The digits count should be between %d and %d inclusive, %d given.",
				self::MIN_DIGITS,
				self::MAX_DIGITS,
				$digits,
			));
		}
	}

	private function generateOneTimePassword(
		int $value,
		Secret $secret,
		int $digits,
	): string
	{
		$packedValue = pack('J*', $value);
		$hash = $this->algorithm->hash($packedValue, $secret);
		$offset = ord($hash[strlen($hash) - 1]) & 0xf;

		$code = (
			((ord($hash[$offset + 0]) & 0x7f) << 24) |
			((ord($hash[$offset + 1]) & 0xff) << 16) |
			((ord($hash[$offset + 2]) & 0xff) << 8) |
			((ord($hash[$offset + 3]) & 0xff) << 0)
		) % (10 ** $digits);

		return str_pad((string) $code, $digits, '0', STR_PAD_LEFT);
	}
}
