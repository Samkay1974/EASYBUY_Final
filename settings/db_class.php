<?php
// DB credentials
include_once 'db_cred.php';

/**
 * PDO-based DB connection wrapper with some compatibility helpers
 */
if (!class_exists('db_connection')) {
    class db_connection
    {
        /** @var PDO|null */
        public $db = null;
        /** @var PDOStatement|null */
        protected $results = null;

        public function __construct()
        {
            $this->db_connect();
        }

        /**
         * Establish a PDO connection and store in $this->db
         * @return bool
         */
        public function db_connect()
        {
            if ($this->db instanceof PDO) return true;
            try {
                $dsn = 'mysql:host=' . SERVER . ';dbname=' . DATABASE . ';charset=utf8mb4';
                $this->db = new PDO($dsn, USERNAME, PASSWD, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                return true;
            } catch (PDOException $e) {
                $this->db = null;
                return false;
            }
        }

        /**
         * Compatibility: return PDO instance
         * @return PDO|false
         */
        public function db_conn()
        {
            return $this->db_connect() ? $this->db : false;
        }

        /** Execute a SELECT query and keep statement in $this->results */
        public function db_query($sql)
        {
            if (!$this->db_connect()) return false;
            try {
                $this->results = $this->db->query($sql);
                return ($this->results !== false);
            } catch (PDOException $e) {
                $this->results = null;
                return false;
            }
        }

        /** Execute write query (INSERT/UPDATE/DELETE) */
        public function db_write_query($sql)
        {
            if (!$this->db_connect()) return false;
            try {
                $res = $this->db->exec($sql);
                return ($res !== false);
            } catch (PDOException $e) {
                return false;
            }
        }

        /** Fetch single row from given SQL */
        public function db_fetch_one($sql)
        {
            if (!$this->db_query($sql)) return false;
            return $this->results->fetch(PDO::FETCH_ASSOC);
        }

        /** Fetch all rows from given SQL */
        public function db_fetch_all($sql)
        {
            if (!$this->db_query($sql)) return false;
            return $this->results->fetchAll(PDO::FETCH_ASSOC);
        }

        /** Return number of rows from last SELECT statement (best-effort) */
        public function db_count()
        {
            if ($this->results === null) return false;
            try {
                $count = $this->results->rowCount();
                if ($count === 0) {
                    // As fallback, fetchAll and count
                    $all = $this->results->fetchAll(PDO::FETCH_ASSOC);
                    $count = is_array($all) ? count($all) : 0;
                }
                return $count;
            } catch (PDOException $e) {
                return false;
            }
        }

        /** Last insert id */
        public function last_insert_id()
        {
            if (!$this->db_connect()) return false;
            try {
                return $this->db->lastInsertId();
            } catch (PDOException $e) {
                return false;
            }
        }
    }
}
