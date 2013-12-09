<?php


class PdftkFile
{
  /**
   * @var string $password
   */
  private $password = '';

  /**
   * @var string $filePath
   */
  private $absolutePath = '';

  public function __construct($filePath) {

    $directory = dirname($filePath);
    $basename = self::getBasename($filePath);
    $absolutePath = realpath($directory) . DIRECTORY_SEPARATOR .  $basename;



    if ((file_exists($absolutePath))) {
      $this->absolutePath = $absolutePath;
    }
    // If the file does not exists but does the wildcards.
    else {
      // In Pdftk can use wildcards.
      $wildcards = 0;
      if (function_exists('glob')) {
        $pattern = glob($filePath);
        if ($wildcards = count($pattern)) {
          $this->absolutePath = $absolutePath;
        }
      }
      else {
        throw new Exception("File $absolutePath does not exists");
      }
    }



  }

  public function getAbsolutePath() {
    return $this->absolutePath;
  }

  public function getPassword() {
    return $this->password;
  }

  public function setPassword($password) {
    $this->password = $password;
  }

  /**
   * Helper function
   */
  static public function getBasename($path) {
    $separators = '/';
    if (DIRECTORY_SEPARATOR != '/') {
      // For Windows OS add special separator.
      $separators .= DIRECTORY_SEPARATOR;
    }

    $uri = rtrim($path, $separators);
    // Returns the trailing part of the $path starting after one of the directory
    // separators.
    return preg_match('@[^' . preg_quote($separators, '@') . ']+$@', $path, $matches) ? $matches[0] : '';

  }

  static public function createNewPath($filename, $directory) {
    $pos = strrpos($filename, '.');

    if ($pos !== FALSE) {
      $name = substr($filename, 0, $pos);
      $ext = substr($filename, $pos);
    }
    else {
      $name = $filename;
      $ext = '';
    }

    $counter = 0;
    do {
      $new_path = $directory . DIRECTORY_SEPARATOR .  $name . '_' . $counter++ . $ext;
    } while (file_exists($new_path));

    return $new_path;
  }
}
