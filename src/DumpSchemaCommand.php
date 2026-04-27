<?php

namespace rdx\graphql;

use Closure;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpSchemaCommand extends Command {

	public function __construct(
		/** @var (Closure(string): Schema) */
		protected Closure $makeSchema,
	) {
		parent::__construct();
	}

	protected function configure() {
		$this->setName('dump-graphql-schema');
		$this->addOption('schema', mode: InputOption::VALUE_REQUIRED);
	}

    protected function execute(InputInterface $input, OutputInterface $output) : int {
    	$schemaName = $input->getOption('schema') ?? '';
    	// var_dump($schemaName);

    	$schema = call_user_func($this->makeSchema, $schemaName);
    	// dump($schema);

    	$dump = trim(SchemaPrinter::doPrint($schema));
    	echo "$dump\n";

    	return 0;
    }

}
