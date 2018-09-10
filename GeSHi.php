<?php
namespace DennisVerspuij\SamiLocalSource;

class GeSHi extends Base
{
  const LINEIDFORMAT = 'L-%1$d';

  /**
   * @var string global CSS
   */
  public $css = <<<'EOT'
html, body { margin: 0; padding: 0; font-family: Arial; }
h1         { height: 1.25em; margin: 0; border-bottom: 1px solid black; padding: 0.5em 1em; font-size: 0.8em; white-space: nowrap; background-color: #ddd;  }
pre        { margin: 4px 0; font-family: Consolas, monospace; }
div.error  { border: 1px solid black; padding: 8px; background-color: red; color: white }
EOT;


  protected function initialize($buildRoot)
  {
    file_put_contents($buildRoot . DIRECTORY_SEPARATOR . 'geshi.css', $this->css);
  }

  protected function get_html($realPath, $relVersionRootUrl)
  {
    ob_start();
    $geshi = new \GeSHi();
    $geshi->set_encoding('UTF-8');
    $geshi->load_from_file($realPath);
    $geshi->enable_strict_mode(\GESHI_MAYBE);
    $geshi->enable_line_numbers(\GESHI_NORMAL_LINE_NUMBERS);
    $geshi->enable_classes();
    $geshi->set_line_style('color: #aaa', TRUE);
    $geshi->set_link_styles(\GESHI_LINK, 'text-decoration: none');
    //$geshi->set_link_styles(\GESHI_HOVER, 'text-decoration: underline');
    $geshi->set_overall_id('L');
    $geshi->enable_ids(TRUE);

    $css = $geshi->get_stylesheet();
    $code = $geshi->parse_code();
    $error = $geshi->error();
?>
<html>
  <head>
    <title><?php echo htmlspecialchars(sprintf('%s (%s/)', basename($realPath), dirname($realPath))) ?></title>
    <style type="text/css">
<!--
<?php echo $css ?>
-->
    </style>
    <link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($relVersionRootUrl) ?>geshi.css" />
  </head>
  <body>
    <h1><?php echo htmlspecialchars($realPath) ?></h1>
<?php if ($error !== FALSE): ?>
    <div class="error"><?php echo preg_replace('~^(<br\s*/>)+~', '', (string) $error) ?></div>
<?php endif ?>
    <?php echo $code ?>

  </body>
</html>
<?php
    return ob_get_clean();
  }
}
