<?php

declare(strict_types=1);

class MockApiPresenter extends \Rose\Application\UI\Presenter\ApiPresenter
{    
    protected function getModel(): \Rose\Data\Model\Model
    {
        return new TestModel();
    }

    protected function validate(array $data): array
    {
        $errors = array();
        return $errors;
    }
}

final class ApiPresenterTest extends PHPUnit\Framework\TestCase
{
	public function		testFoo()
	{
        $presenter = new MockApiPresenter();
        $this->assertNotNull($presenter);
	}        
}
