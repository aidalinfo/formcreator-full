<?php
/**
 * Security Test Suite for FormCreator URL Pre-fill Feature
 * 
 * This script tests the security fixes implemented to prevent XSS attacks
 * through URL parameter pre-filling functionality.
 * 
 * Usage: php test_security_xss.php
 */

require_once __DIR__ . '/../../../inc/includes.php';

echo "FormCreator Security Test Suite - XSS Prevention\n";
echo "===============================================\n\n";

/**
 * Test cases for XSS attack vectors
 */
$xssTestCases = [
    // Basic script injection
    [
        'name' => 'Basic Script Injection',
        'input' => '<script>alert("XSS")</script>',
        'expected_blocked' => true,
        'description' => 'Should block basic script tags'
    ],
    
    // Event handler injection
    [
        'name' => 'Event Handler Injection',
        'input' => '" onmouseover="alert(\'XSS\')"',
        'expected_blocked' => true,
        'description' => 'Should block HTML event handlers'
    ],
    
    // JavaScript URL
    [
        'name' => 'JavaScript URL',
        'input' => 'javascript:alert("XSS")',
        'expected_blocked' => true,
        'description' => 'Should block javascript: URLs'
    ],
    
    // HTML tag injection
    [
        'name' => 'HTML Tag Injection',
        'input' => '<img src="x" onerror="alert(\'XSS\')">',
        'expected_blocked' => true,
        'description' => 'Should block malicious HTML tags'
    ],
    
    // Valid normal input
    [
        'name' => 'Valid Normal Input',
        'input' => 'John Doe',
        'expected_blocked' => false,
        'description' => 'Should allow normal text input'
    ],
    
    // Valid email
    [
        'name' => 'Valid Email Input',
        'input' => 'user@example.com',
        'expected_blocked' => false,
        'description' => 'Should allow valid email addresses'
    ],
    
    // Long input test
    [
        'name' => 'Long Input Test',
        'input' => str_repeat('A', 15000), // Longer than 10000 limit
        'expected_blocked' => false, // Should be truncated, not blocked
        'description' => 'Should truncate very long inputs'
    ]
];

/**
 * Test field name validation
 */
$fieldNameTests = [
    [
        'name' => 'Valid Field Name',
        'input' => 'EmployeeName',
        'expected_blocked' => false
    ],
    [
        'name' => 'Field Name with Special Chars',
        'input' => 'Employee<script>Name',
        'expected_blocked' => true
    ],
    [
        'name' => 'Field Name Too Long',
        'input' => str_repeat('A', 300),
        'expected_blocked' => true
    ]
];

/**
 * Simulate the URL parameter processing from formdisplay.php
 */
function testUrlParameterProcessing($fieldName, $fieldValue) {
    $urlValues = [];
    
    // Simulate the validation logic from formdisplay.php
    if (preg_match('/^[a-zA-Z0-9_\-\s]{1,255}$/', $fieldName)) {
        if (is_string($fieldValue)) {
            $value = strip_tags($fieldValue);
            $value = trim($value);
            
            if (strlen($value) > 10000) {
                $value = substr($value, 0, 10000);
            }
            
            if (preg_match('/(javascript:|on\w+\s*=|<script|<\/script)/i', $value)) {
                return ['blocked' => true, 'reason' => 'Malicious pattern detected'];
            }
            
            $urlValues[$fieldName] = $value;
            return ['blocked' => false, 'processed_value' => $value];
        }
    } else {
        return ['blocked' => true, 'reason' => 'Invalid field name'];
    }
    
    return ['blocked' => true, 'reason' => 'Processing failed'];
}

/**
 * Test the validateAndSanitizeFieldValue method logic
 */
