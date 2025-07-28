<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Hex;
use Random\CryptoSafeEngine;
use Random\Engine;
use Random\Randomizer;

final readonly class Secret
{
	private function __construct(
		private string $secret,
	) {}

	public static function random(
		int $length,
		CryptoSafeEngine $engine = new Engine\Secure(),
	): self
	{
		if ($length < 1) {
			throw new InvalidArgumentException("Secret length must be a positive integer, $length given.");
		}

		$randomizer = new Randomizer($engine);
		return new self($randomizer->getBytes($length));
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
