<?php

declare(strict_types=1);

class RouteInformation
{

	public function __construct(
		private string $route,
		private string $method,
		private $callback,
	) {
	}

	public function get_method(): string
	{
		return $this->method;
	}

	public function is_post(): bool
	{
		return $this->method === 'POST';
	}

	public function is_put(): bool
	{
		return $this->method === 'PUT';
	}

	public function is_delete(): bool
	{
		return $this->method === 'DELETE';
	}

	public function is_put(): bool
	{
		return $this->method === 'PUT';
	}

	public function is_singular(): bool
	{
		// Always true
		if (str_ends_with($this->route, '(?P<id>[\d]+)')) {
			return true;
		}

		// Never true
		if ( ! str_contains($this->route, '?P<id>')) {
			return false;
		}

		return false;
	}

	public function is_list(): bool
	{
		return ! $this->is_singular();
	}

	public function is_wp_rest_controller()
	{
		$allowed = [
			WP_REST_Posts_Controller::class,
			WP_REST_Users_Controller::class,
			WP_REST_Taxonomies_Controller::class,
		];

		foreach ($allowed as $controller) {
			if ($this->callback[0] instanceof $controller) {
				return true;
			}
		}

		return false;
	}

}
