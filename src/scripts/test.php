<?php

$a = [1, 2, 3];

count($a);
count($a);

function_exists("abc") or die("abc");
die(123);

function myFunction1()
{
  echo "Function 1";
  strlen("test");
}

function myFunction2()
{
  echo "Function 2";
  myFunction1();
  array_map(function ($val) {
    return $val * 2;
  }, [1, 2, 3]);
}

myFunction1();
myFunction2();
myFunction1();
strlen("test");
implode(",", [1, 2, 3]);
