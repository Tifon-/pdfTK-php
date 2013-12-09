<?php



abstract class Pdftk
{
  /**
   * @var string $pdftk
   */
  protected $pdftkCmd = '';


  /**
   * @var array $files
   */
  private $logs = array();

  /**
   * @var bool $debug
   */
  private $debug = FALSE;

  /**
   * @param string $pdftk
   *   Linea de comando de pdftk
   * @param bool $debug
   *   Si se desea capturar los comandos ejecutados.
   */
  public function __construct($pdftk = 'pdftk', $debug = FALSE) {
    $this->debug = $debug;
    $this->pdftkCmd = $pdftk;
  }

  /**
   * Execute cmd
   */
  protected function executeCmd($cmd, $sInput = null) {
    
    $this->addLog("The command: $cmd");
    /**
     *
     */

    $aResult = array('stdout' => '', 'stderr' => '', 'return' => '');

    $aDescriptorSpec = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w')
    );

    $proc = proc_open($cmd, $aDescriptorSpec, $aPipes);

    if (!is_resource($proc)) {
        throw new Exception('Unable to open command line resource');
    }

    fwrite($aPipes[0], $sInput);
    fclose($aPipes[0]);

    $aResult['stdout'] = stream_get_contents($aPipes[1]);
    fclose($aPipes[1]);

    $aResult['stderr'] = stream_get_contents($aPipes[2]);
    fclose($aPipes[2]);

    $aResult['return'] = proc_close($proc);

    return $aResult;
    return shell_exec($cmd);
  }
  
  /**
   * Get an array with logs.
   * 
   * @return array
   */
  public function getLogs() {
    if ($this->debug) {
      return $this->logs;
    }

    return array();
  }

  /**
   * @param string $message
   *   Message to log
   * @param string $type
   */
  protected function addLog($message, $type = "success") {
    if ($this->debug) {
      $this->logs[] = array(
        'message' => $message,
        'type' => $type,
      );
    }

  }

  static function help() {
    return shell_exec("$this->pdftkCmd -h");
  }
}
