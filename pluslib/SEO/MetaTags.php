<?php
namespace pluslib\SEO;

use Exception;

const METATAGS_FORMAT_title = '<title>%2$s</title>';
const METATAGS_FORMAT_mn = '<meta name="%s" content="%s"/>';
const METATAGS_FORMAT_charset = '<meta charset="%2$s"/>';
const METATAGS_FORMAT_viewport = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

const METATAGS = [
  'charset' => [
    'format' => METATAGS_FORMAT_charset,
    'default' => 'UTF-8'
  ],
  'viewport' => [
    'set' => false,
    'format' => METATAGS_FORMAT_viewport,
  ],
  'keywords' => [],
  'title' => [
    'format' => METATAGS_FORMAT_title,
  ],
  'subject' => [],
  'copyright' => [],
  'language' => [
    'default' => 'EN'
  ],
  'robots' => [],
  'revised' => [],
  'abstract' => [],
  'topic' => [],
  'summary' => [],
  'classification' => [
    'rname' => 'Classification'
  ],
  'author' => [],
  'designer' => [],
  'reply_to' => ['rname' => 'reply-to']
];

function getMetaTagDefault($name)
{
  return METATAGS[$name]['default'] ?? '';
}

function getMetaTagFormat($name)
{
  return METATAGS[$name]['format'] ?? METATAGS_FORMAT_mn;
}

function getMetaTagRName($name)
{
  return METATAGS[$name]['rname'] ?? $name;
}

function metaTagsExist($name)
{
  return array_key_exists($name, METATAGS);
}

function metaTagsSettable($name)
{
  return METATAGS[$name]['set'] ?? true;
}

/**
 * @method MetaTags charset($charset = 'UTF-8')
 * @method MetaTags title($title)
 * @method MetaTags keywords($keywords)
 * @method MetaTags subject($subject)
 * @method MetaTags copyright($company)
 * @method MetaTags language($language='EN')
 * @method MetaTags robots($robots)
 * @method MetaTags revised($revised)
 * @method MetaTags abstract($abstract)
 * @method MetaTags topic($topic)
 * @method MetaTags summary($summary)
 * @method MetaTags classification($classification)
 * @method MetaTags author($author)
 * @method MetaTags designer($designer)
 * @method MetaTags reply_to($reply_to)
 */
class MetaTags
{
  //? Properties

  //! Get/Set/Invoke
  private array $mts_values;
  public function __get($name)
  {
    $name = strtolower($name);
    if (metaTagsExist($name)) {
      return $this->mts_values[$name] ?? getMetaTagDefault($name);
    }
    throw new Exception('Undefined Metatag "' . $name . '"');
  }
  public function __set($name, $value)
  {
    $name = strtolower($name);
    if (metaTagsExist($name) and metaTagsSettable($name)) {
      $this->mts_values[$name] = $value;
      return;
    }
    throw new Exception('Undefined Metatag "' . $name . '"');
  }
  public function __call($name, $args)
  {
    $name = strtolower($name);
    if (metaTagsExist($name)) {
      $this->{$name} = $args[0] ?? getMetaTagDefault($name);
      return $this;
    }
    throw new Exception('Undefined Metatag "' . $name . '"');
  }

  //! Constructor
  public function __construct()
  {
  }

  //? Generate
  private function gh_mn($prop, $name = null)
  {
    $name ??= $prop;
    $val = $this->{$prop};
    ?>
    <?php if ($val): ?>
      <?= sprintf(getMetaTagFormat($name), getMetaTagRName($name), $val) ?>
    <?php endif ?>
  <?php
  }
  public function generateHere()
  {
    ?>
    <?php foreach (METATAGS as $metaTag => $_) {
      $this->gh_mn($metaTag);
    } ?>
  <?php
  }
}