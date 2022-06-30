<?php

declare(strict_types=1);

namespace JiriPudil\OTP\Infrastructure\Random;

use function random_bytes;

final class NativeRandomBytesGenerator implements RandomBytesGenerator
{
	public function generate(int $length): string
	{
		return random_bytes($length);
	}
}
