<?php

/**
 * Contact class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class Contact extends BaseContact {

	/**
	 * Contacts are searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;
	
	protected $is_read_markable = false;

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('email', 'email2', 'email3', 'firstname', 'w_address', 'h_address', 'o_address');
	protected $searchable_composite_columns = array(
		'firstname' => array('firstname', 'lastname'),
		'w_address' => array('w_address', 'w_city', 'w_state', 'w_zipcode', 'w_country'),
		'h_address' => array('h_address', 'h_city', 'h_state', 'h_zipcode', 'h_country'),
		'o_address' => array('o_address', 'o_city', 'o_state', 'o_zipcode', 'o_country')
	);

	/**
	 * This project object is taggable
	 *
	 * @var boolean
	 */
	protected $is_taggable = true;

	private $user;

	private $company;

	/**
	 * Construct contact object
	 *
	 * @param void
	 * @return User
	 */
	function __construct() {
		parent::__construct();
	} // __construct

	/**
	 * Check if this contact is member of specific company
	 *
	 * @access public
	 * @param Company $company
	 * @return boolean
	 */
	function isMemberOf(Company $company) {
		return $this->getCompanyId() == $company->getId();
	} // isMemberOf

	// ---------------------------------------------------
	//  IMs
	// ---------------------------------------------------

	/**
	 * Return true if this contact have at least one IM address
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasImValue() {
		return ContactImValues::count('`contact_id` = ' . DB::escape($this->getId()));
	} // hasImValue

	/**
	 * Return all IM values
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getImValues() {
		return ContactImValues::getByContact($this);
	} // getImValues

	/**
	 * Return value of specific IM. This function will return null if IM is not found
	 *
	 * @access public
	 * @param ImType $im_type
	 * @return string
	 */
	function getImValue(ImType $im_type) {
		$im_value = ContactImValues::findById(array('contact_id' => $this->getId(), 'im_type_id' => $im_type->getId()));
		return $im_value instanceof ContactImValue && (trim($im_value->getValue()) <> '') ? $im_value->getValue() : null;
	} // getImValue

	/**
	 * Return default IM value. If value was not found NULL is returned
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDefaultImValue() {
		$default_im_type = $this->getDefaultImType();
		return $this->getImValue($default_im_type);
	} // getDefaultImValue

	/**
	 * Return default contact IM type. If there is no default contact IM type NULL is returned
	 *
	 * @access public
	 * @param void
	 * @return ImType
	 */
	function getDefaultImType() {
		return ContactImValues::getDefaultContactImType($this);
	} // getDefaultImType

	/**
	 * Clear all IM values
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function clearImValues() {
		return ContactImValues::instance()->clearByContact($this);
	} // clearImValues

	// ---------------------------------------------------
	//  Retrive
	// ---------------------------------------------------

	/**
	 * Return owner company
	 *
	 * @access public
	 * @param void
	 * @return Company
	 */
	function getCompany() {
		if(is_null($this->company)) {
			if ($this->getCompanyId() > 0)
				$this->company = Companies::findById($this->getCompanyId());
		}
		return $this->company;
	} // getCompany

	/**
	 * Return assigned User
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getUser() {
		if(is_null($this->user)) {
			$this->user = Users::findById($this->getUserId());
		} // if
		return $this->user;
	} // getCompany

	/**
	 * Return display name for this contact.
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDisplayName() {
		$mn = "";
		if (parent::getMiddlename() != "") $mn = " " . parent::getMiddlename();
		$display = parent::getFirstName(). $mn ." ".parent::getLastName();
		return trim($display);
	} // getDisplayName

	/**
	 * Return display name with last name first for this contact
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getReverseDisplayName() {
		$mn = "";
		if (parent::getMiddlename() != "")
		$mn = " " . parent::getMiddlename();
		if (parent::getLastName() != "")
		$display = parent::getLastName().", ".parent::getFirstName() . $mn;
		else
		$display = parent::getFirstName() . $mn;
		return trim($display);
	} // getReverseDisplayName

	/**
	 * Returns true if we have title value set
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasTitle() {
		return trim($this->getTitle()) <> '';
	} // hasTitle


	/**
	 * Returns true if contact has an assigned user
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasUser() {
		return ($this->getUserId() > 0 && $this->getUser() instanceOf User);
	} // hasTitle

	/**
	 * Returns true if contact has an assigned company
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasCompany() {
		return ($this->getCompanyId() > 0 && $this->getCompany() instanceOf Company);
	} // hasTitle


	// ---------------------------------------------------
	//  Picture file
	// ---------------------------------------------------

	/**
	 * Set contact picture from $source file
	 *
	 * @param string $source Source file
	 * @param integer $max_width Max picture widht
	 * @param integer $max_height Max picture height
	 * @param boolean $save Save user object when done
	 * @return string
	 */
	function setPicture($source, $fileType, $max_width = 50, $max_height = 50, $save = true) {
		if (!is_readable($source)) return false;

		do {
			$temp_file = ROOT . '/cache/' . sha1(uniqid(rand(), true));
		} while(is_file($temp_file));

		Env::useLibrary('simplegd');

		$image = new SimpleGdImage($source);
		if ($image->getImageType() == IMAGETYPE_PNG) {
			if ($image->getHeight() > 128 || $image->getWidth() > 128) {
				//	resize images if are png bigger than 128 px
				$thumb = $image->scale($max_width, $max_height, SimpleGdImage::BOUNDARY_DECREASE_ONLY, false);
				$thumb->saveAs($temp_file, IMAGETYPE_PNG);
				$public_fileId = FileRepository::addFile($temp_file, array('type' => 'image/png', 'public' => true));
			} else {
				//keep the png as it is.
				$public_fileId = FileRepository::addFile($source, array('type' => 'image/png', 'public' => true));
			}
		} else {
			$thumb = $image->scale($max_width, $max_height, SimpleGdImage::BOUNDARY_DECREASE_ONLY, false);
			$thumb->saveAs($temp_file, IMAGETYPE_PNG);
			$public_fileId = FileRepository::addFile($temp_file, array('type' => 'image/png', 'public' => true));
		}

		if($public_fileId) {
			$this->setPictureFile($public_fileId);
			if($save) {
				$this->save();
			} // if
		} // if

		$result = true;

		// Cleanup
		if(!$result && $public_fileId) {
			FileRepository::deleteFile($public_fileId);
		} // if
		@unlink($temp_file);

		return $result;
	} // setPicture

	/**
	 * Delete picture
	 *
	 * @param void
	 * @return null
	 */
	function deletePicture() {
		if($this->hasPicture()) {
			FileRepository::deleteFile($this->getPictureFile());
			$this->setPictureFile('');
		} // if
	} // deletePicture

	/**
	 * Return path to the picture file. This function just generates the path, does not check if file really exists
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getPicturePath() {
		return PublicFiles::getFilePath($this->getPictureFile());
	} // getPicturePath

	/**
	 * Return URL of picture
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getPictureUrl() {
		return $this->hasPicture() ? get_url('files', 'get_public_file', array('id' => $this->getPictureFile())): get_image_url('avatar.gif');
		//return $this->hasPicture() ? PublicFiles::getFileUrl($this->getPictureFile()) : get_image_url('avatar.gif');
	} // getPictureUrl

	/**
	 * Check if this user has uploaded picture
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasPicture() {
		return (trim($this->getPictureFile()) <> '') && FileRepository::isInRepository($this->getPictureFile());
	} // hasPicture


	/**
	 * Return name of home country
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getHCountryName() {
		if ($this->getHCountry())
		return lang('country ' . $this->getHCountry());
		return '';
	} // getHCountryName

	/**
	 * Return name of work country
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getWCountryName() {
		if ($this->getWCountry())
		return lang('country ' . $this->getWCountry());
		return '';
	} // getWCountryName

	/**
	 * Return name of other country
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getOCountryName() {
		if ($this->getOCountry())
		return lang('country ' . $this->getOCountry());
		return '';
	} // getOCountryName

	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return view contact URL of this contact
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		return get_url('contact', 'card', $this->getId());
	} // getAccountUrl

	/**
	 * Return URL that will be used to create a user based on the info of this contact
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCreateUserUrl() {
		return get_url('contact', 'create_user', $this->getId());
	} //  getCreateUserUrl

	/**
	 * Show contact card page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function getCardUrl() {
		return get_url('contact', 'card', $this->getId());
	} // getCardUrl

	/**
	 * Return edit contact URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('contact', 'edit', $this->getId());
	} // getEditUrl

	/**
	 * Return add contact URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAddUrl() {
		return get_url('contact', 'add');
	} // getEditUrl

	/**
	 * Return delete contact URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('contact', 'delete', $this->getId());
	} // getDeleteUrl

	/**
	 * Return update picture URL
	 *
	 * @param string
	 * @return string
	 */
	function getUpdatePictureUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('contact', 'edit_picture', $attributes);
	} // getUpdatePictureUrl

	/**
	 * Return delete picture URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeletePictureUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('contact', 'delete_picture', $attributes);
	} // getDeletePictureUrl

	/**
	 * Return assign to project URL
	 *
	 * @param void
	 * @return string
	 */
	function getAssignToProjectUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('contact', 'assign_to_project', $attributes);
	} // getDeletePictureUrl

	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	/**
	 * Validate data before save
	 *
	 * @access public
	 * @param array $errors
	 * @return void
	 */
	function validate(&$errors) {

		// Validate username if present
		if(!$this->validatePresenceOf('lastname') && !$this->validatePresenceOf('firstname')) {
			$errors[] = lang('contact identifier required');
		}
		if (!$this->validateUniquenessOf('firstname','lastname' )) { // if
			$errors[] = lang('name must be unique');
		}

		//if email address is entered, it must be unique
		if($this->validatePresenceOf('email')) {
			$this->setEmail(trim($this->getEmail()));
			if(!$this->validateFormatOf('email', EMAIL_FORMAT)) $errors[] = lang('invalid email address');
			if(!$this->validateUniquenessOf('email')) $errors[] = lang('email address must be unique');
		}
		if($this->validatePresenceOf('email2')) {
			$this->setEmail2(trim($this->getEmail2()));
			if(!$this->validateFormatOf('email2', EMAIL_FORMAT)) $errors[] = lang('invalid email address');
		}
		if($this->validatePresenceOf('email3')) {
			$this->setEmail3(trim($this->getEmail3()));
			if(!$this->validateFormatOf('email3', EMAIL_FORMAT)) $errors[] = lang('invalid email address');
		}
	} // validate

	/**
	 * Delete this object
	 *
	 * @param void
	 * @return boolean
	 */
	function delete() {
		if($this->getUserId() && logged_user() instanceof User && !can_manage_security(logged_user())) {
			return false;
		} // if

		$roles = $this->getRoles();
		if($roles){
			foreach ($roles as $role)
			$role->delete();
		}
		$this->deletePicture();
		return parent::delete();
	} // delete


	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->getDisplayName();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'contact';
	} // getObjectTypeName

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getCardUrl();
	} // getObjectUrl

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------


	/**
	 * Returns true if $user can access this contact
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		return can_manage_contacts($user, true) || can_read($user, $this);
	} // canView

	/**
	 * Check if specific user can add contacts
	 *
	 * @access public
	 * @param User $user
	 * @param Project $project
	 * @return booelean
	 */
	function canAdd(User $user, Project $project) {
		return can_manage_contacts($user, true) || can_add($user, $project, get_class(Contacts::instance()));;
	} // canAdd

	/**
	 * Check if specific user can edit this contact
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		if ($this->getUserId()) {
			// a contact that has a user assigned to it can be modified by anybody that can manage security (this is: users and permissions) or the user himself.
			return can_manage_contacts($user, true) || can_manage_security($user) || $this->getUserId() == $user->getId() || can_write($user, $this);
		} else {
			return can_manage_contacts($user, true) || can_write($user, $this);
		}
	} // canEdit

	/**
	 * Check if specific user can delete this contact
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return can_manage_contacts($user, true) || can_delete($user, $this);
	} // canDelete

	function canLinkObject(User $user){
		return can_manage_contacts($user, true) || can_read($user, $this);
	}

	// ---------------------------------------------------
	//  Roles
	// ---------------------------------------------------

	/**
	 * Return all roles for this contact
	 *
	 * @access public
	 * @return array
	 */
	function getRoles()
	{
		if ($this->getId() == '') return array();
		return ProjectContacts::getRolesByContact($this);
	}

	/**
	 * Return the role for this contact in a specific project
	 *
	 * @param Project $project
	 * @return ProjectContact
	 */
	function getRole(Project $project)
	{
		if (!$project instanceof Project) {
			return null;
		}
		return ProjectContacts::getRole($this,$project);
	}

	// ---------------------------------------------------
	//  Addresses
	// ---------------------------------------------------



	function getFullHomeAddress()
	{
		$line1 = $this->getHAddress();
		$line2 = '';
		$line3 = '';
		 
		if ($this->getHCity() != '')
		$line2 = $this->getHCity();
		 
		if ($this->getHState() != '')
		{
			if ($line2 != '')
			$line2 .= ', ';
			$line2 .= $this->getHState();
		}
		 
		if ($this->getHZipcode() != '')
		{
			if ($line2 != '')
			$line2 .= ', ';
			$line2 .= $this->getHZipcode();
		}
		 
		if ($this->getHCountry() != '')
		$line3 = $this->getHCountryName();

		$result = $line1;
		if ($line2 != '')
		$result .= "\n" . $line2;
		if ($line3 != '')
		$result .= "\n" . $line3;
		 
		return $result;
	}

	/**
	 * Returns the full work address
	 *
	 * @return string
	 */
	function getFullWorkAddress()
	{
		$line1 = $this->getWAddress();
		 
		$line2 = '';
		if ($this->getWCity() != '')
		$line2 = $this->getWCity();
		 
		if ($this->getWState() != '')
		{
			if ($line2 != '')
			$line2 .= ', ';
			$line2 .= $this->getWState();
		}
		 
		if ($this->getWZipcode() != '')
		{
			if ($line2 != '')
			$line2 .= ', ';
			$line2 .= $this->getWZipcode();
		}
		 
		$line3 = '';
		if ($this->getWCountry() != '')
		$line3 = $this->getWCountryName();

		$result = $line1;
		if ($line2 != '')
		$result .= "\n" . $line2;
		if ($line3 != '')
		$result .= "\n" . $line3;
		 
		return $result;
	}

	/**
	 * Returns the full work address
	 *
	 * @return string
	 */
	function getFullOtherAddress()
	{
		$line1 = $this->getOAddress();
		$line2 = '';
		$line3 = '';
		 
		if ($this->getOCity() != '')
		$line2 = $this->getOCity();
		 
		if ($this->getOState() != '')
		{
			if ($line2 != '')
			$line2 .= ', ';
			$line2 .= $this->getOState();
		}
		 
		if ($this->getOZipcode() != '')
		{
			if ($line2 != '')
			$line2 .= ', ';
			$line2 .= $this->getOZipcode();
		}
		 
		if ($this->getOCountry() != '')
		$line3 = $this->getOCountryName();

		$result = $line1;
		if ($line2 != '')
		$result .= "\n" . $line2;
		if ($line3 != '')
		$result .= "\n" . $line3;
		 
		return $result;
	}

	function getDashboardObject(){
		$wsIds = $this->getWorkspacesIdsCSV(logged_user()->getWorkspacesQuery());
		 
		if($this->getUpdatedById() > 0 && $this->getUpdatedBy() instanceof User){
			$updated_by_id = $this->getUpdatedBy()->getObjectId();
			$updated_by_name = $this->getUpdatedByDisplayName();
			$updated_on = $this->getObjectUpdateTime() instanceof DateTimeValue ? ($this->getObjectUpdateTime()->isToday() ? format_time($this->getObjectUpdateTime()) : format_datetime($this->getObjectUpdateTime())) : lang('n/a');
		}else {
			if($this->getCreatedById() > 0 && $this->getCreatedBy() instanceof User)
			$updated_by_id = $this->getCreatedBy()->getId();
			else
			$updated_by_id = lang('n/a');
			$updated_by_name = $this->getCreatedByDisplayName();
			$updated_on = $this->getObjectCreationTime() instanceof DateTimeValue ? ($this->getObjectCreationTime()->isToday() ? format_time($this->getObjectCreationTime()) : format_datetime($this->getObjectCreationTime())) : lang('n/a');
		}
		 
		$deletedOn = $this->getTrashedOn() instanceof DateTimeValue ? ($this->getTrashedOn()->isToday() ? format_time($this->getTrashedOn()) : format_datetime($this->getTrashedOn(), 'M j')) : lang('n/a');
		if ($this->getTrashedById() > 0)
			$deletedBy = Users::findById($this->getTrashedById());
		if (isset($deletedBy) && $deletedBy instanceof User) {
			$deletedBy = $deletedBy->getDisplayName();
		} else {
			$deletedBy = lang("n/a");
		}
		 
		$archivedOn = $this->getArchivedOn() instanceof DateTimeValue ? ($this->getArchivedOn()->isToday() ? format_time($this->getArchivedOn()) : format_datetime($this->getArchivedOn(), 'M j')) : lang('n/a');
		if ($this->getArchivedById() > 0)
			$archivedBy = Users::findById($this->getArchivedById());
		if (isset($archivedBy) && $archivedBy instanceof User) {
			$archivedBy = $archivedBy->getDisplayName();
		} else {
			$archivedBy = lang("n/a");
		}
		return array(
				"id" => $this->getObjectTypeName() . $this->getId(),
				"object_id" => $this->getId(),
				"name" => $this->getObjectName(),
				"type" => $this->getObjectTypeName(),
				"tags" => project_object_tags($this),
				"createdBy" => $this->getCreatedByDisplayName(),// Users::findById($this->getCreatedBy())->getUsername(),
				"createdById" => $this->getCreatedById(),
    			"dateCreated" => $this->getObjectCreationTime() instanceof DateTimeValue ? ($this->getObjectCreationTime()->isToday() ? format_time($this->getObjectCreationTime()) : format_datetime($this->getObjectCreationTime())) : lang('n/a'),
				"updatedBy" => $updated_by_name,
				"updatedById" => $updated_by_id,
				"dateUpdated" => $updated_on,
				"wsIds" => $wsIds,
				"url" => $this->getObjectUrl(),
				"manager" => get_class($this->manager()),
    			"deletedById" => $this->getTrashedById(),
    			"deletedBy" => $deletedBy,
    			"dateDeleted" => $deletedOn,
    			"archivedById" => $this->getArchivedById(),
    			"archivedBy" => $archivedBy,
    			"dateArchived" => $archivedOn
		);
	}

	/**
	 * Returns CSVs of the workspaces the contact is assigned to
	 *
	 * @return unknown
	 */
	function getProjectIdsCSV($wsIds = null, $extra_cond = null) {
		$workspaces = ProjectContacts::getProjectsByContact($this, $extra_cond);
		$result = array();
		if($workspaces){
			if (!is_null($wsIds)){
				foreach($workspaces as $w){
					if ($this->isInCsv($w->getId(),$wsIds))
					$result[] = $w->getId();
				}
			} else foreach ($workspaces as $w){
				$result[] = $w->getId();
			}
		}
		return implode(',',$result);
	}

	
    /**
	 * This function will return content of specific searchable column. It uses inherited
	 * behaviour for all columns except for `firstname`, which is used as a column representing
	 * the first and last name of the contact, and all of the addresses, which are saved in full
	 * form.
	 *
	 * @param string $column_name Column name
	 * @return string
	 */
	function getSearchableColumnContent($column_name) {
		if($column_name == 'firstname') {
			return trim($this->getFirstname() . ' ' . $this->getLastname());
		} else if($column_name == 'w_address') {
			return strip_tags(trim($this->getFullWorkAddress()));
		} else if($column_name == 'h_address') {
			return strip_tags(trim($this->getFullHomeAddress()));
		} else if($column_name == 'o_address') {
			return strip_tags(trim($this->getFullOtherAddress()));
		}
		
		return parent::getSearchableColumnContent($column_name);
	} // getSearchableColumnContent
}
?>