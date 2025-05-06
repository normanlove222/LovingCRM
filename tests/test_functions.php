<?php
/*
test functions file to test functions in functions.php
*/
// require __DIR__ . '/../init.php';
global $pdo;

function assertEqual($expected, $actual, $message = '')
{
    if ($expected === $actual) {
        echo "✔️ $message Passed<br>";
    } else {
        echo "❌ $message Failed: Expected '$expected', got '$actual'<br>";
    }
}

function assertNotEqual($expected, $actual, $message = '')
{
    if ($expected !== $actual) {
        echo "✔️ $message Passed (expected failure)<br>";
    } else {
        echo "❌ $message Failed: Expected not '$expected', but got it<br>";
    }
}

// Test case for getContactsCountByTag
function test_GetContactsCountByTag($pdo)
{
    // Run test with different tag IDs
    assertEqual(3, getContactsCountByTag($pdo, 244), 'getContactsCountByTag($pdo, 244)');
    assertEqual(2276, getContactsCountByTag($pdo, 263), 'getContactsCountByTag($pdo, 263)');
    assertEqual(2392, getContactsCountByTag($pdo, 262), 'getContactsCountByTag($pdo, 262)'); // equals tag
    assertNotEqual(17, getContactsCountByTag($pdo, 244), 'getContactsCountByTag($pdo, 244)'); // equal tag
    assertNotEqual(17, getContactsCountByTag($pdo, 0), 'getContactsCountByTag($pdo, 0)'); //not existing tag

    echo "Finished running test_GetContactsCountByTag.<br/><br/>";
}

function test_getTotalTags($pdo)
{
    assertEqual(38, getTotalTags($pdo), 'getTotalTags($pdo)');
    assertNotEqual("", getTotalTags($pdo), 'getTotalTags($pdo)');
    assertNotEqual(null, getTotalTags($pdo), 'getTotalTags($pdo)');
    assertNotEqual(0, getTotalTags($pdo), 'getTotalTags($pdo)');
    assertNotEqual(false, getTotalTags($pdo), 'getTotalTags($pdo)');
    echo "Finished running test_getTotalTags.<br/><br/>";
}

function test_getTotalContacts()
{
    assertEqual(33002, getTotalContacts(), 'getTotalContacts()');
    echo "Finished running test_getTotalContacts.<br/><br/>";
}

function test_checkuserexist()
{
    $user =  array();
    $user['email'] = 'steelpulsefan@gmail.com';
    assertEqual(true, checkuserexist($user), 'checkuserexist()');
    echo "Finished running test_checkuserexist <br/><br/>";
}

function test_echoCurrentDate()
{
    // Start output buffering
    ob_start();
    // Call the function that echoes the date
    echoCurrentDate();
    // Get the output and clean the buffer
    $output = ob_get_clean();
    // Get the current date
    $currentDate = date('l F j, Y');
    // Assert that the output matches the expected date
    assertEqual($currentDate, $output, 'echoCurrentDate()');

    echo "Finished running test_echoCurrentDate() <br/><br/>";
}

function test_getTags()
{
    global $pdo;
    // Call the getTags function
    $result = getTags();
    // Assert that the result is an array
    assertEqual(true, is_array($result), 'getTags() should return an array');
    // Assert that the array has 37 rows
    assertEqual(38, count($result), 'getTags() should return 38 rows');
    // Check the first row for the required keys
    if (!empty($result)) {
        $firstRow = $result[0];
        assertEqual(true, array_key_exists('name', $firstRow), "First row should have a 'name' key");
        assertEqual(true, array_key_exists('tag_id', $firstRow), "First row should have a 'tag_id' key");
        assertEqual(true, array_key_exists('count', $firstRow), "First row should have a 'count' key");
    }

    echo "Finished running test_getTags <br/><br/>";
}


function test_getContacts()
{
    global $pdo;
    // Define test parameters
    $limit = 10;
    $offset = 0;

    // Call the getContacts function
    $result = getContacts($limit, $offset);
    // Assert that the result is an array
    assertEqual(true, is_array($result), 'getContacts() should return an array');
    // Assert that the array has at most $limit rows
    assertEqual(true, count($result) <= $limit, "getContacts() should return at most $limit rows");

    // Check the first row for expected keys (assuming the contacts table has these columns)
    if (!empty($result)) {
        $firstRow = $result[0];
        assertEqual(true, array_key_exists('contact_id', $firstRow), "First row should have a 'contact_id' key");
        assertEqual(true, array_key_exists('first_name', $firstRow), "First row should have a 'first_name' key");
        assertEqual(true, array_key_exists('last_name', $firstRow), "First row should have a 'last_name' key");
        assertEqual(true, array_key_exists('email', $firstRow), "First row should have an 'email' key");
    }

    echo "Finished running test_getContacts <br/><br/>";
}

function test_buildTagsTable()
{
    // Sample data to test with
    $tags = [
        [
            'tag_id' => 1,
            'name' => 'Tag1',
            'category' => 'Category1'
        ],
        [
            'tag_id' => 2,
            'name' => 'Tag2',
            'category' => 'Category2'
        ]
    ];

    // Call the buildTagsTable function
    $html = buildTagsTable($tags);
    // Assert that the result is a string
    assertEqual(true, is_string($html), 'buildTagsTable() should return a string');
    // Check that the HTML contains the expected table structure
    assertEqual(true, strpos($html, '<div class="table-responsive">') !== false, 'HTML should contain table-responsive div');
    assertEqual(true, strpos($html, '<table class="table table-hover" id="tagsTable">') !== false, 'HTML should contain table with id tagsTable');
    assertEqual(true, strpos($html, '<thead>') !== false, 'HTML should contain thead');
    assertEqual(true, strpos($html, '<tbody>') !== false, 'HTML should contain tbody');

    // Check that the HTML contains the expected data
    foreach ($tags as $tag) {
        assertEqual(true, strpos($html, htmlspecialchars($tag['tag_id'])) !== false, "HTML should contain tag_id {$tag['tag_id']}");
        assertEqual(true, strpos($html, htmlspecialchars($tag['name'])) !== false, "HTML should contain name {$tag['name']}");
        assertEqual(true, strpos($html, htmlspecialchars($tag['category'])) !== false, "HTML should contain category {$tag['category']}");
    }

    echo "Finished running test_buildTagsTable <br/><br/>";
}

// function test_getTagsCount()
// {
//     global $pdo;

//     // Define the search term and expected count
//     $searchTerm = 'Kauai';
//     $expectedCount = 2392;

//     // Call the getTagsCount function
//     $actualCount = getTagsCount($pdo, $searchTerm);

//     // Debugging output
//     echo "Actual count for '$searchTerm': $actualCount<br>";

//     // Assert that the actual count matches the expected count
//     assertEqual($expectedCount, $actualCount, "getTagsCount(\$pdo, '$searchTerm') should return $expectedCount");

//     echo "Finished running test_getTagsCount <br/><br/>";
// }                                                                   

// Add the test_getTagsCount function to the array of test functions
$testFunctions = [
    'test_GetContactsCountByTag',
    'test_getTotalTags',
    'test_getTotalContacts',
    'test_checkuserexist',
    'test_echoCurrentDate',
    'test_getTags',
    'test_getContacts',
    'test_buildTagsTable',
    // 'test_getTagsCount' // Add this line
];


$testCounter = 0;

//displays each test and counter for each
foreach ($testFunctions as $testFunction) {
    $testCounter++;
    echo "Testing Functions #: $testCounter<br>";
    $testFunction($pdo);
}

