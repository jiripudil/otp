<?php

declare(strict_types=1);

namespace JiriPudil\OTP\HmacBasedOTP;

use JiriPudil\OTP\Account\AccountDescriptor;

/**
 * @template AD of AccountDescriptor
 */
interface CounterRepository
{
	/**
	 * @param AD $account
	 */
	public function get(AccountDescriptor $account): int;

	/**
	 * @param AD $account
	 */
	public function set(AccountDescriptor $account, int $counter): void;
}
