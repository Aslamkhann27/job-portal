<?php

require_once __DIR__ . '/auth.php';

function cc_skill_tests_file_path(): string
{
    return __DIR__ . '/../data/skill_tests.json';
}

function cc_load_skill_test_attempts(): array
{
    return cc_read_json_file(cc_skill_tests_file_path());
}

function cc_save_skill_test_attempts(array $attempts): bool
{
    return cc_write_json_file(cc_skill_tests_file_path(), $attempts);
}

function cc_question_bank(): array
{
    return [
        'Development' => [
            [
                'question' => 'Which HTML tag is used to include JavaScript code?',
                'options' => ['<script>', '<js>', '<javascript>', '<code>'],
                'answer' => 0
            ],
            [
                'question' => 'Which HTTP method is usually used to submit form data securely?',
                'options' => ['GET', 'POST', 'PUT', 'TRACE'],
                'answer' => 1
            ],
            [
                'question' => 'What does CSS stand for?',
                'options' => ['Computer Style Sheets', 'Cascading Style Sheets', 'Creative Style Syntax', 'Colorful Style Sheets'],
                'answer' => 1
            ],
            [
                'question' => 'Which PHP function checks if an email is valid?',
                'options' => ['is_email()', 'validate_email()', 'filter_var()', 'email_check()'],
                'answer' => 2
            ],
            [
                'question' => 'In Bootstrap, which class creates a responsive grid container?',
                'options' => ['.grid', '.container', '.row-fluid', '.layout'],
                'answer' => 1
            ]
        ],
        'Design' => [
            [
                'question' => 'Which principle focuses on making content easy to scan?',
                'options' => ['Animation', 'Visual hierarchy', 'Skeuomorphism', 'Parallax'],
                'answer' => 1
            ],
            [
                'question' => 'What is a wireframe used for?',
                'options' => ['Final branding', 'Code compilation', 'Layout planning', 'SEO auditing'],
                'answer' => 2
            ],
            [
                'question' => 'Which color contrast is best for readability?',
                'options' => ['Low contrast', 'Medium contrast', 'High contrast', 'Random contrast'],
                'answer' => 2
            ],
            [
                'question' => 'Responsive design ensures UI works well on:',
                'options' => ['Only desktops', 'Only tablets', 'Different screen sizes', 'Only high-end devices'],
                'answer' => 2
            ],
            [
                'question' => 'Which tool is commonly used for UI prototypes?',
                'options' => ['Figma', 'MySQL', 'Git Bash', 'Apache'],
                'answer' => 0
            ]
        ],
        'Marketing' => [
            [
                'question' => 'CTR in digital marketing stands for:',
                'options' => ['Click Through Rate', 'Cost To Rank', 'Campaign Trend Report', 'Content Tracking Ratio'],
                'answer' => 0
            ],
            [
                'question' => 'Which platform is commonly used for search ads?',
                'options' => ['Google Ads', 'Docker', 'GitHub Actions', 'Postman'],
                'answer' => 0
            ],
            [
                'question' => 'A/B testing is mainly used to:',
                'options' => ['Compare versions', 'Backup content', 'Write CSS', 'Host files'],
                'answer' => 0
            ],
            [
                'question' => 'Organic traffic comes from:',
                'options' => ['Paid campaigns only', 'Unpaid search and content reach', 'Server logs', 'Email attachments'],
                'answer' => 1
            ],
            [
                'question' => 'Conversion rate measures:',
                'options' => ['Page load speed', 'Users completing desired action', 'Number of employees', 'Logo quality'],
                'answer' => 1
            ]
        ],
        'Analytics' => [
            [
                'question' => 'Which SQL clause is used to filter rows?',
                'options' => ['ORDER BY', 'GROUP BY', 'WHERE', 'JOIN'],
                'answer' => 2
            ],
            [
                'question' => 'A dashboard is mainly used to:',
                'options' => ['Store passwords', 'Visualize key metrics', 'Compile code', 'Send emails'],
                'answer' => 1
            ],
            [
                'question' => 'Which metric helps evaluate hiring funnel drop-off?',
                'options' => ['Bounce sequence by stage', 'Font size index', 'DNS lookup time', 'Battery level'],
                'answer' => 0
            ],
            [
                'question' => 'Which format is most common for data exchange?',
                'options' => ['JPEG', 'JSON', 'MP3', 'SVGZ'],
                'answer' => 1
            ],
            [
                'question' => 'Data cleaning usually happens before:',
                'options' => ['Analysis and reporting', 'Turning on monitor', 'Domain registration', 'Buying ads'],
                'answer' => 0
            ]
        ]
    ];
}

function cc_get_questions_for_job(array $job): array
{
    $bank = cc_question_bank();
    $category = $job['category'] ?? '';

    if (isset($bank[$category])) {
        return $bank[$category];
    }

    return $bank['Development'];
}

function cc_evaluate_skill_test(array $questions, array $answers): array
{
    $correct = 0;

    foreach ($questions as $index => $question) {
        $selected = isset($answers[$index]) ? (int)$answers[$index] : -1;
        if ($selected === (int)$question['answer']) {
            $correct++;
        }
    }

    $total = count($questions);
    $score = $total > 0 ? (int)round(($correct / $total) * 100) : 0;

    return [
        'total' => $total,
        'correct' => $correct,
        'score' => $score,
        'status' => $score >= 60 ? 'Pass' : 'Needs Improvement'
    ];
}

function cc_save_skill_test_attempt(array $attempt): bool
{
    $attempts = cc_load_skill_test_attempts();
    $attempts[] = $attempt;
    return cc_save_skill_test_attempts($attempts);
}

function cc_get_latest_skill_test_result(string $userId, int $jobId): ?array
{
    $attempts = cc_load_skill_test_attempts();

    for ($i = count($attempts) - 1; $i >= 0; $i--) {
        $attempt = $attempts[$i];
        if (($attempt['user_id'] ?? '') === $userId && (int)($attempt['job_id'] ?? 0) === $jobId) {
            return $attempt;
        }
    }

    return null;
}
