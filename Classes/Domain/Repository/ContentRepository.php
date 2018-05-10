<?php
namespace Pits\McGooglesitemap\Domain\Repository;

/* * *************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017
 *
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

use Pits\McGooglesitemap\Utility\DatabaseUtility;

/**
 * Class ContentRepository
 * @package Pits\McGooglesitemap\Domain\Repository
 * @version 8.7.13
 */
class ContentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * localConf
     *
     * @var array
     */
    public $localConf;

    /**
     * host
     *
     * @var string
     */
    public $host;

    /**
     * user
     *
     * @var string
     */
    public $user;

    /**
     * password
     *
     * @var string
     */
    public $password;

    /**
     * db
     *
     * @var string
     */
    public $db;

    /**
     * con
     *
     * @var string
     */
    public $con;

    /**
     * Example for repository wide settings
     * Initialize Object
     *
     * @return void
     */
    public function initializeObject()
    {
        /** @var $defaultQuerySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        // add the pid constraint
        $defaultQuerySettings->setRespectStoragePage(TRUE);
    }

    /**
     * Executes Custom Query Using Database Utility
     *
     * @param $sql
     * @return \mysqli_result
     * @throws \Exception
     */
    public function customQuery($sql)
    {
        $res = DatabaseUtility::query($sql);
        return $res;
    }

    /**
     * createCustomQuery
     *
     * @param $sql
     * @return mixed
     */
    public function createCustomQuery($sql)
    {
        $query = $this->createQuery();
        $result = $query->statement($sql)->execute();
        return $result;
    }
}
