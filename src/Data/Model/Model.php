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
    abstract public     function    getEmptyObject(): array;

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

	protected  function	getSelectClause(): string
	{
            return "*"; 
	}
        
	/// Vrati SELECT klazulu pre dotaz na vsetky zaujimave tabulky
	/// Prepisanim sa daju riesit konflikty nazvov stlpcov
	protected  function	getAllTablesJoinSelectClause( array $joinedTables = [] ): string
	{
            return $this->getSelectClause();
	}

    protected function      getFromClause(): string
    {
        return $this->getSelectClause();
    }

	/// Returns FROM clause with joined tables
	protected  function	getAllTablesJoinFromClause( array $joinedTables = [] ): string
	{
		return $this->getTable();
	}
	
	public static function initialize(): void
	{
	}

	public function __construct()
	{
	}

	public function getAll( bool $joinTables = true, array $joinedTables = [] ): \Dibi\DataSource
	{
		$select = $this->getFromClause();
		$from	= $this->getTable();
		
		if($joinTables)
		{
			$select = $this->getAllTablesJoinSelectClause( $joinedTables );
			$from	= $this->getAllTablesJoinFromClause( $joinedTables );
		}	
		
		$sql = "SELECT ".$select."
				FROM ".$from;
        
        if($this->getDeletedColumnName() !== null && strlen($this->getDeletedColumnName()) > 0)
        {
            $sql .= " WHERE ".$this->getDeletedColumnName()." IS NULL";
        }
		
		return	\dibi::dataSource( $sql );
	}
    
	public function getAllNew( bool $joinTables = true, array $joinedTables = [] ): \Dibi\Fluent
	{
            $select = $this->getFromClause();
            $from	= $this->getTable();

            if($joinTables)
            {
                $select = $this->getAllTablesJoinSelectClause( $joinedTables );
                $from	= $this->getAllTablesJoinFromClause( $joinedTables );
            }	

            return \dibi::select($select)->from($from);
	}

	//public function count()
	//{
    //        return \Dibi::getConnection()
    //            ->select('COUNT(*)')
    //            ->from($this->getTableName())
    //            ->execute()
    //            ->fetchSingle();		
	//}
    
	public function	isIdValid( ?string $id ): bool
	{     
        if($id === null)
        {
            return false;
        }
        
		$result = \dibi::getConnection()
						->select('COUNT(*)')
						->from($this->getTableName())
						->where( $this->getPrimaryKeyName()." = %i ", $id )
                        ->execute();

        if(!$result instanceof \Dibi\Result)
        {
            throw new \Exception("Result must be an instance of \Dibi\Result.");
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
            throw new \Exception("Result must be an instance of \Dibi\Result.");
        }

        $count = $result->fetchSingle();

        return $count;
	}

    const   NoLimit = -1;
	public function findAll( int $page = 0, int $limit = 10, array $joinedTables = [] ): \Dibi\Fluent
	{
		$query = \dibi::getConnection()
            ->select($this->getAllTablesJoinSelectClause($joinedTables))
            ->from($this->getAllTablesJoinFromClause($joinedTables))
            ;
        
        if($this->getDeletedColumnName() !== null && strlen($this->getDeletedColumnName()) > 0)
        {
            $query->where($this->getDeletedColumnName()." IS NULL");
        }
        
        if($limit > 0)
        {
          $query
            ->limit($limit)
            ->offset($page*$limit);
        }
        
        return $query;		
	}

	public function find(int $id, bool $joinTables = true, array $joinedTables = [] ): \Dibi\Fluent
	{
		$select = $this->getSelectClause();
		$from	= $this->getTable();
		
		if($joinTables)
		{
			$select = $this->getAllTablesJoinSelectClause($joinedTables);
			$from	= $this->getAllTablesJoinFromClause($joinedTables);
		}
			
		return \dibi::getConnection()
            ->select($select)
            ->from($from)
            ->where($this->getTableAlias().".".$this->getPrimaryKeyName().'=%i', $id);
	}

    public function findLast( bool $joinTables = true, array $joinedTables = [] ): \Dibi\Fluent
    {
        $select = "*";
        $from	= $this->getTable();

        if($joinTables)
        {
            $select = $this->getAllTablesJoinSelectClause($joinedTables);
            $from   = $this->getAllTablesJoinFromClause($joinedTables);
        }

        $query = \dibi::getConnection()
            ->select($select)
            ->from($from)
            ->orderBy( $this->getPrimaryKeyName(), 'DESC' );
            //->limit(1)
            //->offset(0);

        //echo $query->__toString();

        return $query;
    }
    
    public function getLastId(): int
    {
        $query = $this->findLast(false);
        $data = $query->fetch();
        return $data[$this->getPrimaryKeyName()];
    }

    public function save( array &$data ): bool
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
            if($id > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }            
    }
        
    protected function beforeUpdate( int $id, array &$data ): void
    {            
    }
    
    protected function doUpdate( int $id, array $data ): int
    {
        $result = \dibi::getConnection()
            ->update($this->getTableName(), $data)
            ->where($this->getPrimaryKeyName().'=%i', $id)			
            ->execute();

        if(is_int($result))
        {
            return $result;
        }
                
        return \dibi::getAffectedRows();
    }
               
    protected function afterUpdate( int $id, array $data ): void
    {            
    }

    public function update(int $id, array $data): int
	{
		if(array_key_exists($this->getPrimaryKeyName(), $data))
		{
			unset( $data[$this->getPrimaryKeyName()] );
		}
                
        $this->beforeUpdate( $id, $data );
		
		$result = $this->doUpdate($id, $data);
						
        //dibi::test( \dibi::$sql );
        
        $data[$this->getPrimaryKeyName()] = $id;
            
        $this->afterUpdate( $id, $data );
		
		return $result;
	}
        
    protected function beforeInsert( array &$data ): void
    {            
    }
    
    protected function afterInsert( int $id, array $data ): void
    {            
    }
    
    protected function doInsert( array $data ): int
    {
        $result = \dibi::getConnection()
            ->insert($this->getTableName(), $data)
            ->execute();
        
        return \dibi::getInsertId();
    }
    
    public function insert(array &$data): int
    {
        unset( $data[$this->getPrimaryKeyName()] );

        $this->beforeInsert( $data );

        //Debug::fireLog(__METHOD__);	
        $result = $this->doInsert($data);
        
        $id = \dibi::getInsertId();
        $data[$this->getPrimaryKeyName()] = $id;

        //dibi::test( \dibi::$sql );

        $this->afterInsert( $id, $data );

        return $result;
    }
    
    protected function beforeDelete(int $id): void
    {            
    }
    
    protected function afterDelete(int $id): void
    {            
    }
    
    public function delete(int $id): int 
    {
        $this->beforeDelete($id);

        if($this->getDeletedColumnName() !== null && strlen($this->getDeletedColumnName()) > 0)
        {
            $result = $this->markRowAsDeleted($id);
        }
        else 
        {
            $result = $this->deleteRow($id);            
        }

        //dibi::test( \dibi::$sql );

        $this->afterDelete($id);

        return $result;
    }

    public function deleteLast(): void
    {
        $data = $this->findLast(false)->fetch();
        
        $this->delete(
            $data[$this->getPrimaryKeyName()]
        );
    }
        
    protected function getDeletedColumnName(): ?string {
        return null;
    }

    private function deleteRow(int $id): int {
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

    private function markRowAsDeleted(int $id): int {

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
    
        public  function hasFulltextIndex(): bool
        {
            return false;
        }
        
        public function  getFulltextIndexColumns(): array
        {
            throw new \Rose\Exceptions\NotImplementedException();
            return [];            
        }
        
        private function addTablePrefixToColumnName( string $column ): string
        {
            return $this->getTableName().".".$column;
        }
        
        private function getFulltextIndexColumnsString( array $columns ): string
        {
            if(count($columns) == 0)
            {
                throw new \Rose\Exceptions\InvalidArgumentException();
            }

            $result = $columns[0];
            
            //Tracy\Debugger::dump($columns);
                    
            for($i=1; $i < count($columns); $i++)
            {
                $column = $columns[$i];                
                $result .= ','.$column;
            }
            
            //Tracy\Debugger::dump($result);
            
            return $result;
        }
        
        protected function createFulltextIndex( array $columns ): void
        {
            $query = "
                ALTER TABLE     ".$this->getTableName()."
                ADD FULLTEXT    vyhladavanie( ".
                                    $this->getFulltextIndexColumnsString(
                                            $this->getFulltextIndexColumns()
                                            )
                                   ." )
                ";
            
            //dibi::test( $query );            
            \dibi::query( $query );
        }
        
        protected function removeFulltextIndex(): void
        {
            $query = "
                ALTER TABLE     ".$this->getTableName()."
                DROP INDEX      vyhladavanie
                ";
            
            //dibi::test( $query );
            \dibi::query( $query );            
        }
        
        public function search( string $query ): \Dibi\Result
        {
            try {
                $this->createFulltextIndex(
                        $this->getFulltextIndexColumns()
                        );
            }
            catch( \Exception $e )
            {
                // Index already created.
            }
            
            $indexColumns = $this->getFulltextIndexColumnsString($this->getFulltextIndexColumns());
		
            $select = $this->getAllTablesJoinSelectClause();
            $from   = $this->getAllTablesJoinFromClause();
            
            $querySearch = "
                SELECT      $select, MATCH($indexColumns) AGAINST(%s) AS score
                FROM        ".$from."
                WHERE       MATCH($indexColumns) AGAINST(%s)
                ORDER BY    score DESC
                ";
            
            //dibi::test( $querySearch, $q, $q );
            
            $result = \dibi::query( $querySearch, $query, $query );        
            
            //$this->removeFulltextIndex();
            
            return $result;
        }
        
        public function queryRegExp( 
            string $query, 
            string $column, 
            bool $joinTables = true, 
            array $joinedTables = [] 
            ) : string
        {
            $regExp = \Rose\Utils\Strings\Charset::makePunctuationInsensitiveSearchRegularExpression($query);
            //Tracy\Debugger::dump($regExp);

            
            $select = "*";
            $from	= $this->getTable();
		
            if($joinTables)
            {
                $select = $this->getAllTablesJoinSelectClause($joinedTables);
                $from	= $this->getAllTablesJoinFromClause($joinedTables);
            }	

            $sql = "SELECT ".$select."
                    FROM ".$from."
                    WHERE $column REGEXP '$regExp'
                    ";
            
            return $sql;
        }
        
        public function searchRegExp( 
            string  $query, 
            string  $column, 
            bool    $joinTables = true, 
            array   $joinedTables = []
            ): \Dibi\Result
        {
            $sql = $this->queryRegExp( $query, $column, $joinTables, $joinedTables );
            //dibi::test($sql,$regExp);
            return \dibi::query($sql);
        }
        
        public function getNextId(): int
        {
            $sql = "SHOW TABLE STATUS LIKE '".$this->getTableName()."'";
            
            //Dibi::test($sql);
            
            $result = \dibi::query($sql)->fetch();
            //Tracy\Debugger::dump($result);
            
            return $result['Auto_increment'];
        }
        
        public function setNextId( int $id ): \Dibi\Result
        {
            $sql = "ALTER TABLE ".$this->getTableName()." AUTO_INCREMENT = $id";
            //Dibi::test($sql);
            $result = \dibi::query($sql); 
            return $result;           
        }
    
}
