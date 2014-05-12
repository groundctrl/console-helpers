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
     * @param array|\Traversable $rows
     * @param OutputInterface $output
     * @param callable $mapper
     * @param string $filename
     * @return integer
     * @throws \RuntimeException
     */
    public function generate($rows, OutputInterface $output, $mapper = null, $filename = null)
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

        $csvFile = $this->getFilename($output, $filename);

        $this->writeCsvFile($csvFile, $rows, array_keys(current($rows)));

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
     * @param OutputInterface $output
     * @param string $default
     * @return string
     * @throws \RuntimeException
     */
    protected function getFilename(OutputInterface $output, $default = null)
    {
        $filename = null;
        if ($this->input instanceof InputInterface && $this->input->hasOption('to-csv')) {
            $path   = $this->input->getOption('to-csv');
            $dir    = dirname($path);
            if ($this->filesystem->exists($dir)) {
                $filename = $path;
            }
        }

        $isInteractive = $this->input->isInteractive();

        if (!$filename && !$default && !$isInteractive) {
            throw new \RuntimeException('The CSV filename has not been provided.');
        } elseif (!$default && !$isInteractive) {
            throw new \RuntimeException('The CSV filename has not been provided.');
        } elseif (!$default) {
            $filename = $this->askForFilename($output);
        } else {
            $filename = $default;
        }

        return $filename;
    }

    /**
     * @param string $filename
     * @param array|\Traversable $data
     * @param array $headers Optional headers for the csv columns
     * @throws \InvalidArgumentException when $data is neither array nor \Traversable
     */
    protected function writeCsvFile($filename, $data, array $headers = [])
    {
        $rows   = $this->normalizeData($data);
        $csv    = new Writer(new \SplFileObject($filename, 'w'));

        if (!empty($headers)) {
            $csv->insertOne($headers);
        }

        $csv->insertAll($rows);
    }

    /**
     * @param $data
     * @return \ArrayIterator
     * @throws \InvalidArgumentException
     */
    private function normalizeData($data)
    {
        if (is_array($data)) {
            $data = new \ArrayIterator($data);
        } elseif (!$data instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf(
                '%s is neither an array nor Traversable',
                is_object($data) ? get_class($data) : gettype($data)
            ));
        }

        return $data;
    }

}
