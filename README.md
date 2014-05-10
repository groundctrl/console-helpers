console-helpers
===============

A collection of helpers for the Symfony Console Component.

## Installation

```
"require": {
    "ctrl/console-helpers": "~1.0@dev"
}
```

## The Helpers

### TableGeneratorHelper

Register the helper:

```
/** @var \Symfony\Component\Console\Application $app */
$app->getHelperSet()->set(new \Ctrl\Console\Helper\TableGeneratorHelper());
```

Generate a table:

```
public function execute(InputInterface $input, OutputInterface $output)
{
    // Retrieve the Helper from the HelperSet
    $tableGenerator = $this->getHelperSet()->get('table_generator');

    /** @var \Traversable $data */
    $data = getATraversable();

    // Pass $output to the Generator to render the table immediately.
    $tableGenerator->generate($data, $output);

    // Or, don't, and the generator will return the table instead.
    $table = $tableGenerator->generate($data);
    $table->render($output);
}
```

### CsvGeneratorHelper

Register the helper:

```
/** @var \Symfony\Component\Console\Application $app */
$app->getHelperSet()->set(new \Ctrl\Console\Helper\CsvGeneratorHelper());
```

Usage:

*Coming Soon*
