<?php

declare(strict_types=1);

class SingleValueFilterMockup extends \Rose\Application\UI\Presenter\Filters\SingleValueFilter {
    public function __construct(string $key)
    {
        parent::__construct($key);
    }

    public function getName(): string
    {
        return "SingleValueFilterMockup";
    }

    public function applyFilterToQuery(\Dibi\Fluent& $query, array $params): void
    {        
    }
}

final class SingleValueFilterTest extends PHPUnit\Framework\TestCase
{
    public function testIsNotValidIfParametersAreEmptyArray(): void
    {
        $filter = new SingleValueFilterMockup("test_key");

        self::assertFalse(
            $filter->isValid([])
        );
    }

    public function testIsValidIfParametersContainsRequiredKey(): void
    {
        $filter = new SingleValueFilterMockup("test_key");

        self::assertTrue(
            $filter->isValid(["test_key" => 7])
        );
    }

    public function testIsNotValidIfParametersContainsOnlyBadKey(): void
    {
        $filter = new SingleValueFilterMockup("test_key");

        self::assertFalse(
            $filter->isValid(["bad_key" => 7])
        );

    }

    public function testIsValidIfParametersContainsOnlyAlsoRequiredKey(): void
    {
        $filter = new SingleValueFilterMockup("test_key");

        self::assertTrue(
            $filter->isValid([
                "key1" => 7, 
                "test_key" => 7, 
                "another_key" => 7
            ])
        );


    }

    public function testIsValidIfParametersContainsOnlyBadKeys(): void
    {
        $filter = new SingleValueFilterMockup("test_key");

        self::assertFalse(
            $filter->isValid([
                "key1" => 7, 
                "another_key" => 7
            ])
        );
    }
}
