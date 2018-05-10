<?php
namespace Pits\PitsGooglesitemap\Connect;

use Databases;

include ('Connect.php');

class ProcessData extends \Pits\PitsGooglesitemap\Connect\Connect {

    /**
     * _request
     *
     * @var array
     */
    public $_request;

    /**
     * _Connect
     *
     * @var object
     */
    private $_Connect;

    /**
     * Constructor
     */
    function __construct() {
        $this->_Connect = parent::getInstance()->getConnection();
    }

    /**
     * Destructor
     */
    function __destruct() {
        //$this->_mysqlConnect->close();
    }
}
