<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use Generator;
use JiriPudil\OTP\Account\AccountDescriptor;
use JiriPudil\OTP\Infrastructure\Time\Clock;
use JiriPudil\OTP\Infrastructure\Time\SystemClock;
use function floor;

final class TimeBasedOTP implements OTPType
{
	public function __construct(
		private readonly int $tolerance = 1,
		private readonly int $timeStep = 30,
		private readonly Clock $clock = new SystemClock(),
	)
	{
	}

	public function describe(): string
	{
		return 'totp';
	}

	public function getParameters(AccountDescriptor $account): array
	{
		return [
			'period' => $this->timeStep,
		];
	}

	public function generateValues(AccountDescriptor $account): Generator
	{
		$timestamp = floor($this->clock->getTime() / $this->timeStep);
		yield (int) $timestamp;

		for ($offset = 1; $offset <= $this->tolerance; $offset++) {
			$timestamp = floor(($this->clock->getTime() - ($offset * $this->timeStep)) / $this->timeStep);
			yield (int) $timestamp;

			$timestamp = floor(($this->clock->getTime() + ($offset * $this->timeStep)) / $this->timeStep);
			yield (int) $timestamp;
		}
	}
}
