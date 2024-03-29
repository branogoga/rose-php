<?php

declare(strict_types=1);

namespace Rose\Application\UI\Presenter;

class OrderDirection
{
    const ASC = \dibi::ASC;
    const DESC = \dibi::DESC;

    static public function isOrderDirection(string $direction): bool
    {
        if(($direction !== OrderDirection::ASC) && ($direction !== OrderDirection::DESC))
        {
            return false;
        }
    
        return true;
    }    
}

class OrderItem
{
    function __construct(string $column, string $direction)
    {
        $this->column = $column;
        $this->direction = $direction;
    }

    /** @var string */
    public $column;

    /** @var string */
    public $direction;
}

class Order
{
    static public function parseOrder(string $json): array
    {
        $order = array();

        $items = json_decode($json, true);

        if(!is_array($items))
        {
            throw new \InvalidArgumentException("Order must be an JSON array.");
        }

        foreach($items as $item)
        {
            if(is_string($item))
            {
                $order[] = new OrderItem($item, OrderDirection::DESC);
            }
            else if(is_array($item))
            {
                $column = $item[0];
                if(!is_string($column))
                {
                    throw new \InvalidArgumentException("First item of order item array must be a string.");
                }

                $direction = $item[1];
                if(!is_string($direction))
                {
                    throw new \InvalidArgumentException("Second item of order item array must be a string.");
                }

                $direction = strtoupper($direction);
                if((!OrderDirection::isOrderDirection($direction)))
                {
                    throw new \InvalidArgumentException("Order direction must be either 'ASC'or 'DESC'.");
                }

                $order[] = new OrderItem($column, $direction);
            }
        }

        return $order;
    }
}

class CountResponse
{
    public int    $count;
}

class ListResponse
{
    public array    $items;
}

abstract class ApiPresenter extends Presenter
{
    protected abstract function getModel(): \Rose\Data\Model\Model;
    protected abstract function validate(array $data): array;
    protected abstract function getResource(): \Nette\Security\Resource;

    protected function getActionCountResource(): \Nette\Security\Resource
    {
        return $this->getResource();
    }

    protected function getActionCountPermission(): string
    {
        return $this->getActionListPermission();
    }

    public function actionCount(): void
    {
        if(!$this->user->isAllowed(
            $this->getActionCountResource(), 
            $this->getActionCountPermission()
            ))
        {
            throw new \Nette\Application\ForbiddenRequestException();
        }
    }

    public function renderCount(): void
    {
        $query = $this->getListQuery(0, 0);

        $request = $this->getHttpRequest();
        $params = $request->getQuery();

        $filters = $this->getListFilters();
        foreach($filters as $filter)
        {
            if(!$filter instanceof Filters\Filter)
            {
                throw new \InvalidArgumentException("Unable to filter the list: Item '".$this->getName()."' is not an instance of Filter.");
            }

            if($filter->isValid($params))
            {
                $filter->applyFilterToQuery($query, $params);
            }
        }

        $response = new CountResponse();
        $response->count = $query->count();

        $this->sendResponse(
            new \Nette\Application\Responses\JsonResponse(
                $response
            )
        );                
    }

    protected function getActionListResource(): \Nette\Security\Resource
    {
        return $this->getResource();
    }

    protected function getActionListPermission(): string
    {
        return "list";
    }

    public function actionList(string $order = null, int $limit = 100, int $page = 0): void
    {
        if(!$this->user->isAllowed(
            $this->getActionListResource(), 
            $this->getActionListPermission()
            ))
        {
            throw new \Nette\Application\ForbiddenRequestException();
        }
    }

