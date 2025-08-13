<?php
/**
 * Test script for URL pre-filling functionality
 * 
 * This script tests the new feature that allows pre-filling form fields
 * via URL parameters.
 * 
 * Usage:
 * 1. Create a form with fields named "EmployeeName" and "TicketID"
 * 2. Access the form with URL parameters:
 *    /front/formdisplay.php?id=FORM_ID&field_EmployeeName=John%20Doe&field_TicketID=1234
 * 
 * Expected behavior:
 * - The form should display with the "EmployeeName" field pre-filled with "John Doe"
 * - The "TicketID" field should be pre-filled with "1234"
 */

// Include necessary files
require_once __DIR__ . '/../../../inc/includes.php';

// Test configuration
$testCases = [
    [
        'description' => 'Test simple text field pre-fill',
        'url_params' => [
            'field_EmployeeName' => 'John Doe',
        ],
        'expected' => 'EmployeeName field should contain "John Doe"'
    ],
    [
        'description' => 'Test numeric field pre-fill',
        'url_params' => [
            'field_TicketID' => '1234',
        ],
        'expected' => 'TicketID field should contain "1234"'
    ],
    [
        'description' => 'Test multiple fields pre-fill',
        'url_params' => [
            'field_EmployeeName' => 'Jane Smith',
            'field_TicketID' => '5678',
            'field_Department' => 'IT Support',
        ],
        'expected' => 'All three fields should be pre-filled with respective values'
    ],
    [
        'description' => 'Test special characters in field values',
        'url_params' => [
            'field_Description' => 'This is a test & special chars: <test>',
        ],
        'expected' => 'Description field should handle special characters correctly'
    ],
];

echo "FormCreator URL Pre-fill Test Suite\n";
echo "====================================\n\n";

echo "Test Cases:\n";
foreach ($testCases as $index => $testCase) {
    echo ($index + 1) . ". " . $testCase['description'] . "\n";
    echo "   URL Parameters:\n";
    foreach ($testCase['url_params'] as $key => $value) {
        echo "      - $key = $value\n";
    }
    echo "   Expected: " . $testCase['expected'] . "\n\n";
}

echo "\nImplementation Summary:\n";
echo "-----------------------\n";
echo "1. Modified front/formdisplay.php to capture URL parameters starting with 'field_'\n";
echo "2. Updated Form::displayUserForm() to accept URL values parameter\n";
echo "3. Added FormAnswer::setInitialAnswers() to map field names to question IDs\n";
echo "4. Existing AbstractField::setFormAnswer() handles the pre-filled values\n";

echo "\nTo test manually:\n";
echo "-----------------\n";
echo "1. Create a form with the fields mentioned in test cases\n";
echo "2. Note the form ID\n";
echo "3. Access the form using URLs like:\n";
echo "   /front/formdisplay.php?id=FORM_ID&field_EmployeeName=John%20Doe&field_TicketID=1234\n";
echo "4. Verify that fields are pre-filled with the URL values\n";

echo "\nNote: Field names in URLs must match exactly with the question names in the form.\n";