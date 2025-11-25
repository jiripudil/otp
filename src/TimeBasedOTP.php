<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use Generator;
use JiriPudil\OTP\Account\AccountDescriptor;
use Psr\Clock\ClockInterface;
use function floor;

final readonly class TimeBasedOTP implements OTPType
{
	public function __construct(
		private ClockInterface $clock,
		private int $tolerance = 1,
		private int $timeStep = 30,
	) {}

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
		$now = $this->clock->now();

		$timestamp = floor($now->getTimestamp() / $this->timeStep);
		yield (int) $timestamp;

		for ($offset = 1; $offset <= $this->tolerance; $offset++) {
			$timestamp = floor(($now->getTimestamp() - ($offset * $this->timeStep)) / $this->timeStep);
			yield (int) $timestamp;

			$timestamp = floor(($now->getTimestamp() + ($offset * $this->timeStep)) / $this->timeStep);
			yield (int) $timestamp;
		}
	}
}
