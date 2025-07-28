<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use function hash_hmac;
use function sprintf;
use function strlen;

enum Algorithm: string
{
	case Sha1 = 'sha1';
	case Sha256 = 'sha256';
	case Sha512 = 'sha512';

	public function hash(string $value, Secret $secret, bool $skipLengthCheck): string
	{
		if (!$skipLengthCheck && strlen($secret->asBinary()) < $this->minSecretLength()) {
			throw new InvalidArgumentException(sprintf(
				"Secret is too short (%d bytes), at least %d bytes should be provided for algorithm %s.",
				strlen($secret->asBinary()),
				$this->minSecretLength(),
				$this->value,
			));
		}

		return hash_hmac($this->value, $value, $secret->asBinary(), true);
	}

	public function minSecretLength(): int
	{
		return match ($this) {
			self::Sha1 => 20,
			self::Sha256 => 32,
			self::Sha512 => 64,
		};
	}
}
