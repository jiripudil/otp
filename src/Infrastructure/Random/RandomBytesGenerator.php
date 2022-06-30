<?php

declare(strict_types=1);

namespace JiriPudil\OTP\Infrastructure\Random;

interface RandomBytesGenerator
{
	/**
	 * @param positive-int $length
	 */
	public function generate(int $length): string;
}
