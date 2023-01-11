<?php

class Paginator {

	private $items;
	private $total_items = 0;
	private $items_per_page = 10;
	private $current_page = 1;
	private $total_pages = null;

	function __construct($total_items = null, $items_per_page = null, $current_page = null) {
		if(!is_null($total_items)) $this->setTotalItems($total_items);
		if(!is_null($items_per_page)) $this->setItemsPerPage($items_per_page);
		if(!is_null($current_page)) $this->setCurrentPage($current_page);
	} // __construct

	function isCurrent($page = null) {
		$page = is_null($page) ? $this->getCurrentPage() : (integer) $page;
		return $page == $this->getCurrentPage();
	} // isCurrent

	function isFirst($page = null) {
		$page = is_null($page) ? $this->getCurrentPage() : (integer) $page;
		return $page == 1;
	} // isFirst

	function isLast($page = null) {
		$page = is_null($page) ? $this->getCurrentPage() : (integer) $page;
		if(is_null($last = $this->getTotalPages())) return false;
		return $page == $last;
	} // isLast

	function getPrevious() {
		return $this->hasPrevious() ? $this->getCurrentPage() - 1 : $this->getCurrentPage();
	} // getPreviousPage

	function hasPrevious($page = null) {
		$page = is_null($page) ? $this->getCurrentPage() : (integer) $page;
		return $page > 1;
	} // hasPrevious

	function getNext() {
		return $this->hasNext() ? $this->getCurrentPage() + 1 : $this->getCurrentPage();
	} // getNext

	function hasNext($page = null) {
		$page = is_null($page) ? $this->getCurrentPage() : (integer) $page;
		if(is_null($last = $this->getTotalPages())) return false;
		return $page < $last;
	} // hasNext

	function getTotalPages() {
		if(is_int($this->total_pages)) return $this->total_pages;
		if(($this->getItemsPerPage() < 1) || ($this->getTotalItems() < 1)) return 1; // there must be one page
		
		if(($this->getTotalItems() % $this->getItemsPerPage()) == 0) {
		$this->total_pages = (integer) ($this->getTotalItems() / $this->getItemsPerPage());
		} else {
		$this->total_pages = (integer) ($this->getTotalItems() / $this->getItemsPerPage()) + 1; 
		} // if
		
		return $this->total_pages;
	} // getTotalPages

	function countItemsOnPage($page) {
		$page = (integer) $page;
		if($page < 1) $page = 1;
		
		if(($page + 1) * $this->getItemsPerPage() > $this->getTotalItems()) {
		return $this->getTotalItems() - (($page - 1) * $this->getItemsPerPage());
		} else {
		return $this->getItemsPerPage();
		} // if
	} // countItemsOnPage

	function getLimitStart($page = null) {
		
		$page = is_null($page) ? $this->getCurrentPage() : (integer) $page;
		$page -= 1; // Start is one page down...
		
		return ($page * $this->getItemsPerPage());
		
	} // getLimitStart

	function getTotalItems() {
		return $this->total_items;
	} // getTotalItems

	function setTotalItems($value) {
		$this->total_pages = null;
		$this->total_items = (integer) $value > 0 ? (integer) $value : 0;
	} // setTotalItems

	function getItemsPerPage() {
		return $this->items_per_page;
	} // getItemsPerPage

	function setItemsPerPage($value) {
		$this->total_pages = null;
		$this->items_per_page = (integer) $value > 0 ? (integer) $value : 10;
	} // setItemsPerPage

	function setItems($items)
	{
		$this->items = $items;
	}

	function getItems()
	{
		return $this->items;
	}

	function hasItems()
	{
		return count($this->items) > 0;
	}

	function getCurrentPage() {
		return $this->current_page;
	} // getCurrentPage

	function getFirtItemOnPage()
	{
		return ($this->getCurrentPage() - 1) * $this->getItemsPerPage() + 1;
	}

	function getLastItemOnPage()
	{
		return ($this->getCurrentPage() -1 ) * $this->getItemsPerPage() + $this->countItemsOnPage($this->getCurrentPage());
	}

	function setCurrentPage($value) {
		$this->current_page = (integer) $value > 0 ? (integer) $value : 1;
	} // setCurrentPage

}

?>