<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use JiriPudil\OTP\Infrastructure\Random\NativeRandomBytesGenerator;
use JiriPudil\OTP\Infrastructure\Random\RandomBytesGenerator;
use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Hex;

final class Secret
{
	private function __construct(
		private readonly string $secret,
	)
	{
	}

	public static function random(
		int $length,
		RandomBytesGenerator $randomBytesGenerator = new NativeRandomBytesGenerator(),
	): self
	{
		if ($length < 1) {
			throw new InvalidArgumentException("Secret length must be a positive integer, $length given.");
		}

		return new self($randomBytesGenerator->generate($length));
	}

	public static function fromBinary(string $secret): self
	{
		return new self($secret);
	}

	public static function fromHex(string $hexSecret): self
	{
		return new self(Hex::decode($hexSecret));
	}

	public static function fromBase32(string $base32Secret): self
	{
		return new self(Base32::decodeUpper($base32Secret));
	}

	public function asBinary(): string
	{
		return $this->secret;
	}

	public function asHex(): string
	{
		return Hex::encode($this->secret);
	}

	public function asBase32(): string
	{
		return Base32::encodeUpperUnpadded($this->secret);
	}
}
