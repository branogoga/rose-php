<?php

declare(strict_types=1);

namespace Rose\Data\Model;

/**
 * Abstrakt model.
 */
abstract class Model
{
     use \Nette\SmartObject;
    
	/** @var string */
	abstract protected 	function	getTableName(): string;
	abstract public 	function	getPrimaryKeyName(): string;

	/// Alias of main table. Used to specify for conflicting rows 
	/// in JOINed tables (find, getAll)).
	protected function	getTableAlias(): string
	{
		return "tmp";
	}

	protected function	getTable(): string
	{
		return	" ".$this->getTableName()." AS ".$this->getTableAlias()." ";
	}

	/// Returns SELECT clause with joined all tables related to this query
	/// Used to resolve conflicts in the column names
	protected  function	getAllTablesJoinSelectClause(): string
	{
		return " * ";
	}

	/// Returns FROM clause with joined tables
	protected  function	getAllTablesJoinFromClause(): string
	{
		return $this->getTable();
	}
	
	public static function initialize(): void
	{
	}

	public function __construct()
	{
	}

	public function getAll( $joinTables = true ): \Dibi\DataSource
	{
		$select = "*";
		$from	= $this->getTable();
		
		if($joinTables)
		{
			$select = $this->getAllTablesJoinSelectClause();
			$from	= $this->getAllTablesJoinFromClause();
		}	
		
		$sql = "SELECT ".$select."
				FROM ".$from;
        
        if($this->getDeletedColumnName())
        {
            $sql .= " WHERE ".$this->getDeletedColumnName()." IS NULL";
        }
		
		return	\dibi::dataSource( $sql );
	}

	public function	isIdValid( $id ): bool
	{            
		$result = \dibi::getConnection()
						->select('COUNT(*)')
						->from($this->getTableName())
						->where( $this->getPrimaryKeyName()." = %i ", $id )
                        ->execute();

        if(!$result instanceof \Dibi\Result)
        {
            throw new \Exception("Result must be an instance of Dibi\Result.");
        }

        $count = $result->fetchSingle();
		//Nette\Debug::dump($count);
		return ($count == 1);				
	}

	public function count(): int
	{
		$result = \dibi::getConnection()
						->select('COUNT(*)')
						->from($this->getTableName())
                        ->execute();

        if(!$result instanceof \Dibi\Result)
        {
            throw new \Exception("Result must be an instance of Dibi\Result.");
        }

        $count = $result->fetchSingle();

        return $count;
	}

	public function findAll( $page = 0, $limit = 10 ): \Dibi\Fluent
	{
		$query = \dibi::getConnection()
            ->select($this->getAllTablesJoinSelectClause())
            ->from($this->getAllTablesJoinFromClause())
            ;
        
        if($this->getDeletedColumnName())
        {
            $query->where($this->getDeletedColumnName()." IS NULL");
        }
        
        $query
            ->limit($limit)
            ->offset($page*$limit);
        
        return $query;		
	}

	public function find($id, $joinTables = true ): \Dibi\Fluent
	{
		$select = "*";
		$from	= $this->getTable();
		
		if($joinTables)
		{
			$select = $this->getAllTablesJoinSelectClause();
			$from	= $this->getAllTablesJoinFromClause();
		}
			
		return \dibi::getConnection()
            ->select($select)
            ->from($from)
            ->where(/*$this->getTableAlias().".".*/$this->getPrimaryKeyName().'=%i', $id);
	}

    public function save( &$data ): bool
    {
        if(array_key_exists($this->getPrimaryKeyName(), $data) && $this->isIdValid($data[$this->getPrimaryKeyName()]))
        {
            $numberOfAffectedRows = $this->update($data[$this->getPrimaryKeyName()], $data);
            if($numberOfAffectedRows > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            $id = $this->insert($data);
            if(is_int($id) && $id > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }            
    }
        
    public function update($id, $data): int
	{
		if(array_key_exists($this->getPrimaryKeyName(), $data))
		{
			unset( $data[$this->getPrimaryKeyName()] );
		}
                
        $this->beforeUpdate( $id, $data );
		
		$result = \dibi::getConnection()
            ->update($this->getTableName(), $data)
            ->where($this->getPrimaryKeyName().'=%i', $id)			
            ->execute();
						
        //dibi::test( dibi::$sql );
        
        if(!is_int($result))
        {
            throw new \Exception("SQL update should return number of affected rows.");
        }
                
        $data[$this->getPrimaryKeyName()] = $id;
            
        $this->afterUpdate( $id, $data );
		
		return $result;
	}
        
    protected function beforeUpdate( $id, &$data ): void
    {            
    }
    
    protected function afterUpdate( $id, $data ): void
    {            
    }

    public function insert(&$data): int
    {
        unset( $data[$this->getPrimaryKeyName()] );

        $this->beforeInsert( $data );

        //Debug::fireLog(__METHOD__);	
        $result = \dibi::getConnection()
            ->insert($this->getTableName(), $data)
            ->execute();
        
        $data[$this->getPrimaryKeyName()] = \dibi::getInsertId();

        //dibi::test( dibi::$sql );

        if(!is_int($result))
        {
            throw new \Exception("SQL insert should return ID of new item.");
        }
                
        $this->afterInsert( NULL, $data );

        return $result;
    }
    
    protected function beforeInsert( &$data ): void
    {            
    }
    
    protected function afterInsert( $id, $data ): void
    {            
    }

    protected function getDeletedColumnName(): ?string {
        return null;
    }

    public function delete($id): int {
        $this->beforeDelete($id);

        if($this->getDeletedColumnName()) {
            $result = $this->markRowAsDeleted($id);
        } else {
            $result = $this->deleteRow($id);            
        }

        //dibi::test( dibi::$sql );

        $this->afterDelete($id);

        if(!is_int($result))
        {
            throw new \Exception("SQL delete should return number of affected rows.");
        }
                
        return $result;
    }

    private function deleteRow($id): int {
        $result = \dibi::getConnection()
            ->delete($this->getTableName())
            ->where($this->getPrimaryKeyName() . '=%i', $id)
            ->execute();

        if(!is_int($result))
        {
            throw new \Exception("SQL delete should return number of affected rows.");
        }
                
        return $result;
    }

    private function markRowAsDeleted($id): int {

        $values = [];
        $values[$this->getDeletedColumnName()] = (new \DateTimeImmutable())->format("Y-m-d H:i:s");
        
        $result = \dibi::getConnection()
            ->update(
                $this->getTableName(),
                $values
            )
            ->where($this->getPrimaryKeyName() . '=%i', $id)
            ->execute();

        if(!is_int($result))
        {
            throw new \Exception("SQL update should return number of affected rows.");
        }
                
        return $result;
    }
    
    protected function beforeDelete($id): void
    {            
    }
    
    protected function afterDelete($id): void
    {            
    }
}
