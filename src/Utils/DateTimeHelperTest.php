<?php

declare(strict_types=1);

final class DateTimeHelperTest extends PHPUnit\Framework\TestCase
{
	public function		testTimeRestrictionFrom(): void
	{
        self::assertEquals(
            "2017-01-01 00:00:00",
            \Rose\Utils\DateTimeHelper::timeRestrictionFrom(2017)
        );
    }

	public function		testTimeRestrictionTo(): void
	{
        self::assertEquals(
            "2017-12-31 23:59:59",
            \Rose\Utils\DateTimeHelper::timeRestrictionTo(2017)
        );
    }
}
