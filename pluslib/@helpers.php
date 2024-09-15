<?php
use pluslib\Eloquent\ModelNotFoundException;

function truncate($string, $chars = 50, $terminator = ' …')
{
  if (mb_strlen($string) < $chars) {
    return $string;
  }
  $cutPos = $chars - mb_strlen($terminator);
  $boundaryPos = mb_strrpos(mb_substr($string, 0, mb_strpos($string, ' ', $cutPos)), ' ');
  return mb_substr($string, 0, $boundaryPos === false ? $cutPos : $boundaryPos) . $terminator;
}

if (!function_exists('valueof')) {
  /**
   * Return the default value of the given value.
   *
   * @param  mixed  $fv
   * @param  mixed  ...$args
   * @return mixed
   */
  function valueof($fv, ...$data)
  {
    return $fv instanceof Closure ? $fv(...$data) : $fv;
  }
}

if (!function_exists('value')) {
  /**
   * Return the default value of the given value.
   *
   * @template TValue
   * @template TArgs
   *
   * @param  TValue|\Closure(TArgs): TValue  $value
   * @param  TArgs  ...$args
   * @return TValue
   */
  function value($value, ...$args)
  {
    return valueof($value, ...$args);
  }
}

function importJSON($file, $assoc = null, $depth = 512, $flags = 0)
{
  return json_decode(file_get_contents($file), $assoc, $depth, $flags);
}


function number_format_short($n, $precision = 1)
{
  if ($n < 900) {
    // 0 - 900
    $n_format = number_format($n, $precision);
    $suffix = '';
  } else if ($n < 900000) {
    // 0.9k-850k
    $n_format = number_format($n / 1000, $precision);
    $suffix = 'K';
  } else if ($n < 900000000) {
    // 0.9m-850m
    $n_format = number_format($n / 1000000, $precision);
    $suffix = 'M';
  } else if ($n < 900000000000) {
    // 0.9b-850b
    $n_format = number_format($n / 1000000000, $precision);
    $suffix = 'B';
  } else {
    // 0.9t+
    $n_format = number_format($n / 1000000000000, $precision);
    $suffix = 'T';
  }

  // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
  // Intentionally does not affect partials, eg "1.50" -> "1.50"
  if ($precision > 0) {
    $dotzero = '.' . str_repeat('0', $precision);
    $n_format = str_replace($dotzero, '', $n_format);
  }

  return $n_format . $suffix;
}

if (!function_exists('hl_dump')) {
  // Combined of the highlight_string and var_export
  function hl_dump()
  {
    try {
      ini_set("highlight.comment", "#008000");
      ini_set("highlight.default", "#FFFFFF");
      ini_set("highlight.html", "#808080");
      ini_set("highlight.keyword", "#0099FF; font-weight: bold");
      ini_set("highlight.string", "#99FF99");

      $vars = func_get_args();

      foreach ($vars as $var) {
        $output = var_export($var, true);
        $output = trim($output);
        $output = highlight_string("<?php " . $output, true);  // highlight_string() requires opening PHP tag or otherwise it will not colorize the text
        $output = preg_replace("|\\<code\\>|", "<code style='background-color: #000000; padding: 10px; margin: 10px; display: block; font: 12px Consolas; border-radius: 5px;'>", $output, 1);  // edit prefix
        $output = preg_replace("|(\\<span style\\=\"color\\: #[a-fA-F0-9]{0,6}\"\\>)(&lt;\\?php&nbsp;)(.*?)(\\</span\\>)|", "\$1\$3\$4", $output);  // remove custom added "<?php "
        echo $output;
      }
    } catch (Exception $e) {
      echo $e->getMessage();
    }
  }
}

if (!function_exists('dump')) {
  function dump(...$obj)
  {
    hl_dump(...$obj);
  }
}


if (!function_exists('dd')) {
  function dd(...$obj)
  {
    dump(...$obj);
    die;
  }
}

if (!function_exists('optional')) {
  /**
   * IF $obj != null: Returns the $obj (Or $closure($obj) IF $closure be instanceof Closure), 
   * else returns a object that returns null for all properties or methods!
   * @param mixed $obj
   * @param mixed $closure
   * @return mixed
   */
  function optional($obj, $closure = null)
  {
    if (is_null($obj)) {
      return new class {
        function __get($prop)
        {
          return null;
        }
        function __call($f, $p)
        {
          return null;
        }
      };
    }
    if ($closure instanceof Closure && !is_null($closure)) {
      return $closure($obj);
    }
    return $obj;
  }
}

