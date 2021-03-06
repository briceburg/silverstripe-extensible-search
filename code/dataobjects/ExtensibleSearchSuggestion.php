<?php

/**
 *	Details of a user search generated suggestion.
 *	@author Marcus Nyeholt <marcus@silverstripe.com.au>
 *	@author Nathan Glasl <nathan@silverstripe.com.au>
 */

class ExtensibleSearchSuggestion extends DataObject implements PermissionProvider {

	/**
	 *	Store the frequency to make search suggestion relevance more efficient.
	 */

	private static $db = array(
		'Term' => 'Varchar(255)',
		'Frequency' => 'Int',
		'Approved' => 'Boolean'
	);

	private static $has_one = array(
		'ExtensibleSearchPage' => 'ExtensibleSearchPage'
	);

	private static $default_sort = 'Frequency DESC, Term ASC';

	private static $summary_fields = array(
		'Term',
		'FrequencySummary',
		'isPermissionRestricted',
		'ApprovedField'
	);

	private static $field_labels = array(
		'Term' => 'Search Term',
		'FrequencySummary' => 'Analytic Frequency',
		'isPermissionRestricted' => 'Is Permission Restricted?',
		'ApprovedField' => 'Approved?'
	);

	/**
	 *	Allow the ability to disable search suggestions.
	 */

	private static $enable_suggestions = true;

	/**
	 *	Allow the ability to automatically approve user search generated suggestions.
	 */

	private static $automatic_approval = false;

	/**
	 *	Create a unique permission for management of search suggestions.
	 */

	public function providePermissions() {

		Requirements::css(EXTENSIBLE_SEARCH_PATH . '/css/extensible-search.css');
		return array(
			'EXTENSIBLE_SEARCH_SUGGESTIONS' => array(
				'category' => 'Extensible search',
				'name' => 'Manage search suggestions',
				'help' => 'Allow management of user search generated suggestions.'
			)
		);
	}

	/**
	 *	Allow access for CMS users viewing search suggestions.
	 *
	 *	@parameter <{CURRENT_MEMBER}> member
	 *	@return boolean
	 */

	public function canView($member = null) {

		return true;
	}

	/**
	 *	Determine access for the current CMS user creating search suggestions.
	 *
	 *	@parameter <{CURRENT_MEMBER}> member
	 *	@return boolean
	 */

	public function canEdit($member = null) {

		return $this->canCreate($member);
	}

	public function canCreate($member = null) {

		return Permission::checkMember($member, 'EXTENSIBLE_SEARCH_SUGGESTIONS');
	}

	/**
	 *	Determine access for the current CMS user deleting search suggestions.
	 *
	 *	@parameter <{CURRENT_MEMBER}> member
	 *	@return boolean
	 */

	public function canDelete($member = null) {

		return Permission::checkMember($member, 'EXTENSIBLE_SEARCH_SUGGESTIONS');
	}

	/**
	 *	Restrict access for CMS users editing search suggestions.
	 */

	public function getCMSFields() {

		$fields = parent::getCMSFields();
		$fields->removeByName('ExtensibleSearchPageID');

		// Make sure the search suggestions and frequency are read only.

		if($this->Term) {
			$fields->makeFieldReadonly('Term');
		}
		$fields->removeByName('Frequency');

		// Update the approved flag positioning.

		$fields->removeByName('Approved');
		$fields->addFieldToTab('Root.Main', $approved = FieldGroup::create(
			'Approved?'
		)->addExtraClass('approved wrapper'));
		$approved->push($this->getApprovedField());
		return $fields;
	}

	/**
	 *	Retrieve the frequency for display purposes.
	 *
	 *	@return string
	 */

	public function getFrequencySummary() {

		return $this->Frequency ? $this->Frequency : '-';
	}

	/**
	 *	Determine if the search suggestion is permission restricted.
	 *
	 *	@return string
	 */

	public function isPermissionRestricted() {

		return $this->ExtensibleSearchPage()->exists() ? 'true' : 'false';
	}

	/**
	 *	Retrieve the approved field for update purposes.
	 *
	 *	@return string
	 */

	public function getApprovedField() {

		$approved = CheckboxField::create(
			'Approved',
			'',
			$this->Approved
		)->addExtraClass('approved');

		// Restrict this field appropriately.

		$user = Member::currentUserID();
		if(!Permission::checkMember($user, 'EXTENSIBLE_SEARCH_SUGGESTIONS')) {
			$approved->setAttribute('disabled', 'true');
		}
		return $approved;
	}

}
