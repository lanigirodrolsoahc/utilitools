<?php

namespace Utilitools;

class SQLiteUnavailableException extends \Exception {}

/**
 * @version MySQL
 * @version MariaDB
 * @version SQLite
 */
class Database
{
    use Instanced;

    private
    const   TYPE_IN_MARKER      = 'formInParams';

    public
    const   PER_PAGE            = 50,
            PER_PAGE_SMALL      = 5,
            SORTING_ASC         = 'ASC',
            SORTING_DESC        = 'DESC';

    public
    const   SQLite = 'sqlite';

    private $isReading          = false,
            $pwd,
            $tries              = 0;

    public  $allowPagination    = true,
            $current            = false,
            $database,
            $lastId             = false,
            $lastSql,
            $next               = false,
            $ok                 = false,
            $orderBy            = [],
            $pages              = 0,
            $perPage            = self::PER_PAGE,
            $previous           = false,
            $queryParameters    = [],
            $results            = [],
            $rows,
            $sqlError,
            $statement,
            $total              = 0;

    /**
     * Use `Database::Instance()`
     */
    private
    function __construct ()
    {
        $this->connect();
    }

    /**
     * connects to database
     *
     * @param   string  $pwd    allows params to stay put between home and work
     */
    protected
    function connect ( string $pwd = '' ) : void
    {
        try
        {
            if ( $SQLite = ( $system = System::Instance() )->{ System::SQLite_FILE } ?? false )
            {
                if ( ! \in_array( self::SQLite, \PDO::getAvailableDrivers() ) )
                    throw new SQLiteUnavailableException("Missing SQLite driver!");

                $database = new \PDO( \sprintf('%1$s:%2$s', self::SQLite, $SQLite) );
            }
            else
            {
                $host       = $system->{ System::SQL_HOST } ?: 'localhost';
                $port       = $system->{ System::SQL_PORT } ?: 3306;
                $dbName     = $system->{ System::SQL_BASE };

                $database   = new \PDO(
                    "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4",
                    $system->{ System::SQL_USER },
                    ( $pwd ?: false )
                        ? $system->{ System::SQL_PWD_WORK }
                        : $pwd,
                    []
                );

                $database->setAttribute( \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false );
            }

            $database->setAttribute( \PDO::ATTR_EMULATE_PREPARES, false );
            $database->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
            $database->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ );

            $this->database = $database;
            $this->pwd      = $pwd;
        }
        catch ( \Throwable $t )
        {
            $this->database = null;

            \error_log( $t->getMessage() );
        }

        $this->tries++;

