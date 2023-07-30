<?php

declare(strict_types=1);

use Rose\Application\UI\Presenter\OrderItem;

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

    protected function getResource(): \Nette\Security\Resource
    {
        return new \Rose\Security\Resource("mock-api");
    }
}

final class ApiPresenterTest extends PHPUnit\Framework\TestCase
{
	public function		testFoo(): void
	{
        $factory = new \Nette\Application\PresenterFactory();
        //$presenter = $factory->createPresenter("MockApi");
        $presenter = new MockApiPresenter();

        /*\Nette\Application\Container*/ $context = null;
        /*\Nette\Application\IPresenterFactory*/ $presenterFactory = null;
        /*\Nette\Application\Router*/ $router = new \Nette\Application\Routers\SimpleRouter();
        /*\Nette\Application\IRequest*/ $httpRequest = new \Nette\Http\Request(
            new \Nette\Http\UrlScript(), null, null, null, null, \Nette\Http\IRequest::Get
        );
        /*\Nette\Application\IResponse*/ $httpResponse = new \Nette\Http\Response();
        /*\Nette\Application\Session*/ $session = null;
        /*\Nette\Application\User*/ $user = null;
        /*\Nette\Application\ITemplateFactory*/ $templateFactory = null;

        $presenter->injectPrimary(
            $context,
            $presenterFactory,
            $router,
            $httpRequest,
            $httpResponse,
            $session,
            $user,
            $templateFactory
        );

        self::assertNotNull($presenter);

        $request = new \Nette\Application\Request("list", \Nette\Http\IRequest::Get);
        $response = $presenter->run($request);

        //\Tracy\Debugger::dump($response);
        //self::assertEquals(301, $response->httpCode);

        self::markTestIncomplete("Not implemented.");
    }    
    
    public function testIsOrderDirection(): void
    {
        self::assertTrue(\Rose\Application\UI\Presenter\OrderDirection::isOrderDirection(\dibi::ASC));
        self::assertTrue(\Rose\Application\UI\Presenter\OrderDirection::isOrderDirection(\dibi::DESC));
        self::assertTrue(\Rose\Application\UI\Presenter\OrderDirection::isOrderDirection("ASC"));
        self::assertTrue(\Rose\Application\UI\Presenter\OrderDirection::isOrderDirection("DESC"));
        self::assertFalse(\Rose\Application\UI\Presenter\OrderDirection::isOrderDirection("UNKNOWN"));
        self::assertFalse(\Rose\Application\UI\Presenter\OrderDirection::isOrderDirection("asc"));
        //self::assertFalse(\Rose\Application\UI\Presenter\OrderDirection::isOrderDirection(null));
        //self::assertFalse(\Rose\Application\UI\Presenter\OrderDirection::isOrderDirection(7));
    }

    public function testParseOrder_EmptyArray(): void
    {
        $items = json_decode('["column"]', true);

        self::assertTrue(is_array($items));
        self::assertCount(1, $items);

        $order = \Rose\Application\UI\Presenter\Order::parseOrder('[]');
        //self::assertTrue(is_array($order));
        self::assertCount(0, $order);
    }

    public function testParseOrder_OneColumnName(): void
    {
        $order = \Rose\Application\UI\Presenter\Order::parseOrder('["column"]');
        //self::assertTrue(is_array($order));
        self::assertCount(1, $order);
        self::assertTrue($order[0] instanceof \Rose\Application\UI\Presenter\OrderItem);
        self::assertEquals("column", $order[0]->column);
        self::assertEquals(\Rose\Application\UI\Presenter\OrderDirection::DESC, $order[0]->direction);
    }

    public function testParseOrder_OneColumn(): void
    {
        $order = \Rose\Application\UI\Presenter\Order::parseOrder('[ ["column", "ASC"] ]');
        //self::assertTrue(is_array($order));
        self::assertCount(1, $order);
        self::assertTrue($order[0] instanceof \Rose\Application\UI\Presenter\OrderItem);
        self::assertEquals("column", $order[0]->column);
        self::assertEquals(\Rose\Application\UI\Presenter\OrderDirection::ASC, $order[0]->direction);
    }

    public function testParseOrder_TwoColumns(): void
    {
        $order = \Rose\Application\UI\Presenter\Order::parseOrder('[ "column1", ["column2", "ASC"] ]');
        //self::assertTrue(is_array($order));
        self::assertCount(2, $order);

        self::assertTrue($order[0] instanceof \Rose\Application\UI\Presenter\OrderItem);
        self::assertEquals("column1", $order[0]->column);
        self::assertEquals(\Rose\Application\UI\Presenter\OrderDirection::DESC, $order[0]->direction);

        self::assertTrue($order[1] instanceof \Rose\Application\UI\Presenter\OrderItem);
        self::assertEquals("column2", $order[1]->column);
        self::assertEquals(\Rose\Application\UI\Presenter\OrderDirection::ASC, $order[1]->direction);
    }
}
