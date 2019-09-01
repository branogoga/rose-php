<?php

declare(strict_types=1);

namespace Rose\Application\UI\Presenter;

abstract class ApiPresenter extends Presenter
{
    protected abstract function getModel(): \Rose\Data\Model\Model;
    protected abstract function validate(array $data): array;
    protected abstract function getResource(): string;

    protected function getActionListResource()
    {
        return $this->getResource();
    }

    protected function getActionListPermission(): string
    {
        return "list";
    }

    public function actionList(int $limit = 100, int $page = 0): void
    {
        if(!$this->user->isAllowed(
            $this->getActionListResource(), 
            $this->getActionListPermission()
            ))
        {
            throw new \Nette\Application\ForbiddenRequestException();
        }
    }

    public function renderList(int $limit = 100, int $page = 0): void
    {
        $model=  $this->getModel();
        $list = $model->findAll($page, $limit)->fetchAll();

        $this->sendResponse(
            new \Nette\Application\Responses\JsonResponse(
                $list
            )
        );                
    }

    protected function getActionShowResource(int $id)
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

    protected function getActionAddResource()
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

        if(!$body)
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

    protected function getActionEditResource(int $id)
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

        if(!$body)
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
    
    protected function getActionDeleteResource(int $id)
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
        $row = $model->find($id)->fetch();

        if(!$row)
        {
            throw new \Exception("Unable to fetch data of requested item.");
        }

        if(is_array($row))
        {
            $item = $row;
        } 
        else if ($row instanceof \Dibi\Row)
        {
            $item = $row->toArray();
        }

        return $item;
    }
}
