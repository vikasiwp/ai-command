<?php

return [
	'/wp/v2/pages'                => [
		'GET'  => 'Get a list of pages',
		'POST' => 'Create a new page',
	],
	'/wp/v2/posts'                => [
		'GET'  => 'Get a list of posts',
		'POST' => 'Create a new post',
	],

	// Categories
	'/wp/v2/categories'           => [
		'GET'  => 'Get a list of categories',
		'POST' => 'Create a new category',
	],

	// Users
	'/wp/v2/users'                => [
		'GET'  => 'Retrieve a list of users.',
		'POST' => 'Create a new user.',
	],
	'/wp/v2/users/(?P<id>[\\d]+)' => [
		'GET'    => 'Retrieve a specific user by ID.',
		'POST'   => 'Update a specific user by ID.',
		'DELETE' => 'Delete a specific user by ID.',
	],
];
