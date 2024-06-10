<?php
defined('ABSPATH') || exit;

//NOTE: THIS NEED CUSTOMIZATION IN LINE THAT MARKED BY *

date_default_timezone_set('Asia/Tehran');

function db(...$args)
{
  static $db;
  if (!isset($db) || count($args) > 0) {
    // $db = new PDO('mysql:dbname=plus;charset=utf8', 'root', ''); // *
    $db = new Sql_DB(...$args);
  }
  return $db;
}

//! Only for strings search
function searchText_Condition($searchInput, ...$cols)
{
  $where = '1 = 1'; //? Conditions to find
  $qm = 0; //? question Mark Count
  $sval = ''; //? What to Like

  if ($searchInput && $searchInput != '') {
    $sval = '%' . $searchInput . '%';
    $where .= ' AND ( 0 = 1 ';
    foreach ($cols as $col) {
      $where .= " OR $col LIKE ? ";
      $qm++;
    }
    $where .= ')';
  }

  return [$where, array_fill(0, $qm, $sval)];
}

function exec_q($q, $p, $fetch_b = false)
{
  $query = db()->prepare($q);

  $ex = $query->execute($p);

  return $fetch_b ? $query : $ex;
}


function insert_q($tbl, $keys, $vals, $params)
{
  return exec_q(
    "INSERT INTO $tbl ($keys) VALUES ( $vals )",
    $params
  );
}

// SELECT $cols FROM $tbl INNER JOIN $join_tbl ON $join_query WHERE $condition GROUP BY $groupby HAVING $having ORDER BY $order LIMIT $lim
function select_q($tbl, $cols, $join_tbl = null, $join_query = null, $condition = null, $groupby = null, $having = null, $order = null, $lim = null, $p = [])
{
  $join = $join_tbl && $join_query ? "INNER JOIN" . $join_tbl . " ON $join_query" : '';
  $cond = $condition ? "WHERE $condition" : '';
  $gb = $groupby ? "GROUP BY $groupby" : '';
  $having = $having ? "HAVING $having" : '';
  $ob = $order ? "ORDER BY $order" : '';
  $lm = $lim ? "LIMIT $lim" : '';

  $query = "SELECT $cols FROM $tbl $join $cond $gb $having $ob $lm";

  return exec_q(
    $query,
    $p,
    true
  );
}

function delete_q($tbl, $condition, $params = [])
{
  return exec_q("DELETE FROM $tbl WHERE $condition", $params);
}

function update_q($tbl, $condition, $set, $params = [])
{
  return exec_q("UPDATE $tbl SET $set WHERE $condition", $params);
}

// Pagination
function PaginationQuery($per_page, $page, $fetchMode, ...$SEL_PARAMS)
{
  $SEL = $SEL_PARAMS;

  $SEL['cols'] = 'COUNT(*)';

  $count = select_q(...$SEL)->fetchColumn(0);

  // Pagination Main
  $page = intval($page);

  $pages = ceil($count / $per_page);

  if ($page < 1) {
    $page = 1;
  }

  if ($page > $pages) {
    $page = $pages;
  }

  $off = ($page - 1) * $per_page;

  $SEL_PARAMS['lim'] = "$per_page OFFSET $off";

  $mn = select_q(...$SEL_PARAMS);

  return ['page_count' => $pages, 'res' => $mn->fetchAll($fetchMode), 'current' => $page, 'count' => $count, 'offset' => $off];
}

include_once ('oop.sql.php');
include_once 'MVC/@model.php';