if (!function_exists('blank')) {
  /**
   * Determine if the given value is "blank".
   *
   * @param  mixed  $value
   * @return bool
   */
  function blank($value)
  {
    if (is_null($value)) {
      return true;
    }

    if (is_string($value)) {
      return trim($value) === '';
    }

    if (is_numeric($value) || is_bool($value)) {
      return false;
    }

    if ($value instanceof Countable) {
      return count($value) === 0;
    }

    if ($value instanceof Stringable) {
      return trim((string) $value) === '';
    }

    return empty($value);
  }
}

if (!function_exists('filled')) {
  /**
   * Determines whether the given value is not "blank"
   * 
   * @param  mixed  $value
   * @return bool
   */
  function filled($value)
  {
    return !blank($value);
  }
}

if (!function_exists('literal')) {
  /**
   * Return a new literal or anonymous object using named arguments.
   *
   * @return \stdClass
   */
  function literal(...$arguments)
  {
    if (count($arguments) === 1 && array_is_list($arguments)) {
      return $arguments[0];
    }

    return (object) $arguments;
  }
}

function minify_html($buffer)
{
  $buffer = preg_replace('/\s+/', ' ', $buffer);
  $buffer = preg_replace('/<!--.*?-->/s', '', $buffer);
  return $buffer;
}

if (!function_exists('tap')) {
  function tap($value, $callback)
  {
    $callback($value);

    return $value;
  }
}

if (!function_exists('pascalcase')) {
  function pascalcase($str)
  {
    $sep = '-_';

    $str = ucwords($str, $sep);

    return str_replace(str_split($sep), '', $str);
  }
}

if (!function_exists('camelcase')) {
  function camelcase($str)
  {
    $sep = '-_';

    $str = ucwords($str, $sep);

    return lcfirst(str_replace(str_split($sep), '', $str));
  }
}

function pls_exception_handler(Throwable $throwable)
{
  if ($throwable instanceof ModelNotFoundException) {
    _404_();
  }
  $pre = "color: whitesmoke; background-color: blue; font-family: Consolas; padding: 5px; border-radius: 3px";
  $pre_ = "color: white; background-color: darkblue; font-family: Consolas; padding: 5px; border-radius: 3px";
  ?>
  <div style="color: white; background-color:black; border-radius: 5px; padding: 15px; font-family: sans-serif">
    <b style="color: red"><?= get_class($throwable) ?></b>
    <i style="background-color: blueviolet; padding: 5px; border-radius: 3px"><?= $throwable->getCode() ?></i>

    <pre style="<?= $pre ?>"><?= $throwable->getMessage() ?></pre>
    <h3>Stack Trace</h3>
    <pre style="<?= $pre_ ?>"><?= $throwable->getTraceAsString() ?></pre>
    <b style="margin:0; padding:0">At</b>
    <i style="font-weight: bold; ">
      <span style="color:yellow;"><?= $throwable->getFile() ?></span>
      Line
      <span style="color:blueviolet"><?= $throwable->getLine() ?></span>
    </i>
    <hr>
    <?= ($prev = $throwable->getPrevious()) && pls_exception_handler($prev) ?>
  </div>
  <?php
}

if (!function_exists('join_paths')) {
  function join_paths(...$paths)
  {
    return preg_replace('/\/+/', '/', join('/', $paths));
  }
}

if (!function_exists('asset')) {
  function asset($path)
  {
    return url(c_url(join_paths(app()->assets, $path)));
  }
}


if (!function_exists('loadenv')) {
  function loadenv($path = '.env', $load = true)
  {
    static $env = [];
    static $loaded = [];

    if ($load || !in_array($path, $loaded)) {
      $fpath = etc_url(c_url($path));
      $res = parse_ini_file($fpath, true);
      $env = [...$env, ...$res];
      $loaded[] = $path;
    }

    return $env;
  }
}

if (!function_exists('env')) {
  function env($variable=null, $default=null)
  {
    return data_get(loadenv(load: false), $variable, $default);
  }
}

include_once '@info.php';
include_once '@url.php';
include_once '@path.php';
include_once '@session.php';
include_once 'HTTP/@helpers.php';
include_once 'Collections/@helpers.php';
include_once '@Admin/@helpers.php';
include_once '@User/@helpers.php';
include_once 'Security/@helpers.php';
include_once '@Form/@helpers.php';
include_once 'Database/@helpers.php';
include_once 'SEO/@helpers.php';
include_once '@facades.php';