<?php

use PhpParser\Node;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

require_once __DIR__ . '/../init.php';

$data = [];

$php_functions = get_defined_functions()['internal'];

class FunctionCallVisitor extends NodeVisitorAbstract
{
  public function enterNode(Node $node)
  {
    global $data;

    if ($node instanceof Node\Expr\FuncCall) {
      if ($node->name instanceof Node\Name) {
        if (array_key_exists($node->name->toString(), $data)) {
          $data[$node->name->toString()]++;
        } else {
          $data[$node->name->toString()] = 1;
        }
      }
    }
  }
}

$parser = (new ParserFactory())->createForNewestSupportedVersion();
$traverser = new NodeTraverser;
$visitor = new FunctionCallVisitor();

try {
  $code = file_get_contents(__DIR__ . '/test.php');

  $statements = $parser->parse($code);
  $traverser->addVisitor($visitor);
  $traverser->traverse($statements);

  dump($data);
} catch (Exception $error) {
  dump($error->getMessage());
}
