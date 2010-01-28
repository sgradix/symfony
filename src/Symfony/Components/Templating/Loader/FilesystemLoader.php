<?php

namespace Symfony\Components\Templating\Loader;

use Symfony\Components\Templating\Storage\Storage;
use Symfony\Components\Templating\Storage\FileStorage;

/*
 * This file is part of the symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * FilesystemLoader is a loader that read templates from the filesystem.
 *
 * @package    symfony
 * @subpackage templating
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class FilesystemLoader extends Loader
{
  protected $templatePathPatterns;

  /**
   * Constructor.
   *
   * @param array $templatePathPatterns An array of path patterns to look for templates
   */
  public function __construct($templatePathPatterns)
  {
    if (!is_array($templatePathPatterns))
    {
      $templatePathPatterns = array($templatePathPatterns);
    }

    $this->templatePathPatterns = $templatePathPatterns;

    parent::__construct();
  }

  /**
   * Loads a template.
   *
   * @param string $template The logical template name
   * @param array  $options  An array of options
   *
   * @return Storage|Boolean false if the template cannot be loaded, a Storage instance otherwise
   */
  public function load($template, array $options = array())
  {
    if (self::isAbsolutePath($template) && file_exists($template))
    {
      return new FileStorage($template);
    }

    $options = $this->mergeDefaultOptions($options);
    $options['name'] = $template;

    $replacements = array();
    foreach ($options as $key => $value)
    {
      $replacements['%'.$key.'%'] = $value;
    }

    foreach ($this->templatePathPatterns as $templatePathPattern)
    {
      if (is_file($file = strtr($templatePathPattern, $replacements)))
      {
        if ($this->debugger)
        {
          $this->debugger->log(sprintf('Loaded template file "%s" (renderer: %s)', $file, $options['renderer']));
        }

        return new FileStorage($file);
      }

      if ($this->debugger)
      {
        $this->debugger->log(sprintf('Failed loading template file "%s" (renderer: %s)', $file, $options['renderer']));
      }
    }

    return false;
  }

  /**
   * Returns true if the file is an existing absolute path.
   *
   * @param string $file A path
   *
   * @return true if the path exists and is absolute, false otherwise
   */
  static protected function isAbsolutePath($file)
  {
    if ($file[0] == '/' || $file[0] == '\\' ||
        (strlen($file) > 3 && ctype_alpha($file[0]) &&
         $file[1] == ':' &&
         ($file[2] == '\\' || $file[2] == '/')
        )
       )
    {
      return true;
    }

    return false;
  }
}
