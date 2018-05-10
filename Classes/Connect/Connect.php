<?php
namespace Pits\PitsGooglesitemap\Connect;

/* * *************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2005 - 2006 Maximo Cuadros [Gobernalia Global Net] <typo3@markus-blaschke.de> (metaseo)
 *  (c) 2006 - 2018 PIT Solutions AG
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Class Connect
 * @package Pits\PitsGooglesitemap\Connect
 * @version 8.7.13
 */
class Connect implements \TYPO3\CMS\Core\SingletonInterface
{

    private $_connection;
    private static $_instance; //The single instance
    private $localConf;
    private $host;
    private $user;
    private $password;
    private $db;

    /**
     * getInstance
     *
     * @return Connect
     */
    public static function getInstance()
    {
        if (!self::$_instance) { // If no instance then make one
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Connect constructor.
     */
    private function __construct()
    {
        try {

            $this->localConf = include 'typo3conf/LocalConfiguration.php';
            $this->host = $this->localConf['DB']['host'];
            $this->user = $this->localConf['DB']['username'];
            $this->password = $this->localConf['DB']['password'];
            $this->db = $this->localConf['DB']['database'];
            $this->_connection = mysqli_connect($this->host, $this->user, $this->password, $this->db);
            return $this->_connection;
        } catch (Exception $e) {
            return "PHP Fehler: " . $e->getMessage();
        }
    }
}

