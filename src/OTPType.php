<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use Generator;
use JiriPudil\OTP\Account\AccountDescriptor;

interface OTPType
{
	public function describe(): string;

	/**
	 * @return array<string, mixed>
	 */
	public function getParameters(AccountDescriptor $account): array;

	/**
	 * @return Generator<int, int, bool, void>
	 */
	public function generateValues(AccountDescriptor $account): Generator;
}
