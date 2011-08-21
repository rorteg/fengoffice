<?php

  /**
  * WorkspaceBillings
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class WorkspaceBillings extends BaseWorkspaceBillings {
  	
  function clearByProject(Project $project){
		return self::delete('project_id = ' . $project->getId());
	}
	
  } // WorkspaceBillings 
  
?>