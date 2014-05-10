<?php

namespace Ctrl\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

class TableGeneratorHelper extends Helper
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'table_generator';
    }

    /**
     * @param \Traversable $rows
     * @param OutputInterface $output
     * @param callable $mapper
     * @return int|\Symfony\Component\Console\Helper\TableHelper The table, or the number of rows output.
     */
    public function generate(\Traversable $rows, OutputInterface $output = null, $mapper = null)
    {
        /** @var \Symfony\Component\Console\Helper\TableHelper $table */
        $table  = $this->getHelperSet()->get('table');
        $rows   = iterator_to_array($rows);

        if (is_callable($mapper))   { $rows = array_map($mapper, $rows); }
        if (!empty($rows))          { $table->setHeaders(array_keys(current($rows))); }

        $table->addRows($rows);

        if ($output) { $table->render($output); }

        return $output ? count($rows) : $table;
    }
}