function testFieldValueSanitization($value, $fieldType = 'text') {
    // Simulate the validation logic from setInitialAnswers()
    if (is_array($value)) {
        return ['type' => 'array', 'processed' => true];
    }
    
    if (!is_string($value)) {
        return ['type' => 'invalid', 'processed' => false];
    }
    
    // Type-specific validation
    switch ($fieldType) {
        case 'email':
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return ['type' => 'email', 'processed' => false, 'reason' => 'Invalid email'];
            }
            break;
            
        case 'integer':
            if (!ctype_digit(str_replace(['-', '+'], '', $value))) {
                return ['type' => 'integer', 'processed' => false, 'reason' => 'Invalid integer'];
            }
            break;
            
        case 'float':
            if (!is_numeric($value)) {
                return ['type' => 'float', 'processed' => false, 'reason' => 'Invalid float'];
            }
            break;
    }
    
    // Final sanitization simulation
    $sanitized = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return ['type' => $fieldType, 'processed' => true, 'sanitized_value' => $sanitized];
}

// Run URL parameter tests
echo "1. URL Parameter Processing Tests\n";
echo "---------------------------------\n";
foreach ($xssTestCases as $test) {
    $result = testUrlParameterProcessing('TestField', $test['input']);
    $status = ($result['blocked'] === $test['expected_blocked']) ? 'PASS' : 'FAIL';
    
    echo sprintf("%-25s [%s] %s\n", 
        $test['name'], 
        $status, 
        $test['description']
    );
    
    if ($status === 'FAIL') {
        echo "  Expected blocked: " . ($test['expected_blocked'] ? 'true' : 'false') . "\n";
        echo "  Actually blocked: " . ($result['blocked'] ? 'true' : 'false') . "\n";
        if (isset($result['reason'])) {
            echo "  Reason: " . $result['reason'] . "\n";
        }
    }
    echo "\n";
}

// Run field name validation tests
echo "2. Field Name Validation Tests\n";
echo "------------------------------\n";
foreach ($fieldNameTests as $test) {
    $result = testUrlParameterProcessing($test['input'], 'test_value');
    $status = ($result['blocked'] === $test['expected_blocked']) ? 'PASS' : 'FAIL';
    
    echo sprintf("%-30s [%s]\n", $test['name'], $status);
    
    if ($status === 'FAIL') {
        echo "  Expected blocked: " . ($test['expected_blocked'] ? 'true' : 'false') . "\n";
        echo "  Actually blocked: " . ($result['blocked'] ? 'true' : 'false') . "\n";
    }
    echo "\n";
}

// Run field type validation tests
echo "3. Field Type Validation Tests\n";
echo "------------------------------\n";
$typeTests = [
    ['value' => 'user@example.com', 'type' => 'email', 'should_pass' => true],
    ['value' => 'invalid-email', 'type' => 'email', 'should_pass' => false],
    ['value' => '123', 'type' => 'integer', 'should_pass' => true],
    ['value' => 'abc', 'type' => 'integer', 'should_pass' => false],
    ['value' => '12.34', 'type' => 'float', 'should_pass' => true],
    ['value' => 'not_a_number', 'type' => 'float', 'should_pass' => false],
];

foreach ($typeTests as $test) {
    $result = testFieldValueSanitization($test['value'], $test['type']);
    $passed = $result['processed'] === $test['should_pass'];
    $status = $passed ? 'PASS' : 'FAIL';
    
    echo sprintf("%-20s %-10s [%s]\n", 
        $test['type'] . ': ' . $test['value'], 
        $test['should_pass'] ? 'valid' : 'invalid',
        $status
    );
    
    if (!$passed && isset($result['reason'])) {
        echo "  Reason: " . $result['reason'] . "\n";
    }
    echo "\n";
}

echo "4. HTML Escaping Tests\n";
echo "----------------------\n";

// Test HTML escaping
$htmlTests = [
    '<script>alert("test")</script>',
    '"><script>alert("test")</script>',
    '<img src="x" onerror="alert(1)">',
    'Normal text content'
];

foreach ($htmlTests as $html) {
    $escaped = htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $is_safe = ($escaped !== $html);
    echo sprintf("Input:    %s\n", $html);
    echo sprintf("Escaped:  %s\n", $escaped);
    echo sprintf("Status:   %s\n", $is_safe ? 'ESCAPED' : 'NO_CHANGE');
    echo "\n";
}

echo "Security Test Suite Complete\n";
echo "============================\n";
echo "Review the results above to ensure all security measures are working correctly.\n";
echo "Any FAIL status indicates a potential security vulnerability that needs attention.\n";