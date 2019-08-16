<?php

declare(strict_types=1);

namespace Rose\Application\UI\Presenter;

require_once("Presenter.php");

abstract class ApiPresenter extends Presenter
{
    protected abstract function getModel(): \Rose\Data\Model\Model;
    protected abstract function validate(array $data): array;

    public function actionList(): void
    {		
    }

    public function renderList(): void
    {
        $model=  $this->getModel();
        $list = $model->findAll()->fetchAll();

        $this->sendResponse(
            new \Nette\Application\Responses\JsonResponse(
                $list
            )
        );                
    }

    public function actionShow( int $id ): void
    {		
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
            \Tracy\Debugger::barDump($errors);

            $response = new \Nette\Application\Responses\JsonResponse($errors);
            //$this->getHttpResponse()->setCode(\Nette\Http\IResponse::S400_BAD_REQUEST);

            $this->sendResponse(
                $response
            );
        }        
    }

    public function actionAdd(): void
    {
    }

    public function renderAdd(): void
    {
        $request = $this->getHttpRequest();
        $body = $request->getRawBody();

        if(!$body)
        {
            throw new \Exception("Action 'add' required non-empty body.");
        }
        
        $json = \Nette\Utils\Json::decode($body, \Nette\Utils\Json::FORCE_ARRAY);
        \Tracy\Debugger::barDump($json);
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

        \Tracy\Debugger::barDump($item);

        $model->insert($item);

        \Tracy\Debugger::barDump($item);

        return $item;
    }

    protected function afterAdd(array &$item, array $values): void
    {            
    }

    public function actionEdit( int $id ): void
    {
    }

    public function renderEdit( int $id ): void
    {
        
        $request = $this->getHttpRequest();
        $body = $request->getRawBody();        

        if(!$body)
        {
            throw new \Exception("Action 'edit' required non-empty body.");
        }
        
        $json = \Nette\Utils\Json::decode($body, \Nette\Utils\Json::FORCE_ARRAY);
        \Tracy\Debugger::barDump($json);
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

        \Tracy\Debugger::barDump($item);

        $model->update($id, $item);

        \Tracy\Debugger::barDump($item);

        return $item;
    }

    protected function afterEdit(int $id, array &$item, array $values): void
    {            
    }
    
    public function actionDelete( int $id ): void
    {
        $model = $this->getModel();
        $item = $this->getEntityAsArray($id);
        \Tracy\Debugger::barDump($item);
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