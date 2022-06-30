<?php

declare(strict_types=1);

namespace JiriPudil\OTP\Infrastructure\Time;

use function microtime;

final class SystemClock implements Clock
{
	public function getTime(): float
	{
		return microtime(true);
	}
}