    public function renderList(string $order = null, int $limit = 100, int $page = 0): void
    {
        $query = $this->getListQuery($limit, $page);

        $request = $this->getHttpRequest();
        $params = $request->getQuery();

        $filters = $this->getListFilters();
        foreach($filters as $filter)
        {
            if(!$filter instanceof Filters\Filter)
            {
                throw new \InvalidArgumentException("Unable to filter the list: Item '".$this->getName()."' is not an instance of Filter.");
            }

            if($filter->isValid($params))
            {
                $filter->applyFilterToQuery($query, $params);
            }
        }

        if ($order !== null)
        {
            $orderItems = Order::parseOrder($order);
            foreach($orderItems as $item)
            {
                if(!$item instanceof OrderItem)
                {
                    throw new \LogicException("Invalid order item.");
                }

                $query = $query->orderBy($item->column, $item->direction);
            }
        }
        else
        {
            $model =  $this->getModel();
            $query = $query->orderBy($model->getPrimaryKeyName(), "DESC");
        }

        $list = $query->fetchAll();
        $this->afterList($list);
    
        $response = new ListResponse();
        $response->items = $list;

        $this->sendResponse(
            new \Nette\Application\Responses\JsonResponse(
                $response
            )
        );                
    }

    protected function afterList(array &$list): void
    {        
    }

    protected function getListFilters(): array 
    {
        $filters = [];
        return $filters;
    }

    protected function getListQuery(int $limit = 0, int $page = 0): \Dibi\Fluent 
    {
        $model =  $this->getModel();
        $query = $model->findAll($page, $limit);

        $request = $this->getHttpRequest();
        $params = $request->getQuery();

        $filters = $this->getListFilters();
        foreach($filters as $filter)
        {
            if(!$filter instanceof Filters\Filter)
            {
                throw new \InvalidArgumentException("Unable to filter the list: Item '".$this->getName()."' is not an instance of Filter.");
            }

            if($filter->isValid($params))
            {
                $filter->applyFilterToQuery($query, $params);
            }
        }

        return $query;
    }

    protected function getActionShowResource(int $id): \Nette\Security\Resource
    {
        return $this->getResource();
    }

    protected function getActionShowPermission(): string
    {
        return "show";
    }

    public function actionShow( int $id ): void
    {		
        if(!$this->user->isAllowed($this->getActionShowResource($id), "show"))
        {
            throw new \Nette\Application\ForbiddenRequestException();
        }
    }

    public function renderShow( int $id ): void
    {		
        $item = $this->getEntityAsArray($id);
        $this->afterShow($id, $item);

        $this->sendResponse(
            new \Nette\Application\Responses\JsonResponse(
                $item
            )
        );                
    }
    
    protected function afterShow(int $id, array &$item): void
    {        
    }

    private function doValidation(array $data): void
    {
        $errors = $this->validate($data);
        if(count($errors) > 0)
        {
            \Tracy\Debugger::barDump($errors, "Validation errors");

            $response = new \Nette\Application\Responses\JsonResponse($errors);
            //$this->getHttpResponse()->setCode(\Nette\Http\IResponse::S400_BAD_REQUEST);

            $this->sendResponse(
                $response
            );
        }        
    }

    protected function getActionAddResource(): \Nette\Security\Resource
    {
        return $this->getResource();
    }

    protected function getActionAddPermission(): string
    {
        return "add";
    }

    public function actionAdd(): void
    {
        if(!$this->user->isAllowed(
            $this->getActionAddResource(),
            $this->getActionAddPermission()
            ))
        {
            throw new \Nette\Application\ForbiddenRequestException();
        }
    }

    public function renderAdd(): void
    {
        $request = $this->getHttpRequest();
        $body = $request->getRawBody();

        if(!is_string($body) || strlen($body) == 0)
        {
            throw new \Nette\Application\BadRequestException("Action 'add' required non-empty body.");
        }
        
        $json = \Nette\Utils\Json::decode($body, \Nette\Utils\Json::FORCE_ARRAY);
        \Tracy\Debugger::barDump($json, "JSON");
        $this->doValidation($json);

        $this->beforeAdd($json);        
        $item = $this->add($json);
        $this->afterAdd($item, $json);
        
        //
        //
        // Send response
        
        $this->sendResponse(
            new \Nette\Application\Responses\JsonResponse(
                $item
            )
        );        
    }
    
