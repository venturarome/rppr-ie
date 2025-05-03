<?php

require_once 'Property.php';

class InputFile
{
    private static ?InputFile $instance = null;
    private $file;

    private function __construct() {
        $this->file = fopen('../data/PPR-ALL.csv', 'rb');
        if ($this->file === false) {
            throw new RuntimeException('Could not open file');
        }
        fgetcsv($this->file, escape: '\\');   // Purge header line
    }

    public function __destruct() {
        fclose($this->file);
    }

    public static function get(): InputFile
    {
        if (self::$instance === null) {
            self::$instance = new InputFile();
        }
        return self::$instance;
    }

    public function next(): ?Property
    {
        if ($row = fgetcsv($this->file, escape: '\\')) {
            return new Property(
                date: DateTimeImmutable::createFromFormat("d/m/Y", $row[0]),
                address: $row[1],
                county: $row[2],
                eircode: $row[3] !== '' ? $row[3] : null,
                price: (int)str_replace(",", "", substr($row[4], 1)),
                type: str_starts_with($row[7], 'New') ? 'new' : 'second_hand',
            );
        }
        return null;
    }
}