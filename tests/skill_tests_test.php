<?php
require_once __DIR__ . '/../includes/skill_tests.php';

function assert_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

$custom = [
    ['question' => 'Custom question 1', 'options' => ['A', 'B', 'C', 'D'], 'answer' => 2],
    ['question' => 'Custom question 2', 'options' => ['A', 'B', 'C', 'D'], 'answer' => 1],
    ['question' => 'Custom question 3', 'options' => ['A', 'B', 'C', 'D'], 'answer' => 0],
    ['question' => 'Custom question 4', 'options' => ['A', 'B', 'C', 'D'], 'answer' => 3],
    ['question' => 'Custom question 5', 'options' => ['A', 'B', 'C', 'D'], 'answer' => 2]
];

$selected = cc_get_questions_for_job(['category' => 'Development', 'assessment_questions' => $custom]);
assert_same($custom, $selected, 'A valid recruiter assessment should override the category question bank.');

$defaults = cc_get_questions_for_job(['category' => 'Design']);
assert_same(5, count($defaults), 'Jobs without custom assessments should retain the five-question category test.');

$result = cc_evaluate_skill_test($custom, [0 => 2, 1 => 1, 2 => 0, 3 => 3, 4 => 2]);
assert_same(100, $result['score'], 'All correct answers should produce a 100% score.');
assert_same('Pass', $result['status'], 'A passing score should be marked Pass.');

$result = cc_evaluate_skill_test($custom, [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0]);
assert_same(20, $result['score'], 'Incorrect answers should be scored accurately.');
assert_same('Needs Improvement', $result['status'], 'A score below 60% should not pass.');

echo "Skill assessment tests passed." . PHP_EOL;
