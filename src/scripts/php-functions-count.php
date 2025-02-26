<?php

use PhpParser\Node;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Symfony\Component\Finder\Finder;

require_once __DIR__ . '/../init.php';

ini_set('memory_limit', '8000M');

$data = [];

$php_functions = get_defined_functions()['internal'];

class FunctionCallVisitor extends NodeVisitorAbstract
{
  public array $functions = [];

  public function enterNode(Node $node)
  {
    if ($node instanceof Node\Expr\FuncCall) {
      if ($node->name instanceof Node\Name) {
        $this->functions[$node->name->toString()] = ($this->functions[$node->name->toString()] ?? 0) + 1;
      }
    }

    return null;
  }
}

function find_php_files(string $directory): array
{
  $finder = new Finder();
  $finder->files()->name('*.php')->in($directory);

  $files = [];
  foreach ($finder as $file) {
    $files[] = $file->getRealPath();
  }

  return $files;
}

$files = find_php_files(TH_REPOS_PATH);

$parser = (new ParserFactory())->createForNewestSupportedVersion();
$traverser = new NodeTraverser;

foreach ($files as $file) {
  try {
    $code = file_get_contents($file);
    $statements = $parser->parse($code);

    $visitor = new FunctionCallVisitor();
    $traverser->addVisitor($visitor);
    $traverser->traverse($statements);
    $traverser->removeVisitor($visitor);

    foreach ($visitor->functions as $name => $count) {
      $data[$name] = ($data[$name] ?? 0) + $count;
    }
  } catch (Exception $error) {
    dump($error->getMessage());
  }
}

$result = array_intersect_key($data, array_flip($php_functions));

arsort($result);

dump($result);
