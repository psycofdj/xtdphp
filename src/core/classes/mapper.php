<?php

require_once(dirname(__FILE__) . "/../../local.php");

class MapperParams
{
  public function __construct($p_id, $p_start, $p_length, $p_search, $p_isRegex, $p_colInfo)
  {
    $this->m_id         = $p_id;
    $this->m_start      = $p_start;
    $this->m_length     = $p_length;
    $this->m_search     = $p_search;
    $this->m_isRegex    = $p_isRegex;
    $this->m_colInfo    = $p_colInfo;
    $this->m_cols       = array();
    $this->m_sort       = array();
  }

  public function addColumns($p_idx, $p_prop, $p_search, $p_isRegexp, $p_isSearchable, $p_isSortable)
  {
    $l_col = new stdClass;
    $l_col->m_idx          = $p_idx;
    $l_col->m_prop         = $p_prop;
    $l_col->m_search       = $p_search;
    $l_col->m_isRegexp     = $p_isRegexp;
    $l_col->m_isSearchable = $p_isSearchable;
    $l_col->m_isSortable   = $p_isSortable;
    array_push($this->m_cols, $l_col);
  }

  public function addSort($p_idx, $p_colIdx, $p_dir)
  {
    $l_sort = new stdClass;
    $l_sort->m_idx    = $p_idx;
    $l_sort->m_colIdx = $p_colIdx;
    $l_sort->m_dir    = $p_dir;
    array_push($this->m_sort, $l_sort);
  }
}



class RawMapper
{
  public function __construct($p_params, $p_columns, $p_baseQuery)
  {
    $this->m_params    = $p_params;
    $this->m_columns   = $p_columns;
    $this->m_baseQuery = $p_baseQuery;
  }

  protected function getColName($p_idx)
  {
    return $this->m_columns[$p_idx];
  }

  protected function getConstriaints()
  {
    $l_conds = array();
    $l_vars  = array();
    foreach ($this->m_params->m_cols as $c_col)
    {
      $l_colName = $this->getColName($c_col->m_idx);
      if (($c_col->m_isSearchable) && ($c_col->m_search != ""))
      {
        if ("__null__" == $c_col->m_search)
        {
          $l_cond = sprintf("%s IS NULL", $l_colName);
        }
        else if ("__notnull__" == $c_col->m_search)
        {
          $l_cond = sprintf("%s IS NOT NULL", $l_colName);
        }
        else if (false == $c_col->m_isRegexp)
        {
          $l_cond = sprintf("%s LIKE ?", $l_colName);
          array_push($l_vars,  "%" . $c_col->m_search . "%");
        }
        else
        {
          $l_cond = sprintf("%s REGEXP ?", $l_colName);
          array_push($l_vars,  $c_col->m_search);
        }
        array_push($l_conds, $l_cond);
      }
    }

    if ("" != $this->m_params->m_search)
    {
      $l_global = array();
      foreach ($this->m_params->m_cols as $c_col)
      {
        $l_colName = $this->getColName($c_col->m_idx);
        if (true == $this->m_params->m_isRegex)
        {
          $l_cond = sprintf("%s REGEXP ?", $l_colName);
          array_push($l_vars,  $this->m_params->m_search);
        }
        else
        {
          $l_cond = sprintf("%s LIKE ?", $l_colName);
          array_push($l_vars, "%" . $this->m_params->m_search . "%");
        }
        array_push($l_global, $l_cond);
      }
      array_push($l_conds, sprintf("(%s)", implode(" OR ", $l_global)));
    }

    return array($l_conds, $l_vars);
  }

  protected function getOrders()
  {
    $l_orders = array();
    $l_vars   = array();
    foreach ($this->m_params->m_sort as $c_sort)
    {
      $l_colName = $this->getColName($c_sort->m_colIdx);
      $l_order   = sprintf("%s %s", $l_colName, $c_sort->m_dir);
      array_push($l_orders, $l_order);
    }
    if (count($l_orders))
      $l_result = sprintf("ORDER BY %s", implode(",", $l_orders));
    else
      $l_result = "";
    return array($l_result, $l_vars);
  }

  protected function getLimit()
  {
    $l_vars = array();
    array_push($l_vars, $this->m_params->m_start);
    array_push($l_vars, $this->m_params->m_length);
    return array("LIMIT ?, ?", $l_vars);
  }

  protected function getTotal()
  {
    $l_query = sprintf("SELECT count(*) as count FROM (%s) as tmp", $this->m_baseQuery);
    $l_data  = R::getRow($l_query, $this->m_columns);
    return $l_data["count"];
  }

  protected function getFilteredTotal()
  {
    list($l_conds, $l_vars) = $this->getConstriaints();

    $l_vars  = array_merge($this->m_columns, $l_vars);
    $l_query = sprintf("SELECT count(*) as count FROM (%s) as tmp %s %s",
                       $this->m_baseQuery,
                       count($l_conds) ? "WHERE" : "",
                       implode(" AND ", $l_conds));

    $l_data = R::getRow($l_query, $l_vars);
    return $l_data["count"];
  }

  private function getUniqueData($p_colName)
  {
    $l_query = sprintf("SELECT DISTINCT %s FROM (%s) as tmp ORDER BY %s ASC", $p_colName, $this->m_baseQuery, $p_colName);
    return R::getAll($l_query, $this->m_columns);
  }

  public function process()
  {
    if (0 == count($this->m_params->m_colInfo))
      return $this->processData();
    return $this->processColInfo();
  }

  public function processColInfo()
  {
    $l_data = array();
    foreach ($this->m_params->m_colInfo as $c_colIdx)
    {
      $l_key          = "0" + $c_colIdx;
      $l_colName      = $this->m_columns[$c_colIdx];
      $l_data[$l_key] = array();
      $l_values       = $this->getUniqueData($l_colName);
      foreach ($l_values as $c_value)
        array_push($l_data[$l_key], $c_value[$l_colName]);
    }
    return $l_data;
  }

  public function processData()
  {
    $l_vars = $this->m_columns;
    list($l_conds, $l_cvars) = $this->getConstriaints();
    $l_vars = array_merge($l_vars, $l_cvars);
    list($l_orders, $l_cvars) = $this->getOrders();
    $l_vars = array_merge($l_vars, $l_cvars);
    list($l_limit, $l_cvars) = $this->getLimit();
    $l_vars = array_merge($l_vars, $l_cvars);

    $l_body = sprintf("%s %s %s %s",
                      count($l_conds) ? "WHERE" : "",
                      implode(" AND ", $l_conds),
                      $l_orders,
                      $l_limit);


    $l_query = sprintf("SELECT * FROM (%s) as tmp %s;", $this->m_baseQuery, $l_body);
    $l_data    = R::getAll($l_query, $l_vars);
    $l_results = array();
    foreach ($l_data as $c_data)
    {
      $l_result  = array();
      for ($c_idx = 0; $c_idx < count($this->m_columns); $c_idx++)
      {
        $l_colName = $this->m_columns[$c_idx];
        array_push($l_result, $c_data[$l_colName]);
      }
      array_push($l_results, $l_result);
    }

    return array("sEcho"                => $this->m_params->m_id,
                 "aaData"               => $l_results,
                 "iTotalRecords"        => $this->getTotal(),
                 "iTotalDisplayRecords" => $this->getFilteredTotal());
  }

}

?>