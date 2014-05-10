<?php

namespace Ctrl\Console\Helper;

use League\Csv\Writer;
use Symfony\Component\Console\Helper\InputAwareHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CsvGeneratorHelper extends InputAwareHelper
{
    /** @var \Symfony\Component\Filesystem\Filesystem */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    function __construct(Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'csv_generator';
    }

    /**
     * @param array|\Iterator $rows
     * @param OutputInterface $output
     * @param null $mapper
     * @return int
     * @throws \RuntimeException
     */
    public function generate($rows, OutputInterface $output, $mapper = null)
    {
        if ($rows instanceof \Iterator) {
            $rows = iterator_to_array($rows);
        } elseif(!is_array($rows) && !$rows instanceof \Traversable) {
            throw new \RuntimeException('$rows must be an array or implement \Traversable');
        }

        if (empty($rows)) {
            throw new \RuntimeException('No rows were returned.');
        }

        if (is_callable($mapper)) { $rows = array_map($mapper, $rows); }

        $filename = null;
        if ($this->input instanceof InputInterface && $this->input->hasOption('to-csv')) {
            $path   = $this->input->getOption('to-csv');
            $dir    = dirname($path);
            if ($this->filesystem->exists($dir)) {
                $filename = $path;
            }
        }

        if (!$filename) {
            $filename = $this->askForFilename($output);
        }

        $this->writeCsvFile($filename, $rows);

        return count($rows);
    }

    /**
     * @param OutputInterface $output
     * @return string
     */
    protected function askForFilename(OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->askAndValidate($output, '<question>Filename:</question> ', function ($answer) {

            if (!$this->filesystem->isAbsolutePath($answer)) {
                throw new \RuntimeException('The filename must be an absolute path.');
            } elseif ($this->filesystem->exists($answer)) {
                throw new \RuntimeException('A file with that filename already exists.');
            }

            return $answer;
        });
    }

    /**
     * @param $filename
     * @param $rows
     */
    protected function writeCsvFile($filename, array $rows = [])
    {
        if (empty($rows)) { return; }

        $csv = new Writer(new \SplFileObject($filename, 'w'));
        $csv->insertOne(array_keys(current($rows)));
        $csv->insertAll($rows);

        $csv->output();
    }

}
