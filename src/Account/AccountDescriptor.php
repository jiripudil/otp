<?php

declare(strict_types=1);

namespace JiriPudil\OTP\Account;

use JiriPudil\OTP\Secret;

interface AccountDescriptor
{
	public function getAccountName(): string;

	public function getSecret(): Secret;
}
