<?php
namespace Pits\McGooglesitemap\Controller;

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
 * Class ContentController
 * @package Pits\McGooglesitemap\Controller
 * @version 8.7.13
 */
class ContentController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var \Pits\McGooglesitemap\Domain\Repository\ContentRepository
     * @inject
     */
    protected $contentRepository;

    /**
     * $dateFormat
     * @var string
     */
    protected $dateFormat = null;

    /**
     * baseUrl of website
     * @var string
     */
    protected $baseUrl = null;

    /**
     * common function called in Page / Content / Extension
     * @param int $type
     * @return void
     */
    public function common($type = 0)
    {
        ini_Set("max_execution_time", 120);
        $mySetting = $this->settings;
        $GLOBALS["TSFE"]->set_no_cache();
        $this->act = array(
            "1" => "Always",
            "2" => "Hourly",
            "3" => "Daily",
            "4" => "Weekly",
            "5" => "Monthly",
            "6" => "Yearly",
            "7" => "Never"
        );

        // Define Content Type XML for Site-Map
        // Also started the document version and encoding section
        header('Content-type: text/xml');
        $head[] = '<?xml version="1.0" encoding="UTF-8"?>';
        if ($type == 1) {
            $head[] = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        } else {
            $head[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        }
        ob_clean();
        echo implode("\n", $head);

        // Clearing the Array $head
        unset($head);
        $tmp = explode("/", $GLOBALS['_SERVER']['PHP_SELF']);
        unset($tmp[0]);
        unset($tmp[count($tmp)]);
        $path = implode("/", $tmp);
        if (strlen($path) != 0) {
            $path .= "/";
        }

        // Setting Host BaseUrl Parameter
        $baseUrl= preg_replace('{/$}', '', $this->request->getBaseUri());
        $this->baseUrl = $baseUrl;
        if ($mySetting['lastmodif'] == 1) {
            $this->dateFormat = 'Y-m-d\TH:i:s\Z';
        } else {
            $this->dateFormat = "Y-m-d";
        }
    }

    /**
     * contentAction
     *
     * @return void
     */
    public function contentAction()
    {
        $this->common();
        $this->sitemapContent();
        echo "</urlset>\n";
        exit();
    }

    /**
     * pagesAction
     *
     * @return void
     */
    public function pagesAction()
    {
        $this->common();
        $this->sitemapPage();
        echo "</urlset>\n";
        exit();
    }

    /**
     * indexAction
     *
     * @return void
     */
    public function indexAction()
    {
        $this->common(1);
        $this->sitemapIndex();
        echo "</urlset>\n";
        exit();
    }

    /**
     * preview Object
     * Only for debugging purpose
     *
     * @param string $obj
     */
    public function pre($obj = "")
    {
        echo '<pre>';
        print_r($obj);
        echo '</pre>';
    }

    /**
     * sitemapContent
     *
     * @param array $array
     */
    public function sitemapContent(array $array = array())
    {
        $cObjData = $this->configurationManager->getContentObject();
        $fix = array();
        $tema = array();
        if (count($array) == 0) {
            $array = $this->configurationManager->getContentObject()->data;
        }
        $mySetting = $this->settings;

        if ($mySetting['tableslive'] == "tt_news") {
            return $this->sitemapTTNews($array);
        }

        if ($mySetting['changfreq'] != 0) {
            $fix['changefreq'] = strtolower($this->act[$mySetting['changfreq']]);
        }

        if ($mySetting['priority'] <= 1 && $mySetting['priority'] > 0) {
            $fix['priority'] = $mySetting['priority'];
            if (strlen($fix['priority']) == 1) {
                $fix['priority'] .= ".0";
            }
        }

        if ($mySetting['pageslive']!='') {
            if($mySetting['tableslive'] == 'tx_tntbabygallery_babies') {
               $babycondition = "AND FROM_UNIXTIME(birthdate) > (NOW() - INTERVAL 6 MONTH)";
            }
            $sql = "SELECT * FROM " . $mySetting['tableslive'] . " WHERE pid IN (" . $mySetting['pageslive'] . ") " . $babycondition . $cObjData->enableFields($mySetting['tableslive']);
            $res = $this->contentRepository->customQuery($sql);
        }

        if (isset($res) && ($res->num_rows > 0)) {
            while ($row = mysqli_fetch_array($res)) {
                $tema['lastmod'] = gmdate($this->dateFormat, $row['tstamp']);
                $tema['page'] = $this->elcHash($mySetting['pagescontent'], $this->replaceParams($row, $mySetting['url']));
                if ($GLOBALS['TYPO3_CONF_VARS']['EXT']['mc_googlesitemap'] == 1) {
                    $tema['page'] = $this->changeTitle($tema['page'], $row['title']);
                }
                $tema['loc'] = htmlspecialchars(utf8_encode($this->baseUrl . $tema['page']));
                $tema = array_merge($fix, $tema);
                $this->createElement($tema);
                unset($tema);
            }
        }
    }

    /**
     * sitemapTTNews render sitemap Tree for News
     *
     * @param array $array
     */
    public function sitemapTTNews(array $array = array())
    {
        $fix = array();
        $tema = array();
        $cObjData = $this->configurationManager->getContentObject();
        if (count($array) == 0) {
            $array = $this->configurationManager->getContentObject()->data;
        }
        $mySetting = $this->settings;
        if (count($GLOBALS['TYPO3_LOADED_EXT']['tt_news']) == 0) {
            return;
        }

        if ($mySetting['changfreq'] != 0) {
            $fix['changefreq'] = strtolower($this->act[$mySetting['changfreq']]);
        }
        if ($mySetting['priority'] <= 1 && $mySetting['priority'] > 0) {
            $fix['priority'] = $mySetting['priority'];
            if (strlen($fix['priority']) == 1) {
                $fix['priority'] .= ".0";
            }
        }

        $pages = explode(',', $mySetting['pageslive'] ? $mySetting['pageslive'] : $GLOBALS['TSFE']->id);
        foreach ($pages as $page) {
            $sql = 'SELECT tt_news.uid,tt_news.tstamp, tt_news_cat.single_pid FROM tt_news LEFT OUTER  JOIN tt_news_cat_mm ON tt_news_cat_mm.uid_local = tt_news.uid LEFT OUTER JOIN tt_news_cat ON tt_news_cat_mm.uid_foreign = tt_news_cat.uid  WHERE tt_news.pid = ' . $page . $cObjData->enableFields("tt_news");
            $res = $this->contentRepository->customQuery($sql);
            if (isset($res) && ($res->num_rows > 0)) {
                while ($row = mysqli_fetch_array($res)) {
                    if ($row['single_pid'] == 0) {
                        $row['single_pid'] = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tt_news.']['singlePid'];
                    }
                    $tema = array_merge($fix, $tema);
                    $tema['lastmod'] = gmdate($this->dateFormat, $row['tstamp']);
                    $tema['page'] = $this->elcHash($row['single_pid'], array("tx_ttnews[tt_news]" => $row['uid']));
                    
                    $tema['loc'] = htmlspecialchars(utf8_encode($this->baseUrl . $tema['page']));

                    if (((int) $row['single_pid'] * 1) == 0) {
                        $row['single_pid'] = $mySetting['pagescontent'];
                    }
                    if (((int) $row['single_pid'] * 1) != 0) {
                        $this->createElement($tema);
                    }
                    unset($tema);
                }
            }
            print_R($i);
        }
    }

    /**
     * sitemapPage
     *
     * @param array $array
     */
    public function sitemapPage($array = array())
    {
        $cObjData = $this->configurationManager->getContentObject();
        $fix = array();
        $tema = array();
        if (count($array) == 0) {
            $array = $this->configurationManager->getContentObject()->data;
        }
        $mySetting = $this->settings;
        $anormal = array();
        $tree = "";
        $pages = explode(',', $mySetting['pageslive_pages'] ? $mySetting['pageslive_pages'] : $GLOBALS['TSFE']->id);
        foreach ($pages as $page) {
            $tree .= $cObjData->getTreeList($page, 1000);
        }

        $tree = substr($tree, 0, strlen($tree) - 1);
        $sql = "SELECT uid,pid,doktype FROM pages WHERE uid IN (" . $tree . ") " . $cObjData->enableFields("pages");
        $res = $this->contentRepository->customQuery($sql);

        if (isset($res) && ($res->num_rows > 0)) {
            while ($row = mysqli_fetch_array($res)) {
                $pids[$row['uid']] = $row['pid'];
                $prios[$row['uid']] = $mySetting['priority_pages'];
                $freqs[$row['uid']] = $mySetting['changfreq_pages'];

                if ((!in_array($row['uid'], $pages) && ( $row['doktype'] == 254 || $row['doktype'] == 199 )) || @in_array($row['pid'], $anormal)) {
                    $anormal[] = $row['uid'];
                }
            }
        }

        $tree = implode(",", array_merge($pages, array_diff(explode(",", $tree), $anormal)));
        if (count($anormal) != 0) {
            $anormal = implode(",", $anormal);
            $anormalSql = " pid NOT IN (" . $anormal . ") AND ";
        }

        $sel = "SELECT * FROM pages WHERE doktype IN(1,2) AND " . $anormalSql . " uid IN (" . $tree . ") AND nav_hide=0 " . $cObjData->enableFields("pages");
        $res = $this->contentRepository->customQuery($sel);

        if (isset($res) && ($res->num_rows > 0)) {
            while ($row = mysqli_fetch_array($res)) {
                $uid = $row['uid'];
                $freq = $row['tx_mcgooglesitemap_changefreq'];
                while (($freq == 0) && array_key_exists($uid, $pids)) {
                    $uid = $pids[$uid];
                    $freq = $freqs[$uid];
                }
                $uid = $row['uid'];
                $prio = $mySetting['priority_pages'];
                while (($prio == 0) && array_key_exists($uid, $pids)) {
                    $uid = $pids[$uid];
                    $prio = $prios[$uid];
                }
                if ($freq != 0) {
                    $tema['changefreq'] = strtolower($this->act[$freq]);
                }
                if ($prio <= 1 && $prio > 0) {
                    $tema['priority'] = $prio;
                    if (strlen($tema['priority']) == 1) {
                        $tema['priority'] .= ".0";
                    }
                }
                $time = ($row['SYS_LASTCHANGED'] > $row['tstamp']) ? $row['SYS_LASTCHANGED'] : $row['tstamp'];
                $tema['lastmod'] = gmdate($this->dateFormat, $time);
                $tema['page'] = $this->elcHash($row['uid'], array(), 0);
                if (@strpos('http://', $tema['page']) === false) {
                    $tema['loc'] = htmlspecialchars(utf8_encode($this->baseUrl . $tema['page']));
                } else {
                    $tema['loc'] = htmlspecialchars(utf8_encode($tema['page']));
                }
                $this->createElement($tema);
                unset($tema);
            }
        }
    }

    /**
     * sitemapIndex
     *
     * @param array $array
     */
    public function sitemapIndex($array = array())
    {
        $cObjData = $this->configurationManager->getContentObject();
        $mySetting = $this->settings;
        if ($cObjData->data['pages']) {
            $pages = explode(',', $cObjData->data['pages'] ? $cObjData->data['pages'] : $GLOBALS['TSFE']->id);
            foreach ($pages as $page) {
                $tree .= $cObjData->getTreeList($page, 1000);
            }
            $tree = substr($tree, 0, strlen($tree) - 1);
            $getTree = " AND pages.uid IN (" . $tree . ") ";
        } else {
            $getTree = "";
        }

        $this->dateFormat = 'Y-m-d\TH:i:s\Z';
        $sql = "SELECT tt_content.* FROM tt_content INNER JOIN pages ON pages.uid=tt_content.pid " . $getTree . $cObjData->enableFields("pages") . " " . $cObjData->enableFields("tt_content");
        $res = $this->contentRepository->customQuery($sql);

        if (isset($res) && ($res->num_rows > 0)) {
            while ($row = mysqli_fetch_array($res)) {
                $url = $cObjData->typolink("", array("no_cache" => 0, "returnLast" => "url", "parameter" => $row['pid'], "useCacheHash" => 0));
                if ($row['list_type'] == "pitsgooglesitemap_contents") {
                    $sql = "SELECT tstamp FROM " . $mySetting['tableslive'] . " WHERE pid IN (" . $mySetting['pageslive_index'] . ") " . $cObjData->enableFields($mySetting['tableslive_index']) . " ORDER BY tstamp DESC LIMIT 1";
                    $last = $this->contentRepository->customQuery($sql);
                } else {
                    $sql = "SELECT tstamp FROM pages  WHERE pid IN (" . $mySetting['pageslive_index'] . ") " . $cObjData->enableFields("pages") . " ORDER BY tstamp DESC LIMIT 1";
                    $last = $this->contentRepository->customQuery($sql);
                }

                if (isset($last) && ($last->num_rows > 0)) {
                    $last = mysqli_fetch_array($last);
                    $linea[] = "\t<sitemap>";
                    $linea[] = "\t\t<loc>" . $this->baseUrl . $url . "</loc>";
                    $linea[] = "\t\t<lastmod>" . gmdate('Y-m-d\TH:i:s\Z', $last[0]) . "</lastmod>";
                    $linea[] = "\t</sitemap>";
                    echo implodE("\n", $linea);
                    unset($linea);
                }
            }
        }
        echo "</sitemapindex>\n";
        exit();
    }

    /**
     * elcHash
     *
     * @param $page
     * @param $array
     * @param int $cHash
     * @return mixed
     */
    public function elcHash($page, $array, $cHash = 1)
    {
        $i = 0;
        $max = count($array);
        $keys = array_keys($array);
        while ($i < $max) {
            if (strlen($array[$keys[$i]]) != 0) {
                $salida .= "&" . $keys[$i] . "=" . $array[$keys[$i]];
            }
            $i++;
        }
        $typoLinkConf = array(
            "no_cache" => 0,
            "returnLast" => "url",
            "parameter" => $page,
            "additionalParams" => $salida,
            "useCacheHash" => $cHash
        );
        return $this->configurationManager->getContentObject()->typolink("", $typoLinkConf);
    }

    /**
     * replaceParams
     *
     * @param $fields
     * @param $params
     * @return mixed
     */
    public function replaceParams($fields, $params)
    {
        $params = str_replace("?", "", $params);

        $i = 0;
        $max = count($fields);
        $keys = array_keys($fields);
        while ($i < $max) {
            $params = str_replace("###" . $keys[$i] . "###", $fields[$keys[$i]], $params);
            $i++;
        }
        $i = 0;
        $ele = explode("&", $params);
        $max = count($ele);
        while ($i < $max) {
            $tmp = explodE("=", $ele[$i]);
            $salida[$tmp[0]] = $tmp[1];
            $i++;
        }
        return $salida;
    }

    /**
     * createElement
     *
     * @param $array
     */
    public function createElement($array)
    {

        $linea[] = "\t<url>";
        $linea[] = "\t\t<loc>" . $array['loc'] . "</loc>";
        $linea[] = "\t\t<lastmod>" . $array['lastmod'] . "</lastmod>";
        if (strlen($array['changefreq']) != 0) {
            $linea[] = "\t\t<changefreq>" . $array['changefreq'] . "</changefreq>";
        }
        if (strlen($array['priority']) != 0) {
            $linea[] = "\t\t<priority>" . $array['priority'] . "</priority>";
        }
        $linea[] = "\t</url>\n";
        echo implode("\n", $linea);
        unset($linea);
    }

    /**
     * changeTitle
     *
     * @param $url
     * @param $str
     * @return string
     */
    public function changeTitle($url, $str)
    {
        $str = str_replace(chr(225), "a", $str);
        $str = str_replace(chr(233), "e", $str);
        $str = str_replace(chr(237), "i", $str);
        $str = str_replace(chr(243), "o", $str);
        $str = str_replace(chr(250), "u", $str);

        $str = str_replace(chr(193), "A", $str);
        $str = str_replace(chr(201), "E", $str);
        $str = str_replace(chr(205), "I", $str);
        $str = str_replace(chr(211), "O", $str);
        $str = str_replace(chr(218), "U", $str);

        $str = str_replace(chr(241), "n", $str);
        $str = str_replace(chr(209), "N", $str);

        $str = str_replace("+", "", $str);
        $str = str_replace("%", "", $str);
        $str = str_replace("&", "", $str);
        $str = str_replace("(", "", $str);
        $str = str_replace(")", "", $str);
        $str = str_replace("$", "", $str);
        $str = str_replace("@", "", $str);
        $str = str_replace("#", "", $str);
        $str = str_replace("!", "", $str);
        $str = str_replace("", "", $str);
        $str = str_replace("?", "", $str);
        $str = str_replace("", "", $str);
        $str = str_replace(":", "", $str);
        $str = str_replace('.', "", $str);
        $str = str_replace("'", "", $str);
        $str = str_replace("'", "", $str);
        $str = str_replace("*", "", $str);
        $str = str_replace(';', "", $str);
        $str = str_replace(',', "", $str);


        $str = str_replace(" ", "_", $str);
        $str = str_replace("\n", "", $str);
        $str = str_replace("\r", "", $str);
        $str = str_replace("\t", "", $str);
        $tmp = explode('.', $url);
        $tmp[0] = $str;
        return implode('.', $tmp);
    }
}
