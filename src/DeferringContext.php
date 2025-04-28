<?php

namespace rdx\graphql;

class DeferringContext {

	/** @var list<mixed> */
	public array $queue = [];
	public mixed $cache = [];

	/**
	 * @param AssocArray $args
	 */
	public function __construct(
		public array $args,
	) {}

}
