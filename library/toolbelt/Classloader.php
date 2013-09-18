<?php
/**
 * Classloader.php
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */


namespace Molly\library\toolbelt;

// Basic requires to instantiate this class.

require_once(getcwd() . "/library/io/dataloaders/interfaces/iLoader.php");
require_once(getcwd() . "/library/io/dataloaders/Loader.php");
require_once(getcwd() . "/library/io/dataloaders/files/FileLoader.php");
require_once(getcwd() . "/library/io/dataloaders/files/exceptions/FileNotFoundException.php");
require_once(getcwd() . "/library/io/dataloaders/files/exceptions/ExpectedFileLocationsNotSetException.php");
require_once(getcwd() . "/library/exceptions/IllegalArgumentException.php");

use Molly\library\dataloaders\files\exceptions\FileNotFoundException;
use Molly\library\dataloaders\files\FileLoader;

class Classloader extends FileLoader {

    protected function __construct() {
        // Simple classloader only tries to load our library
        $this->addExpectedFileLocation("/library");
    }

    public function autoload($class_name) {
        if (strpos($class_name, '\\') !== false) {

            // Namespaced classname
            $ns = explode('\\', $class_name);

            // Check if this is our own Molly-code
            if ($ns[0] == 'Molly') {

                // Unset "molly" it's not needed as a folder.
                unset($ns[0]);

                $guessed_location = "";
                // Build porbable folder by inserting directory seperators between ns parts.
                foreach ($ns as $key => $nspart) {
                    $guessed_location .= $nspart . DIRECTORY_SEPARATOR;
                }

                // Make it a file by adding .php
                $guessed_location = rtrim($guessed_location, DIRECTORY_SEPARATOR) . ".php";

                // Check if this is a real file
                if (file_exists($guessed_location)) {
                    return include_once($guessed_location);
                } else {
                    return false;
                }

            } else {
                // This is not something we'll be able to load.
                return false;
            }
        } else {
            $file_name = $class_name . ".php";

            try {
                // Try locating our file
                $location = $this->locate($file_name);

                if (file_exists($location . $file_name)) {
                    /*
                     * Wouldn't it be nice if we could do something like this here:
                     * use $location . $class_name as $class_name;
                     * Then I wouldn't have to use the namespace loader above.
                     */
                    return include_once($location . $file_name);
                }
            } catch (FileNotFoundException $fnfe) {
                echo $fnfe->getMessage();
                return false;
            }

            return false;
        }
    }
}

spl_autoload_register(array(Classloader::getInstance(), 'autoload'));
