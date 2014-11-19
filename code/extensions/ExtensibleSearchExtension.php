<?php

/**
 * A controller extension that provides additional methods on page controllers
 * to allow for better searching using an extensible search page
 *
 * @author marcus@silverstripe.com.au
 * @license http://silverstripe.org/bsd-license/
 */
class ExtensibleSearchExtension extends Extension {

	private static $allowed_actions = array(
		'SearchForm',
		'results'
	);

	/**
	 * Returns the default search page for this site
	 *
	 * @return ExtensibleSearchPage
	 */
    public function getSearchPage() {
		// get the search page for this site, if applicable... otherwise use the default
		return class_exists('ExtensibleSearchPage') ? ExtensibleSearchPage::get()->first() : null;
	}

	/**
	 * Get the list of facet values for the given term
	 * 
	 * @param String $term
	 */
	public function Facets($term=null) {
		$sp = $this->owner->getSearchPage();
		if ($sp && $sp->hasMethod('currentFacets')) {
			$facets = $sp->currentFacets($term);
			return $facets;
		}
	}

	/**
	 * The current search query that is being run by the search page. 
	 *
	 * @return String
	 */
	public function SearchQuery() {
		$sp = $this->owner->getSearchPage();
		if ($sp) {
			return $sp->SearchQuery();
		}
	}

	/**
	 * Site search form
	 */
	public function SearchForm() {

		// Retrieve the search form input, excluding any filters.

		return (($page = $this->owner->getSearchPage()) && $page->SearchEngine) ? ModelAsController::controller_for($page)->getForm(false) : null;
	}

}