        if (
            \is_null($this->database)
            && ( $this->tries % 2 ) !== 0
            && ! ( $SQLite ?? false )
        )
            $this->connect( System::Instance()->{ System::SQL_PWD_HOME } );
    }

    /**
     * did your last request find anything?
     *
     * @return  bool
     */
    public
    function found () : bool
    {
        return $this->ok && $this->rows > 0;
    }

    /**
     * sets reading mode
     *
     * @param   ?bool   $unset
     *
     * @return  Database
     */
    public
    function isReading ( bool $unset = false ) : Database
    {
        $this->isReading = ! $unset;

        return $this;
    }

    /**
     * ends connection
     */
    public
    function kill () : void
    {
        if ( $this->statement instanceof \PDOStatement )
            $this->statement->closeCursor();

        $this->statement = null;
        $this->results = [];
        $this->database = null;
    }

    /**
     * erazes query parameters
     *
     * @return  Database
     */
    public
    function noQueryParameters () : Database
    {
        $this->queryParameters = [];

        return $this;
    }

    /**
     * paginates SQL
     *
     * @param   string  &$sql
     */
    private
    function paginate ( string &$sql ) : Database
    {
        do
        {
            $total = 'total';

            if ( ! $this->current ) break;

            $this->allowPagination  = false;

            $this
                ->isReading(true)
                ->query("SELECT COUNT(*) AS `{$total}` FROM ({$sql}) x");

            if ( ! ( $res = $this->results[0] ?? false ) ) break;

            $this->allowPagination  = true;
            $this->total            = $res->$total;
            $this->pages            = ceil($this->total / $perPage = $this->perPage);
            $this->current          = $this->current > $this->pages ? $this->pages : $this->current;
            $this->previous         = ( $previous = $this->current - 1 ) < 1 ? false : $previous;
            $this->next             = ( $next = $this->current + 1 ) > $this->pages ? false : $next;

            $offset = ( $offset = $previous * $this->perPage ) < 0 ? 0 : $offset;
            $sql    .= " LIMIT {$perPage} OFFSET {$offset}";

            $this->perPage = self::PER_PAGE;

            $this->setPage();
        }
        while ( 0 );

        return $this;
    }

    /**
     * uses PDO to execute a prepared query
     *
     * @param   string|array    $sql
     *
     * @return  Database
     */
    public
    function query ( $sql ) : Database
    {
        $this->statement    = null;
        $this->results      = [];
        $this->ok           = false;
        $this->sqlError     = null;
        $this->lastId       = false;

        if ( \is_string($sql) )
        {
            try {
                if ( ! empty($this->orderBy) )
                {
                    $imploded       = implode(', ', $this->orderBy);
                    $sql            .= " ORDER BY {$imploded}";
                    $this->orderBy  = [];
                }

                if ( $this->allowPagination )   $this->paginate($sql);
                if ( $this->isReading )         $this->reduceInTypes();

                $this->statement        = $this->database->prepare($sql);

                $this->statement->execute($this->queryParameters ?? null);

                $this->results          = $this->statement->fetchAll();
                $this->rows             = count($this->results);
                $this->lastSql          = $sql;
                $this->ok               = true;
                $this->lastId           = ($last = $this->database->lastInsertId()) == 0 ? false : $last;
            }
            catch ( \Throwable $t ) {
                $this->ok           = false;
                $this->sqlError     = $t->getMessage();

                \error_log(\json_encode(
                    Std::__new()
                        ->sql($sql)
                        ->msg($this->sqlError)
                        ->from(\sprintf('%1$s::%2$s', \get_called_class(), __FUNCTION__))
                ));
            }
        }
        elseif ( \is_array($sql) )
            foreach ( $sql as $request )
                if ( $this->ok )
                    $this->query($request);

        return $this;
    }

    /**
     * reduces `IN` types to simple arguments
     */
    private
    function reduceInTypes () : void
    {
        foreach ( $this->queryParameters ?? [] as $field => $value )
            if ( ! \is_array($value) )
                continue;
            elseif ( strpos( key($value) ?? '', self::TYPE_IN_MARKER ) === false )
                continue;
            else
            {
                $this->queryParameters = \array_merge(
                    $this->queryParameters,
                    Sql::Instance()->formInParams($value)
                );

                unset( $this->queryParameters[$field] );
            }
    }

    /**
     * - sets orderBy for next SQL request
     * - calling `null` empties any orderBy previously set
     *
     * @param   ?string|?array      $fieldName
     * @param   ?bool               $ascending
     *
     * @return  Database
     *
     * @todo multisort
     */
    public
    function setOrderBy ( $fieldName = null, bool $ascending = true ) : Database
    {
        if ( \is_null($fieldName) ) $this->orderBy = [];
        else
            $this->orderBy[] = \implode(
                ', ',
                \array_map(
                    fn ( string $field ) : string => \sprintf(
                        '`%1$s` %2$s',
                        $field,
                        $ascending ? self::SORTING_ASC : self::SORTING_DESC
                    ),
                    $fieldName = \is_array($fieldName) ? $fieldName : [$fieldName]
                )
            );

        return $this;
    }

    /**
     * sets the page to target or cancels it
     *
     * @param   ?int    $page
     *
     * @return  Database
     */
    public
    function setPage ( ?int $page = 0 ) : Database
    {
        $this->current = $page < 1 ? false : \abs($page);

        return $this;
    }
}
