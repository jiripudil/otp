<?php

declare(strict_types=1);

namespace JiriPudil\OTP;

use Generator;
use JiriPudil\OTP\Account\AccountDescriptor;
use JiriPudil\OTP\HmacBasedOTP\CounterRepository;

/**
 * @template AD of AccountDescriptor
 */
final class HmacBasedOTP implements OTPType
{
	/**
	 * @param CounterRepository<AD> $counterRepository
	 */
	public function __construct(
		private readonly CounterRepository $counterRepository,
		private readonly int $lookAhead = 3,
	)
	{
	}

	public function describe(): string
	{
		return 'hotp';
	}

	/**
	 * @param AD $account
	 */
	public function getParameters(AccountDescriptor $account): array
	{
		return [
			'counter' => $this->counterRepository->get($account),
		];
	}

	/**
	 * @param AD $account
	 */
	public function generateValues(AccountDescriptor $account): Generator
	{
		$counter = $this->counterRepository->get($account);

		for ($attempt = 0; $attempt <= $this->lookAhead; $attempt++) {
			$step = $counter + $attempt;
			$success = yield $step;

			if ($success) {
				$this->counterRepository->set($account, $step + 1);
				return;
			}
		}
	}
}
