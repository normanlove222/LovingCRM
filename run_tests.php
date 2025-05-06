<?php
require_once 'init.php';
include 'header.php';
include 'menu.php';
// run_tests.php

echo "Running tests...<br/>";
include __DIR__ . '/tests/test_functions.php';
// Add more include statements for additional test files

echo "All tests completed.<br/>";