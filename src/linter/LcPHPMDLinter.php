<?php

/**
 * Wrapper for phpmd
 */
final class LcPHPMDLinter extends ArcanistExternalLinter {

  private $xmlFileName = "./phpmd.xml";

  public function getInfoName() {
    return 'PHP_MD';
  }

  public function getInfoURI() {
    return 'https://phpmd.org/';
  }

  public function getInfoDescription() {
    return pht(
      'PHPMD Lints PHP Files');
  }

  public function getLinterName() {
    return 'LCPHPMDLINTER';
  }

  public function getLinterConfigurationName() {
    return 'lcphpmdlinter';
  }

  public function getInstallInstructions() {
    return pht('Install phpmd with composer');
  }

  public function getLinterConfigurationOptions() {
    $options = array(
      'phpmd.xml_file_name' => array(
        'type' => 'optional string',
        'help' => pht('The path to the phpmd.xml file with ruleset configurations.'),
      ),
    );

    return $options + parent::getLinterConfigurationOptions();
  }

  public function setLinterConfigurationValue($key, $value) {
    switch ($key) {
      case 'phpmd.xml_file_name':
        $this->xmlFileName = $value;
        return;

      default:
        return parent::setLinterConfigurationValue($key, $value);
    }
  }

  protected function buildFutures(array $paths) {
    $executable = $this->getExecutableCommand();

   

    $futures = array();
    foreach ($paths as $path) {
      $disk_path = $this->getEngine()->getFilePathOnDisk($path);
      $path_argument = $this->getPathArgumentForLinterFuture($disk_path);

      $bin = csprintf('%C %C %Ls', $executable, $path_argument, $this->getCommandFlags());

      $future = new ExecFuture('%C', $bin);

      $future->setCWD($this->getProjectRoot());
      $futures[$path] = $future;
    }

    return $futures;
  }


  protected function getMandatoryFlags() {
    $options = array('xml');

    $options[] = $this->xmlFileName;

    return $options;
  }

  public function getDefaultBinary() {
    return './vendor/bin/phpmd';
  }

  public function getVersion() {
    list($stdout) = execx('%C --version', $this->getExecutableCommand());

    $matches = array();
    $regex = '/^PHP_CodeSniffer version (?P<version>\d+\.\d+\.\d+)\b/';
    if (preg_match($regex, $stdout, $matches)) {
      return $matches['version'];
    } else {
      return false;
    }
  }

/* 
  <pmd version="@package_version@" timestamp="2020-09-29T16:14:40+00:00">
  <file name="/var/www/sites/lucore/app/Console/Commands/ExportsValidateSettings.php">
    <violation beginline="89" endline="89" rule="UnusedLocalVariable" ruleset="Unused Code Rules" externalInfoUrl="https://phpmd.org/rules/unusedcode.html#unusedlocalvariable" priority="3">
      Avoid unused local variables such as '$resultId'.
    </violation>
  </file>
  <file name="/var/www/sites/lucore/app/Console/Commands/HoverRunTests.php">
    <violation beginline="149" endline="149" rule="UnusedLocalVariable" ruleset="Unused Code Rules" externalInfoUrl="https://phpmd.org/rules/unusedcode.html#unusedlocalvariable" priority="3">
      Avoid unused local variables such as '$runner'.
    </violation>
  </file> */

  protected function parseLinterOutput($path, $err, $stdout, $stderr) {

    if (!$err) {
      return array();
    }

    $report_dom = new DOMDocument();
    $ok = @$report_dom->loadXML($stdout);
    if (!$ok) {
      return false;
    }

    $files = $report_dom->getElementsByTagName('file');
    $messages = array();
    foreach ($files as $file) {
      foreach ($file->childNodes as $child) {
        if (!($child instanceof DOMElement)) {
          continue;
        }

        if ($child->tagName == 'violation') {
          $prefix = 'E';
        } else {
          $prefix = 'W';
        }

        $source = $child->getAttribute('rule').".".$child->getAttribute('ruleset');
        $code = 'PHPMD.'.$prefix;

        $message = id(new ArcanistLintMessage())
          ->setPath($path)
          ->setName($source)
          ->setLine($child->getAttribute('beginline'))
          ->setCode($code)
          ->setDescription($child->nodeValue)
          ->setSeverity($this->getLintMessageSeverity($code));

        $messages[] = $message;
      }
    }

    return $messages;
  }

  protected function getDefaultMessageSeverity($code) {
    if (preg_match('/^PHPMD\\.W\\./', $code)) {
      return ArcanistLintSeverity::SEVERITY_WARNING;
    } else {
      return ArcanistLintSeverity::SEVERITY_ERROR;
    }
  }

  protected function getLintCodeFromLinterConfigurationKey($code) {
    if (!preg_match('/^PHPMD\\.(E|W)\\./', $code)) {
      throw new Exception(
        pht(
          "Invalid severity code '%s', should begin with '%s.'.",
          $code,
          'PHPCS'));
    }
    return $code;
  }

}