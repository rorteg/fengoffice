<?php

  /**
  * BaseProject class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProject extends ApplicationDataObject {
  
  	protected $objectTypeIdentifier = 'ws';
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getId() {
      return $this->getColumnValue('id');
    } // getId()
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setId($value) {
      return $this->setColumnValue('id', $value);
    } // setId() 
    
    /**
    * Return value of 'name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getName() {
      return $this->getColumnValue('name');
    } // getName()
    
    /**
    * Set value of 'name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setName($value) {
      return $this->setColumnValue('name', $value);
    } // setName() 
    
    /**
    * Return value of 'description' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDescription() {
      return $this->getColumnValue('description');
    } // getDescription()
    
    /**
    * Set value of 'description' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDescription($value) {
      return $this->setColumnValue('description', $value);
    } // setDescription() 
    
    /**
    * Return value of 'show_description_in_overview' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getShowDescriptionInOverview() {
      return $this->getColumnValue('show_description_in_overview');
    } // getShowDescriptionInOverview()
    
    /**
    * Set value of 'show_description_in_overview' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setShowDescriptionInOverview($value) {
      return $this->setColumnValue('show_description_in_overview', $value);
    } // setShowDescriptionInOverview() 
    
    /**
    * Return value of 'completed_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getCompletedOn() {
      return $this->getColumnValue('completed_on');
    } // getCompletedOn()
    
    /**
    * Set value of 'completed_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setCompletedOn($value) {
      return $this->setColumnValue('completed_on', $value);
    } // setCompletedOn() 
    
    /**
    * Return value of 'completed_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCompletedById() {
      return $this->getColumnValue('completed_by_id');
    } // getCompletedById()
    
    /**
    * Set value of 'completed_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCompletedById($value) {
      return $this->setColumnValue('completed_by_id', $value);
    } // setCompletedById() 
    
    /**
    * Return value of 'created_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getCreatedOn() {
      return $this->getColumnValue('created_on');
    } // getCreatedOn()
    
    /**
    * Set value of 'created_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setCreatedOn($value) {
      return $this->setColumnValue('created_on', $value);
    } // setCreatedOn() 
    
    /**
    * Return value of 'created_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCreatedById() {
      return $this->getColumnValue('created_by_id');
    } // getCreatedById()
    
    /**
    * Set value of 'created_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCreatedById($value) {
      return $this->setColumnValue('created_by_id', $value);
    } // setCreatedById() 
    
    /**
    * Return value of 'updated_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getUpdatedOn() {
      return $this->getColumnValue('updated_on');
    } // getUpdatedOn()
    
    /**
    * Set value of 'updated_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setUpdatedOn($value) {
      return $this->setColumnValue('updated_on', $value);
    } // setUpdatedOn() 
    
    /**
    * Return value of 'updated_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getUpdatedById() {
      return $this->getColumnValue('updated_by_id');
    } // getUpdatedById()
    
    /**
    * Set value of 'updated_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setUpdatedById($value) {
      return $this->setColumnValue('updated_by_id', $value);
    } // setUpdatedById() 
    
    /**
    * Return value of 'color' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getColor() {
    	return $this->getColumnValue('color');
    }
    
    /**
    * Set value of 'color' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setColor($value) {
    	$this->setColumnValue('color', $value);
    }
  
  	/* Return value of 'p1' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getP1() {
    	return $this->getColumnValue('p1');
    }
    
    /**
    * Set value of 'p1' field
    *
    * @access public   
    * @param integer $value
    */
    function setP1($value) {
    	$this->setColumnValue('p1', $value);
    }
  
  	/* Return value of 'p2' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getP2() {
    	return $this->getColumnValue('p2');
    }
    
    /**
    * Set value of 'p2' field
    *
    * @access public   
    * @param integer $value
    */
    function setP2($value) {
    	$this->setColumnValue('p2', $value);
    }
  
  	/* Return value of 'p3' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getP3() {
    	return $this->getColumnValue('p3');
    }
    
    /**
    * Set value of 'p3' field
    *
    * @access public   
    * @param integer $value
    */
    function setP3($value) {
    	$this->setColumnValue('p3', $value);
    }
  
  	/* Return value of 'p4' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getP4() {
    	return $this->getColumnValue('p4');
    }
    
    /**
    * Set value of 'p4' field
    *
    * @access public   
    * @param integer $value
    */
    function setP4($value) {
    	$this->setColumnValue('p4', $value);
    }
  
  	/* Return value of 'p5' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getP5() {
    	return $this->getColumnValue('p5');
    }
    
    /**
    * Set value of 'p5' field
    *
    * @access public   
    * @param integer $value
    */
    function setP5($value) {
    	$this->setColumnValue('p5', $value);
    }
  
  	/* Return value of 'p6' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getP6() {
    	return $this->getColumnValue('p6');
    }
    
    /**
    * Set value of 'p6' field
    *
    * @access public   
    * @param integer $value
    */
    function setP6($value) {
    	$this->setColumnValue('p6', $value);
    }
  
  	/* Return value of 'p7' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getP7() {
    	return $this->getColumnValue('p7');
    }
    
    /**
    * Set value of 'p7' field
    *
    * @access public   
    * @param integer $value
    */
    function setP7($value) {
    	$this->setColumnValue('p7', $value);
    }
  
  	/* Return value of 'p8' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getP8() {
    	return $this->getColumnValue('p8');
    }
    
    /**
    * Set value of 'p8' field
    *
    * @access public   
    * @param integer $value
    */
    function setP8($value) {
    	$this->setColumnValue('p8', $value);
    }
  
  	/* Return value of 'p9' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getP9() {
    	return $this->getColumnValue('p9');
    }
    
    /**
    * Set value of 'p9' field
    *
    * @access public   
    * @param integer $value
    */
    function setP9($value) {
    	$this->setColumnValue('p9', $value);
    }
  
  	/* Return value of 'p10' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getP10() {
    	return $this->getColumnValue('p10');
    }
    
    /**
    * Set value of 'p10' field
    *
    * @access public   
    * @param integer $value
    */
    function setP10($value) {
    	$this->setColumnValue('p10', $value);
    }
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Projects 
    */
    function manager() {
      if(!($this->manager instanceof Projects)) $this->manager = Projects::instance();
      return $this->manager;
    } // manager
  
  } // BaseProject 

?>