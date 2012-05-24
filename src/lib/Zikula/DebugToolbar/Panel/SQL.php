<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_DebugToolbar
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This panel displays all executed SQL queries.
 */
class Zikula_DebugToolbar_Panel_SQL implements Zikula_DebugToolbar_PanelInterface
{
    /**
     * SQL queries.
     *
     * @var array
     */
    private $_queries = array();

    /**
     * Returns the id of this panel.
     *
     * @return string
     */
    public function getId()
    {
        return "sql";
    }

    /**
     * Returns the link name.
     *
     * @return string
     */
    public function getTitle()
    {
        $title =  __('Queries');
        $count = $this->getSQLCount();

        if ($count > 0) {
            $title .= " ({$count})";
        }

        return $title;
    }

    /**
     * Returns the content panel title.
     *
     * @return string
     */
    public function getPanelTitle()
    {
        return __('Queries');
    }

    /**
     * Returns the the HTML code of the content panel.
     *
     * @return string HTML
     */
    public function getPanelContent()
    {
        $rows = array();
        $sum_time = 0;
        $sum_rows_aff = 0;
        $sum_rows_marsh = 0;

        foreach ($this->_queries as $query) {
            $sum_time += $query['time'];
            $rows[] = '<tr>
                           <td>'.$this->formatSql($query['query']).'</td>
                           <td>'.round($query['time']*1000, 3).'</td>
                       </tr>';
        }

        return '<table class="DebugToolbarTable">
                    <tr>
                        <th>'.__('Query').'</th>
                        <th>'.__('Time (ms)').'</th>
                    </tr>
                    '.implode(' ', $rows).'
                    <tr>
                        <td>&nbsp;</td>
                        <td><strong>'.round($sum_time*1000, 3).'</strong></td>
                    </tr>
                </table>';
    }

    /**
     * Returns the number of executed SQL queries.
     *
     * @return integer
     */
    protected function getSQLCount()
    {
        return count($this->_queries);
    }

    /**
     * Format a SQL with some colors on SQL keywords to make it more readable.
     *
     * @param string $sql SQL query.
     *
     * @return string Formated SQL query
     */
    protected function formatSql($sql)
    {
        $sql = wordwrap($sql, 150 , ' <br/> ', true);

        $color = "#990099";
        $newSql = str_replace("\t" , "" ,$sql);
        $newSql = str_replace("\n" , "" ,$newSql);
        $newSql = str_replace("SELECT ", "<span style=\"color: $color;\"><b>SELECT </b></span>  ",$newSql);
        $newSql = str_replace("UPDATE ", "<span style=\"color: $color;\"><b>UPDATE </b></span>  ",$newSql);
        $newSql = str_replace("INSERT ", "<span style=\"color: $color;\"><b>INSERT </b></span>  ",$newSql);
        $newSql = str_replace("DELETE ", "<span style=\"color: $color;\"><b>DELETE </b></span>  ",$newSql);
        $newSql = str_replace("SET ", "<span style=\"color: $color;\"><b>SET </b></span>  ",$newSql);
        $newSql = str_replace("FROM ", "<span style=\"color: $color;\"><b>FROM </b></span>",$newSql);
        $newSql = str_replace(" LEFT JOIN ", "<span style=\"color: $color;\"><b> LEFT JOIN </b></span>",$newSql);
        $newSql = str_replace(" INNER JOIN ", "<span style=\"color: $color;\"><b> INNER JOIN </b></span>",$newSql);
        $newSql = str_replace(" WHERE ", "<span style=\"color: $color;\"><b> WHERE </b></span>",$newSql);
        $newSql = str_replace(" GROUP BY ", "<span style=\"color: $color;\"><b> GROUP BY </b></span>",$newSql);
        $newSql = str_replace(" HAVING ", "<span style=\"color: $color;\"><b> HAVING </b></span>",$newSql);
        $newSql = str_replace(" AS ", "<span style=\"color: $color;\"><b> AS </b></span>  ",$newSql);
        $newSql = str_replace(" IN ", "<span style=\"color: $color;\"><b> IN </b></span>  ",$newSql);
        $newSql = str_replace(" ON ", "<span style=\"color: $color;\"><b> ON </b></span>",$newSql);
        $newSql = str_replace(" ORDER BY ", "<span style=\"color: $color;\"><b> ORDER BY </b></span>",$newSql);
        $newSql = str_replace(" LIMIT ", "<span style=\"color: $color;\"><b> LIMIT </b></span>",$newSql);
        $newSql = str_replace(" OFFSET ", "<span style=\"color: $color;\"><b> OFFSET </b></span>",$newSql);
        $newSql = str_replace(" AND ", "<span style=\"color: $color;\"><b> AND </b></span>",$newSql);
        $newSql = str_replace(" OR ", "<span style=\"color: $color;\"><b> OR </b></span>",$newSql);

        return $newSql;
    }

    /**
     * Event listener for log.sql.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function logSql(Zikula_Event $event)
    {
        $this->_queries[] = $event->getArgs();
    }

    /**
     * Returns the panel data in raw format.
     *
     * @return array
     */
    public function getPanelData()
    {
        return $this->_queries;
    }
}
