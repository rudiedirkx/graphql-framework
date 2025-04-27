<?php

namespace rdx\graphql;

class DeferringContext {

	/** @var list<mixed> */
	public array $queue = [];
	/** @var AssocArray */
	public array $cache = [];

	/**
	 * @param AssocArray $args
	 */
	public function __construct(
		public array $args,
	) {}

}
