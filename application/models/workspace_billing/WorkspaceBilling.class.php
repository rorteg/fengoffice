<?php

/**
 * WorkspaceBilling class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class WorkspaceBilling extends BaseWorkspaceBilling {

	protected $billing_category;
	
	function getBillingCategory(){
		if(is_null($this->billing_category)) {
			$this->billing_category = BillingCategories::findById($this->getBillingId());
		} // if
		return $this->billing_category;
	}
} // WorkspaceBilling

?>