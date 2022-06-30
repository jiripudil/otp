<?php

declare(strict_types=1);

namespace JiriPudil\OTP\Infrastructure\Time;

interface Clock
{
	public function getTime(): float;
}
