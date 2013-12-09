<?php

require_once('Pdftk.php');
require_once('PdftkFile.php');



class PdftkCat extends Pdftk
{
  /**
   * @var array $files
   */
  private $files = array();
  
  /**
   * @var array $filesPassword
   */
  private $filesPassword = array();
  
  /**
   * @var array $filesPages
   */
  private $filesPages = array();
  
  /**
   * @var int $handle
   */
  private $handle = 65;
  
  /**
   * @param PdftkFile $pdfFile
   * 
   * @param array $pages
   */
  public function addPdf(PdftkFile $pdfFile, Array $pages = array()) {
    $handle = chr($this->handle);

    $absolutePath = $pdfFile->getAbsolutePath();
    $password = $pdfFile->getPassword();

    $this->files[$handle] = "$handle=\"$absolutePath\"";
    if ($password != '') {
      $this->filesPasswords[$handle] = "$handle=\"$password\"";
    }

    // pages options
    if (!empty($pages)) {
      foreach ($pages as $page) {
        $this->filesPages[$handle][] = "$handle$page";
      }
    }
    else {
      $this->filesPages[$handle][] = "$handle";
    }

    $this->handle++;
  }

  /**
   * @param string $pathOutput
   *
   * @param bool $replace
   *
   * @return mixed
   */
  public function cat($pathOutput, $replace = FALSE) {
    // Se requiere mas de un archivo para concatenar
    if (count($this->files) > 0) {
      $directory = dirname($pathOutput);
      // Check if the directory is not writable
      if (!is_writable($directory)) {
        $this->addLog("The directory '$directory' is not writable.", "error");

        return FALSE;
      }

      $filemtime = 0;
      if (file_exists($pathOutput)) {
        $filemtime = filemtime($pathOutput);
      }
      if (!$replace && $filemtime) {
        $basename = PdftkFile::getBasename($pathOutput);
        $pathOutput = PdftkFile::createNewPath($basename, $directory);
      }

      // Get the basename again in case was change.
      $basename = PdftkFile::getBasename($pathOutput);
      $absoluteOutput = realpath($directory) . DIRECTORY_SEPARATOR .  $basename;

      $files = implode(' ', $this->files);
      $handles = array_keys($this->files);
      $cmd = "$this->pdftkCmd $files ";
      if (!empty($this->filesPasswords)) {
        $passwords = implode(' ', $this->filesPasswords);
        $cmd .= "input_pw $passwords ";
      }

      $cat = array();
      foreach ($this->filesPages as $h) {
        $cat[] = implode(' ', $h);
      }

      $final_cat = implode(' ', $cat);
      $cmd .= "cat $final_cat output \"$absoluteOutput\"";


      // Execute the command
      $this->executeCmd($cmd);


      if (realpath($absoluteOutput) && ($changed = filemtime($absoluteOutput)) && $changed > $filemtime) {
        $this->addLog("The file $absoluteOutput was created. This file was dated $filemtime and now has this date $changed");

        return array(
          'path' => $pathOutput,
          'absolute' => $absoluteOutput,
        );
      }

      $this->addLog("The file $absoluteOutput was not created.", "error");
    }
    else {
      $this->addLog("Need files to merge", "error");
    }

    return FALSE;
  }
}
