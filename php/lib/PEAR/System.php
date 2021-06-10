                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                _string($program) || '' == $program) {
            return $fallback;
        }

        // available since 4.3.0RC2
        if (defined('PATH_SEPARATOR')) {
            $path_delim = PATH_SEPARATOR;
        } else {
            $path_delim = OS_WINDOWS ? ';' : ':';
        }
        // full path given
        if (basename($program) != $program) {
            $path_elements[] = dirname($program);
            $program = basename($program);
        } else {
            // Honor safe mode
            if (!ini_get('safe_mode') || !$path = ini_get('safe_mode_exec_dir')) {
                $path = getenv('PATH');
                if (!$path) {
                    $path = getenv('Path'); // some OSes are just stupid enough to do this
                }
            }
            $path_elements = explode($path_delim, $path);
        }

        if (OS_WINDOWS) {
            $exe_suffixes = getenv('PATHEXT')
                                ? explode($path_delim, getenv('PATHEXT'))
                                : array('.exe','.bat','.cmd','.com');
            // allow passing a command.exe param
            if (strpos($program, '.') !== false) {
                array_unshift($exe_suffixes, '');
            }
            // is_executable() is not available on windows for PHP4
            $pear_is_executable = (function_exists('is_executable')) ? 'is_executable' : 'is_file';
        } else {
            $exe_suffixes = array('');
            $pear_is_executable = 'is_executable';
        }

        foreach ($exe_suffixes as $suff) {
            foreach ($path_elements as $dir) {
                $file = $dir . DIRECTORY_SEPARATOR . $program . $suff;
                if (@$pear_is_executable($file)) {
                    return $file;
                }
            }
        }
        return $fallback;
    }

    /**
    * The "find" command
    *
    * Usage:
    *
    * System::find($dir);
    * System::find("$dir -type d");
    * System::find("$dir -type f");
    * System::find("$dir -name *.php");
    * System::find("$dir -name *.php -name *.htm*");
    * System::find("$dir -maxdepth 1");
    *
    * Params implmented:
    * $dir            -> Start the search at this directory
    * -type d         -> return only directories
    * -type f         -> return only files
    * -maxdepth <n>   -> max depth of recursion
    * -name <pattern> -> search pattern (bash style). Multiple -name param allowed
    *
    * @param  mixed Either array or string with the command line
    * @return array Array of found files
    *
    */
    function find($args)
    {
        if (!is_array($args)) {
            $args = preg_split('/\s+/', $args, -1, PREG_SPLIT_NO_EMPTY);
        }
        $dir = array_shift($args);
        $patterns = array();
        $depth = 0;
        $do_files = $do_dirs = true;
        for ($i = 0; $i < count($args); $i++) {
            switch ($args[$i]) {
                case '-type':
                    if (in_array($args[$i+1], array('d', 'f'))) {
                        if ($args[$i+1] == 'd') {
                            $do_files = false;
                        } else {
                            $do_dirs = false;
                        }
                    }
                    $i++;
                    break;
                case '-name':
                    if (OS_WINDOWS) {
                        if ($args[$i+1]{0} == '\\') {
                            // prepend drive
                            $args[$i+1] = addslashes(substr(getcwd(), 0, 2) . $args[$i + 1]);
                        }
                        // escape path separators to avoid PCRE problems
                        $args[$i+1] = str_replace('\\', '\\\\', $args[$i+1]);
                    }
                    $patterns[] = "(" . preg_replace(array('/\./', '/\*/'),
                                                     array('\.', '.*', ),
                                                     $args[$i+1])
                                      . ")";
                    $i++;
                    break;
                case '-maxdepth':
                    $depth = $args[$i+1];
                    break;
            }
        }
        $path = System::_dirToStruct($dir, $depth);
        if ($do_files && $do_dirs) {
            $files = array_merge($path['files'], $path['dirs']);
        } elseif ($do_dirs) {
            $files = $path['dirs'];
        } else {
            $files = $path['files'];
        }
        if (count($patterns)) {
            $patterns = implode('|', $patterns);
            $ret = array();
            for ($i = 0; $i < count($files); $i++) {
                if (preg_match("#^$patterns\$#", $files[$i])) {
                    $ret[] = $files[$i];
                }
            }
            return $ret;
        }
        return $files;
    }
}
?>
