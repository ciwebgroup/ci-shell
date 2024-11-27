<?php
/**
 * An alternative rewrite of core operating system functions when aholes disable the following functions:
 * getmyuid,passthru,leak,listen,diskfreespace,tmpfile,link,dl,system,highlight_file,source,show_source,fpassthru,virtual,posix_ctermid,
 * posix_getcwd,posix_getegid,posix_geteuid,posix_getgid,posix_getgrgid,posix_getgrnam,posix_getgroups,posix_getlogin,posix_getpgid,
 * posix_getpgrp,posix_getpid,posix,_getppid,posix_getpwuid,posix_getrlimit,posix_getsid,posix_getuid,posix_isatty,posix_kill,posix_mkfifo,
 * posix_setegid,posix_seteuid,posix_setgid,posix_setpgid,posix_setsid,posix_setuid,posix_times,posix_ttyname,posix_uname,proc_open,
 * proc_close,proc_nice,proc_terminate,escapeshellcmd,ini_alter,popen,pcntl_exec,socket_accept,socket_bind,socket_clear_error,socket_close,
 * socket_connect,symlink,posix_geteuid,ini_alter,socket_listen,socket_create_listen,socket_read,socket_create_pair,stream_socket_server,
 * shell_exec,exec,putenv...etc
 * 
 * ... because it's really difficult to do your job when another asshole marketing company decided they don't want to provide access to 
 * migrate the customer's website.
 */
// Function similar to 'cat' command - it reads and outputs the content of a file
function php_cat($filePath) {
    if (file_exists($filePath)) {
        echo file_get_contents($filePath);
    } else {
        echo "File not found: $filePath\n";
    }
}

// Function similar to 'ls -la' command - it lists the files and directories in a directory with detailed info
function php_ls($directoryPath) {
    if (is_dir($directoryPath)) {
        $files = scandir($directoryPath);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $directoryPath . DIRECTORY_SEPARATOR . $file;
                $fileInfo = stat($filePath);
                $permissions = substr(sprintf('%o', fileperms($filePath)), -4);
                $owner = function_exists('posix_getpwuid') ? posix_getpwuid($fileInfo['uid'])['name'] : $fileInfo['uid'];
                $group = function_exists('posix_getgrgid') ? posix_getgrgid($fileInfo['gid'])['name'] : $fileInfo['gid'];
                $size = $fileInfo['size'];
                $modifiedTime = date('Y-m-d H:i:s', $fileInfo['mtime']);
                $fileType = is_dir($filePath) ? 'd' : '-';
                
                printf("%s %s %s %10d %s %s\n", $fileType, $permissions, $owner, $size, $modifiedTime, $file);
            }
        }
    } else {
        echo "Directory not found: $directoryPath\n";
    }
}

// Function similar to 'wp-cli db export' - it exports the database to a specified SQL file without using shell commands
function wp_cli_db_export($filePath) {
    if (!defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASSWORD') || !defined('DB_HOST')) {
        echo "Database configuration not defined in wp-config.php\n";
        return false;
    }

    $dbName = DB_NAME;
    $dbUser = DB_USER;
    $dbPassword = DB_PASSWORD;
    $dbHost = DB_HOST;

    $mysqli = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

    if ($mysqli->connect_error) {
        echo "Connection failed: " . $mysqli->connect_error . "\n";
        return false;
    }

    $fp = fopen($filePath, 'w');
    if (!$fp) {
        echo "Unable to open file for writing: $filePath\n";
        return false;
    }

    $tablesResult = $mysqli->query("SHOW TABLES");
    if ($tablesResult) {
        while ($tableRow = $tablesResult->fetch_row()) {
            $tableName = $tableRow[0];
            $createTableResult = $mysqli->query("SHOW CREATE TABLE `$tableName`");
            if ($createTableResult) {
                $createTableRow = $createTableResult->fetch_assoc();
                fwrite($fp, "\n" . $createTableRow['Create Table'] . ";\n\n");
            }

            $dataResult = $mysqli->query("SELECT * FROM `$tableName`");
            if ($dataResult) {
                while ($row = $dataResult->fetch_assoc()) {
                    $columns = array_map(function($col) use ($mysqli) {
                        return '`' . $mysqli->real_escape_string($col) . '`';
                    }, array_keys($row));

                    $values = array_map(function($val) use ($mysqli) {
                        return is_null($val) ? 'NULL' : "'" . $mysqli->real_escape_string($val) . "'";
                    }, array_values($row));

                    fwrite($fp, "INSERT INTO `$tableName` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");\n");
                }
            }
        }
    }

    fclose($fp);
    $mysqli->close();

    echo "Database export successful: $filePath\n";
    return true;
}

// Function to zip the wp-content directory purely in PHP
function zip_wp_content($sourceDir, $outputZip) {
    if (!extension_loaded('zip') || !file_exists($sourceDir)) {
        echo "ZIP extension not loaded or source directory does not exist.\n";
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($outputZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        echo "Unable to create zip file: $outputZip\n";
        return false;
    }

    $sourceDir = realpath($sourceDir);
    if ($sourceDir !== false) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($sourceDir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();
    echo "Zipping successful: $outputZip\n";
    return true;
}

// Function similar to 'cp' command - it copies a file from source to destination
function php_cp($source, $destination) {
    if (!file_exists($source)) {
        echo "Source file not found: $source\n";
        return false;
    }

    if (!copy($source, $destination)) {
        echo "Failed to copy $source to $destination\n";
        return false;
    }

    echo "Copy successful: $source to $destination\n";
    return true;
}

// Function similar to 'mv' command - it moves a file from source to destination
function php_mv($source, $destination) {
    if (!file_exists($source)) {
        echo "Source file not found: $source\n";
        return false;
    }

    if (!rename($source, $destination)) {
        echo "Failed to move $source to $destination\n";
        return false;
    }

    echo "Move successful: $source to $destination\n";
    return true;
}

// Function similar to 'rm' command - it removes a file
function php_rm($filePath) {
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return false;
    }

    if (!unlink($filePath)) {
        echo "Failed to remove $filePath\n";
        return false;
    }

    echo "Remove successful: $filePath\n";
    return true;
}

// Example usage:
// php_cat('/path/to/your/file.txt');
// php_ls('/path/to/your/directory');
// wp_cli_db_export('/path/to/export.sql');
// zip_wp_content('./wp-content', '/path/to/output/wp-content.zip');
// php_cp('/path/to/source.txt', '/path/to/destination.txt');
// php_mv('/path/to/source.txt', '/path/to/destination.txt');
// php_rm('/path/to/your/file.txt');
