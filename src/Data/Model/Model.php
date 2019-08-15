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

	public function getAll( $joinTables = true )
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

	public function	isIdValid( $id ): boolean
	{            
		$count = \dibi::getConnection()
						->select('COUNT(*)')
						->from($this->getTableName())
						->where( $this->getPrimaryKeyName()." = %i ", $id )
						->execute()
						->fetchSingle();
		//Nette\Debug::dump($count);
		return ($count == 1);				
	}

	public function count(): int
	{
		return \dibi::getConnection()
						->select('COUNT(*)')
						->from($this->getTableName())
						->execute()
						->fetchSingle();		
	}

	public function findAll( $page = 0, $limit = 10 )
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

	public function find($id, $joinTables = true )
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

    public function save( &$data )
    {
        if(array_key_exists($this->getPrimaryKeyName(), $data) && $this->isIdValid($data[$this->getPrimaryKeyName()]))
        {
            return $this->update($data[$this->getPrimaryKeyName()], $data);
        }
        else
        {
            return $this->insert($data);
        }            
    }
        
    public function update($id, $data)
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

    public function insert(&$data)
    {
        unset( $data[$this->getPrimaryKeyName()] );

        $this->beforeInsert( $data );

        //Debug::fireLog(__METHOD__);	
        $result = \dibi::getConnection()
            ->insert($this->getTableName(), $data)
            ->execute();
        
        $data[$this->getPrimaryKeyName()] = \dibi::getInsertId();

        //dibi::test( dibi::$sql );

        $this->afterInsert( NULL, $data );

        return $result;
    }
    
    protected function beforeInsert( &$data ): void
    {            
    }
    
    protected function afterInsert( $id, $data ): void
    {            
    }

    protected function getDeletedColumnName(): string {
        return null;
    }

    public function delete($id) {
        $this->beforeDelete($id);

        if($this->getDeletedColumnName()) {
            $result = $this->markRowAsDeleted($id);
        } else {
            $result = $this->deleteRow($id);            
        }

        //dibi::test( dibi::$sql );

        $this->afterDelete($id);

        return $result;
    }

    private function deleteRow($id) {
        $result = \dibi::getConnection()
                ->delete($this->getTableName())
                ->where($this->getPrimaryKeyName() . '=%i', $id)
                ->execute();

        return $result;
    }

    private function markRowAsDeleted($id) {

        $values = [];
        $values[$this->getDeletedColumnName()] = (new \DateTimeImmutable())->format("Y-m-d H:i:s");
        
        $result = \dibi::getConnection()
                ->update(
                        $this->getTableName(),
                        $values
                        )
                ->where($this->getPrimaryKeyName() . '=%i', $id)
                ->execute();

        return $result;
    }
    
    protected function beforeDelete($id): void
    {            
    }
    
    protected function afterDelete($id): void
    {            
    }
}
