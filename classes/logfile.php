<?php

/**
 * Description of logfile
 *
 * @author Chris Vaughan
 */
class Logfile {

    private static $logfile;
    private static $noerrors = 0;
    private static $errors;
    private static $name;

    static function create($name) {
        self::$name = $name;
        $subname = date("YmdHis");
        self::$logfile = fopen($name . $subname . ".log", "w") or die("Unable to open logfile file!");
        Logfile::writeWhen("Logfile " . $subname . ".log created");
        self::deleteOldFiles();
        self::$errors = [];
    }

    static function deleteOldFiles() {
        $today = date("Y-m-d");
        $date = new DateTime($today);
        $date->sub(new DateInterval('P3D'));
        $datestring = $date->format('Y-m-d');
        foreach (glob(self::$name . "*.log") as $filename) {
            //echo "$filename size " . filesize($filename) . "\n";
            $modified = date("Y-m-d", filemtime($filename));
            if ($modified < $datestring) {
                unlink($filename);
                logfile::writeWhen("Old logfile deleted: " . $filename);
            }
        }
    }

    static function write($text) {
        if (isset(self::$logfile)) {
            fwrite(self::$logfile, $text . "\n");
        }
    }

    static function writeWhen($text) {
        $today = new DateTime();
        $when = $today->format('Y-m-d H:i:s');
        self::write($when . " " . $text);
    }

    static function writeError($text) {
        self::$noerrors += 1;
        self::writeWhen(" ERROR: " . $text);
        self::addError($text);
    }

    private static function addError($text) {
        if (self::$noerrors <= 10) {
            self::$errors[] = $text;
        }
    }

    static function getNoErrors() {
        return self::$noerrors;
    }

    static function getErrors() {
        return self::$errors;
    }

    static function resetNoErrrors() {
        self::$noerrors = 0;
    }

    static function close() {
        if (isset(self::$logfile)) {
            fclose(self::$logfile);
            self::$logfile = NULL;
        }
    }
}