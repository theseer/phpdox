<?php
/**
* Represents a single project that should be rendered using xsl.
*
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2011 Thomas Weinert
*
* @package XslRunner
*/

namespace Carica\Xsl\Runner;

/**
* Represents a single project that should be rendered using xsl.
*
* @package XslRunner
*/
class Directory {

  /**
  * Copy the contents of a directory into another.
  *
  * @param string $source
  * @param string $target
  */
  public function copy($source, $target) {
    $iterator = new \RecursiveIteratorIterator(
      $this->getDirectoryIterator($source),
      \RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $sourceFile) {
      if ($sourceFile->isFile()) {
        $targetFile = $target.substr((string)$sourceFile, strlen($source));
        self::force(dirname($targetFile));
        copy((string)$sourceFile, $targetFile);
      }
    }
  }

  /**
  * Remove the contents of a directory
  *
  * @param string $directory
  */
  public function remove($directory) {
    $iterator = new \RecursiveIteratorIterator(
      $this->getDirectoryIterator($directory),
      \RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $path) {
      if ($path->isDir()) {
         rmdir((string)$path);
      } else {
        unlink((string)$path);
      }
    }
  }

  /**
  * Force the existance of a directory
  *
  * @param string $path
  */
  public static function force($directory) {
    if (!(file_exists($directory) && is_dir($directory))) {
      mkdir($directory, 0755, TRUE);
    }
  }

  /**
  * Cleanup a directory string
  *
  * @param string $path
  */
  public static function cleanup($path) {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('(//+)', '/', $path);
    if (strpos($path, './') !== FALSE) {
      $result = array();
      $parts = explode('/', $path);
      foreach ($parts as $part) {
        switch ($part) {
        case '' :
        case '.' :
          break;
        case '..' :
          array_pop($result);
          break;
        default :
          $result[] = $part;
          break;
        }
      }
      $path = implode('/', $result);
    } else {
      $path = str_replace('/./', '/', $path);
    }
    if (substr($path, -1) !== '/') {
      $path .= '/';
    }
    if (substr($path, 0, 1) !== '/' &&
        !preg_match('(^[a-zA-Z]:/)', $path)) {
      $path = '/'.$path;
    }
    return $path;
  }

  /**
  * Create a recurive directory iterator for a given directory
  *
  * @param string $path
  */
  public function getDirectoryIterator($directory) {
    $directory = new \RecursiveDirectoryIterator($directory);
    return $directory;
  }
}