    protected function beforeAdd(array &$item): void
    {            
    }
    
    protected function add(array $data): array
    {
        $model = $this->getModel();
        $item = $model->getEmptyObject();

        foreach($data as $key => $value)
        {
            if(array_key_exists($key, $item))
            {
                $item[$key] = $value;
            }
        }

        \Tracy\Debugger::barDump($item, "Item immediately before insert:");

        $model->insert($item);

        \Tracy\Debugger::barDump($item, "Item immediately after insert:");

        return $item;
    }

    protected function afterAdd(array &$item, array $values): void
    {            
    }

    protected function getActionEditResource(int $id): \Nette\Security\Resource
    {
        return $this->getResource();
    }

    protected function getActionEditPermission(): string
    {
        return "edit";
    }

    public function actionEdit( int $id ): void
    {
        if(!$this->user->isAllowed(
            $this->getActionEditResource($id), 
            $this->getActionEditPermission()
            ))
        {
            throw new \Nette\Application\ForbiddenRequestException();
        }
    }

    public function renderEdit( int $id ): void
    {
        
        $request = $this->getHttpRequest();
        $body = $request->getRawBody();        

        if(!is_string($body) || strlen($body) == 0)
        {
            throw new \Nette\Application\BadRequestException("Action 'edit' required non-empty body.");
        }
        
        $json = \Nette\Utils\Json::decode($body, \Nette\Utils\Json::FORCE_ARRAY);
        \Tracy\Debugger::barDump($json, "JSON");
        $this->doValidation($json);
        
        $this->beforeEdit($id, $json);
        $item = $this->edit($id, $json);
        $this->afterEdit($id, $item, $json);
        
        $this->sendResponse(
            new \Nette\Application\Responses\JsonResponse(
                $item
            )
        );
    }

    protected function beforeEdit(int $id, array &$values): void
    {            
    }
    
    protected function edit(int $id, array $data): array
    {
        $model = $this->getModel();
        unset($data[$model->getPrimaryKeyName()]);  // Do not overwrite the item ID

        $item = $this->getEntityAsArray($id);

        foreach($data as $key => $value)
        {
            if(array_key_exists($key, $item))
            {
                $item[$key] = $value;
            }
        }

        \Tracy\Debugger::barDump($item, "beforeUpdate");

        $model->update($id, $item);

        \Tracy\Debugger::barDump($item, "afterUpdate");

        return $item;
    }

    protected function afterEdit(int $id, array &$item, array $values): void
    {            
    }
    
    protected function getActionDeleteResource(int $id): \Nette\Security\Resource
    {
        return $this->getResource();
    }

    protected function getActionDeletePermission(): string
    {
        return "delete";
    }

    public function actionDelete( int $id ): void
    {
        if(!$this->user->isAllowed(
            $this->getActionDeleteResource($id),
            $this->getActionDeletePermission()
            ))
        {
            throw new \Nette\Application\ForbiddenRequestException();
        }

        $model = $this->getModel();
        $item = $this->getEntityAsArray($id);
        \Tracy\Debugger::barDump($item, "beforeDelete");
        $this->beforeDelete($id, $item);
        $model->delete($id);
        $this->afterDelete($id, $item);
    
        $this->sendResponse(
            new \Nette\Application\Responses\JsonResponse(
                $item
            )
        );
    }

    public function renderDelete( int $id ): void
    {		
    }
    
    protected function beforeDelete(int $id, array &$item): void
    {            
    }
    
    protected function afterDelete(int $id, array &$item): void
    {            
    }
    
    protected function getEntityAsArray(int $id): array
    {
        $model=  $this->getModel();
        $row = $model->find($id, false)->fetch();

        if($row === null)
        {
            throw new \Exception("Unable to fetch data of requested item.");
        }

        if(is_array($row))
        {
            $item = $row;
        } 
        else // $row instanceof Dibi\Row
        {
            $item = $row->toArray();
        }

        return $item;
    }
}
