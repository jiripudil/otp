<?php

declare(strict_types=1);

namespace JiriPudil\OTP\Account;

use JiriPudil\OTP\Secret;

final class SimpleAccountDescriptor implements AccountDescriptor
{
	public function __construct(
		private readonly string $accountName,
		private readonly Secret $secret,
	)
	{
	}

	public function getAccountName(): string
	{
		return $this->accountName;
	}

	public function getSecret(): Secret
	{
		return $this->secret;
	}
}
