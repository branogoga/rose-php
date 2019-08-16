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
        $factory = new \Nette\Application\PresenterFactory();
        //$presenter = $factory->createPresenter("MockApi");
        $presenter = new MockApiPresenter();

        /*\Nette\Application\Container*/ $context = null;
        /*\Nette\Application\IPresenterFactory*/ $presenterFactory = null;
        /*\Nette\Application\Router*/ $router = new \Nette\Application\Routers\SimpleRouter();
        /*\Nette\Application\IRequest*/ $httpRequest = new \Nette\Http\Request(
            new \Nette\Http\UrlScript(), null, null, null, null, \Nette\Http\IRequest::GET
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

        $this->assertNotNull($presenter);

        $request = new \Nette\Application\Request("list", \Nette\Http\IRequest::GET);
        $response = $presenter->run($request);

        \Tracy\Debugger::dump($response);
	}        
}
