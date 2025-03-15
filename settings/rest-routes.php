<?php

return [
	'/wp/v2/pages'      => [
		'GET'  => 'Get a list of pages',
		'POST' => 'Create a new page',
	],
	'/wp/v2/posts'      => [
		'GET'  => 'Get a list of posts',
		'POST' => 'Create a new post',
	],
	'/wp/v2/posts/(?P<id>[\d]+)' => [
		'GET' => 'Retrieve a specific post by ID.',
		'POST' => 'Update a specific post by ID.',
		'DELETE' => 'Delete a specific post by ID.'
	],
	'/wp/v2/categories' => [
		'GET'  => 'Get a list of categories',
		'POST' => 'Create a new category',
	],
];
