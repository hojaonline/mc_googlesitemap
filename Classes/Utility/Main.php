<?php
namespace Pits\McGooglesitemap\Utility;

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

use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;

/**
 * Class DatabaseUtility
 * @package Pits\McGooglesitemap\Utility
 * @version 8.7.13
 */
class Main
{
    /**
     * @param array $config
     * @return array
     */
    public function populateItems(array &$config)
    {
        $show = array("tt", "tx");
        $res = DatabaseUtility::query('SHOW TABLES');
        while ($row = mysqli_fetch_array($res)) {
            $tmp = explode("_", $row[0]);
            if (in_array($tmp[0], $show) && $tmp[count($tmp) - 1] != "mm") {
                $content[] = Array($row[0], $row[0]);
            }
        }
        $config["items"] = $content;
        return $config;
    }
}
