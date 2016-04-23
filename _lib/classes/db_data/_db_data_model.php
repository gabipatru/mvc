<?php

/*
 * This class is used to implement basic operations on a database table
 */

require_once(CLASSES_DIR.'/db.php');

abstract class dbDataModel {
	private $tableName;
	private $idField;
	private $statusField;
	
	function __construct($table, $id, $status) {
		$this->tableName = $table;
		$this->idField = $id;
		$this->statusField = $status;
	}
	
	/*
	 * The abstract functions: re-declare all of these when you extend
	 */
	abstract function onAdd($insertId);
	abstract function onEdit($iId, $res);
	abstract function onBeforeDelete($iId);
	abstract function onDelete($iId);
	abstract function onSetStatus($iId);
	
	/*
	 * Some get functions for class data
	 */
	public function getTableName() {
		return $this->tableName;
	}
	
	public function getIdField() {
		return $this->idField;
	}
	
	public function getStatusField() {
		return $this->statusField;
	}
	
	/*
	 * Add function - adds data to the database
	 */
	public function Add($aData) {
		if (!is_array($aData)) {
			return false;
		}
		
		// compose the SQL strings
		$sFields = '';
		$sValues = '';
		$aParams = array();
		$aMarkers = array();
		
		$aFields = array_keys($aData);
		$sFields = implode(',', $aFields);
		foreach ($aData as $field => $value) {
			$aParams[] = $value;
			$aMarkers[] = '?';
		}
		$sValues = implode(',', $aMarkers);
		
		// execute the query
		$sql = "INSERT INTO ".$this->getTableName()
				." (".$sFields.")"
				." VALUES (".$sValues.")";
		$res = db::query($sql, $aParams);
		if ($res->errorCode() != '00000') {
			return false;
		}
		$iLastId = db::lastInsertId();

		// call abstract function onAdd
		return $this->onAdd($iLastId);
	}
	
	/*
	 * EDIT function - performs updates in the database
	 */
	public function Edit($iId, $aData) {
		if (!is_array($aData)) {
			return false;
		}
		if (!$iId) {
			return false;
		}
		
		// compose the SQL
		$sFields = '';
		$aFields = array();
		$aParams = array();
		foreach ($aData as $field => $value) {
			$aParams[] = $value;
			$aFields[] = $field.' = ?';
		}
		$sFields = implode(',', $aFields);
		$aParams[] = $iId;
		
		// execute the query
		$sql = "UPDATE ".$this->getTableName()." SET "
				.$sFields
				.' WHERE '.$this->getIdField()." = ?";
		$res = db::query($sql, $aParams);
		if ($res->errorCode() != '00000') {
			return false;
		}
		
		return $this->onEdit($iId, $res);
	}
	
	/*
	 * DELETE function - deletes from the database
	 */
	public function Delete($iId) {
		if (!$iId) {
			return false;
		}
		
		// call pre-delete function
		$r = $this->onBeforeDelete($iId);
		if (!$r) {
			return false;
		}
		
		// delete the item
		$sql = "DELETE FROM ".$this->getTableName()
				." WHERE ".$this->getIdField()." = ?";
		$res = db::query($sql, array($iId));
		if ($res->errorCode() != '00000') {
			return false;
		}
		
		// call post delete function
		$r = $this->onDelete($iId);
		
		if ($r) {
			return db::rowCount($res);
		}
		return false;
	}
	
	/*
	 * STATUS functions - checge the status to onlin / offline, etc
	 */
	public function setStatus($iId, $mNewStatus) {
		if (!$iId) {
			return false;
		}
		
		// run the query
		$sql = "UPDATE ".$this->getTableName()." SET "
				.$this->getStatusField()." = ? "
				." WHERE ".$this->getIdField()." = ?";
		$res = db::query($sql, array($mNewStatus, $iId));
		if ($res->errorCode() != '00000') {
			return false;
		}
		
		$r = $this->onSetStatus($iId, $mNewStatus);
		if (!$r) {
			return false;
		}
		return true;
	}
	
	/*
	 * Count function - count the rows that match a given criteria
	 */
	public function Count($filters = array(), $options = array()) {
		if (!is_array($filters)) {
			return false;
		}
		if (!is_array($options)) {
			return false;
		}
		
		list($whereCondition, $aParams) = db::filters($filters);
		
		$sql = "SELECT COUNT(*) AS cnt FROM ".$this->getTableName()
				." WHERE ".$whereCondition;
		$row = db::querySelect($sql, $aParams);
		if (isset($row['cnt'])) {
			return $row['cnt'];
		}
		return false;
	}
	
	/*
	 * Get function - fetch data from database
	 */
	public function Get($filters = array(), $options = array()) {
		if (!is_array($filters)) {
			return false;
		}
		if (!is_array($options)) {
			return false;
		}
		
		$iNrItems = $this->Count($filters, $options);
		
		list($whereCondition, $aParams) = db::filters($filters);
		
		// ordering
		$sOrder = '';
		if (!empty ($options['order_field']) && !empty($options['order_type'])) {
			$sOrder = " ORDER BY ".$options['order_field']." ".$options['order_type'];
		}
		
		// paging
		$sLimit = '';
		if (!empty($options['per_page']) && !empty($options['page'])) {
			$offset = ($options['page'] - 1) * $options['per_page'];
			$limit = $options['per_page'];
			$sLimit = " LIMIT ".$offset.', '.$limit;
		}
		
		// select the data
		$sql = "SELECT * FROM ".$this->getTableName()
				." WHERE ".$whereCondition
				.$sOrder
				.$sLimit;
		$res = db::query($sql, $aParams);
		if (!$res || $res->errorCode() != '00000') {
			return false;
		}
		
		// compute the max page
		$iMaxPage = 0;
		if ($iNrItems > 0 && !empty($options['per_page']) && !empty($options['page'])) {
			$iMaxPage = floor($iNrItems / $options['per_page']);
			if ($iNrItems % $options['per_page'] != 0) {
				$iMaxPage++;
			}
		}
		
		$aData = array();
		while ($row = db::fetchAssoc($res)) {
			$aData[$row[$this->getIdField()]] = $row;
		}
		
		return array($aData, $iNrItems, $iMaxPage);
	}
	
	/*
	 * Some wrappers for Get
	 */
	public function simpleGet($filters = array(), $options = array()) {
		list($aData, $iNr, $iMaxPage) = $this->Get($filters, $options);
		return $aData;
	}
	public function singleGet($filters = array(), $options = array()) {
		list($aData, $iNr, $iMaxPage) = $this->Get($filters, $options);
		return current($aData);
	}
    
}