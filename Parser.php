<?php
namespace Beanbun\Middleware;

use QL\QueryList;

class Parser
{
	public $auto = true;
	public $ql = null;

	public function __construct($config = [])
	{
		if (isset($config['auto'])) {
			$this->auto = boolval($config['auto']);
		}
	}

	public function handle($beanbun)
	{
		$beanbun->parser = $this;

		if ($this->auto) {
			$beanbun->data = [];
			$beanbun->afterDownloadPageHooks[] = [$this, 'parseData'];
			$beanbun->afterDiscoverHooks[] = [$this, 'cleanData'];
		}
	}

	public function __call($method, $args)
	{
		return $this->ql->{$method}($args);
	}

	public function parseData($beanbun)
	{
		if (!isset($beanbun->fields) || empty($beanbun->fields)) {
			return;
		}

		$this->ql = QueryList::Query($beanbun->page, []);
		$beanbun->data = $this->getData($beanbun->fields);
	}

	public function cleanData($beanbun)
	{
		$beanbun->data = [];
		$this->ql = null;
	}

	public function getData($fields)
	{
		$data = [];
		foreach ($fields as $field) {
			if (isset($field['selector'])) {
				$repeated = isset($field['repeated']) && $field['repeated'];
				if (!$repeated && !strstr($field['selector'][0], ':eq(')) {
					$field['selector'][0] .= ':eq(0)';
				}
				$rules = [
					$field['name'] => $field['selector']
				];
				$callback = function ($item) use ($field) {
					$item = $item[$field['name']];
					if (isset($field['callback'])) {
						$item = call_user_func($field['callback'], $item);
					}
					return $item;
				};
				$data[$field['name']] = $this->ql->setQuery($rules)->getData($callback);
				if (is_null($data[$field['name']])) {
					$data = [];
					break;
				} else {
					$data[$field['name']] = $repeated ? $data[$field['name']] : $data[$field['name']][0];
				}
				continue ;
			}

			if (isset($field['children'])) {
				$data[$field['name']] = $this->getData($field['children']);
			}
		}
		return $data;
	}
}