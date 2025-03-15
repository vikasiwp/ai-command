<?php

namespace WP_CLI\AiCommand\RESTControllerList;

class Whitelist {

	protected array $proof_of_concept_whitelist = array(
		'/wp/v2/pages' =>  array(
			'GET'  => 'Get a list of pages',
			'POST' => 'Create a new page'
		),
		'/wp/v2/posts' =>  array(
			'GET'  => 'Get a list of posts',
			'POST' => 'Create a new post'
		),
		'/wp/v2/categories' => array(
			'GET'  => 'Get a list of categories',
			'POST' => 'Create a new category'
		),
	);

	public function __construct() {}

	public function get() {
		return $this->proof_of_concept_whitelist;
	}
}
