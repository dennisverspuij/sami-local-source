<?php
namespace DennisVerspuij\SamiLocalSource;

abstract class Base extends \Sami\RemoteRepository\AbstractRemoteRepository
{
  /**
   * @var string sprintf format for query hash to jump a given line number %1$d
   */
  const LINEIDFORMAT = '';

  protected $sami;
  protected $relOutPath;
  protected $excludeRE = FALSE;
  protected $filesystem;
  protected $twigExt;
  private   $initialized = FALSE;
  protected $generated = array();


  /**
   * @param \Sami\Sami $sami
   * @param string $localPath   Base path of original source code (project root == base of $sami['files'])
   * @param array  $excludeDirs Do not include source code for these paths relative to $localPath
   * @param string $relOutPath  Source code will be generated in this path relative to each version's build dir
   */
  public function __construct(\Sami\Sami $sami, $localPath, $excludeDirs = array(), $relOutPath = '_src')
  {
    parent::__construct('', $localPath);
    $this->sami = $sami;
    $this->relOutPath = $relOutPath;
    if (!empty($excludeDirs))
      $this->excludeRE = sprintf(
        '~^%1$s(?:%2$s)%1$s~',
        preg_quote(\DIRECTORY_SEPARATOR, '~'),
        implode('|', array_map(static function($s) { return preg_quote(trim($s, \DIRECTORY_SEPARATOR), '~'); }, $excludeDirs))
      );

    $this->filesystem = new \Symfony\Component\Filesystem\Filesystem();
    $this->twigExt = $sami['twig']->getExtension('Sami\Renderer\TwigExtension');
  }

  final public function getFileUrl($projectVersion, $relativePath, $line)
  {
    if ($this->excludeRE && preg_match($this->excludeRE, $relativePath))
      return '';

    if (!$this->initialized)
    {
      $this->initialized = TRUE;
      $this->initialize(dirname($this->sami['project']->getBuildDir()));
    }

    $relDestPath = $relativePath . '.html';
    if (!isset($this->generated[$id = $projectVersion->getName() . "\0" . $relativePath]))
    {
      $this->generated[$id] = TRUE;
      $destPath = $this->sami['project']->getBuildDir() . \DIRECTORY_SEPARATOR . $this->relOutPath . $relDestPath;
      $this->filesystem->mkdir(dirname($destPath));
      file_put_contents($destPath, $this->get_html(
        $this->localPath . $relativePath,
        str_repeat('../', 1 + substr_count($this->relOutPath . $relDestPath, \DIRECTORY_SEPARATOR))
      ));
    }

    return $this->twigExt->pathForStaticFile([], $this->relOutPath . $relDestPath) .
           ($line !== NULL ? '#' . sprintf(static::LINEIDFORMAT, $line) : '');
  }

  /**
   * Override this you need to prepare anything globally for all builds, e.g. store shared CSS/JS.
   *
   * @param string $buildRoot Root directory of all builds (thus parent of version subdirs)
   */
  protected function initialize($buildRoot)
  {
  } 

  /**
   * Render HTML with the source code.
   *
   * @param string $realPath Path name of the source file to render HTML for
   * @param string $relBuildRootUrl Relative URL to the root of all builds, for referencing shared CSS/JS
   * @return string utf-8 encoded HTML exposing the source code
   */
  abstract protected function get_html($realPath, $relVersionRootUrl);
}
