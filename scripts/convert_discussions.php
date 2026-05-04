<?php
/**
 * Convert discussion PHP view files to interactive quiz JSON topic files.
 * Run from project root: php scripts/convert_discussions.php
 */

$views_dir = __DIR__ . '/../application/views/discussions/';
$json_dir  = __DIR__ . '/../assets/json/';

// ── Topic groups: slug → [title, description, sections[file_slug → section_title]] ──
$topic_groups = [
    'db_management' => [
        'title'       => 'Database Management',
        'description' => 'Introduction to databases, DBMS, RDBMS, and normalization concepts.',
        'sections'    => [
            '105a_data'          => 'Data, Information & Metadata',
            '105b_trad_vs_dbms'  => 'Traditional File System vs DBMS',
            '105c_rdbms'         => 'Relational Database Management System',
            '105d_normalization' => 'Database Normalization',
            '105e_introtosql'    => 'Introduction to SQL',
        ],
    ],
    'sql_statements' => [
        'title'       => 'SQL Statements',
        'description' => 'Core SQL statements for querying and manipulating database records.',
        'sections'    => [
            '105_column_guide' => 'Column Data Types Guide',
            '105_constraints'  => 'SQL Constraints',
            '105_select'       => 'SELECT Statement',
            '105_insert'       => 'INSERT Statement',
            '105_update'       => 'UPDATE Statement',
            '105_delete'       => 'DELETE Statement',
            '105_joins'        => 'SQL JOINs',
        ],
    ],
    'mysql_setup' => [
        'title'       => 'MySQL Setup & Configuration',
        'description' => 'Connecting PHP to MySQL databases and understanding storage engines.',
        'sections'    => [
            '105_connect_db'     => 'Connecting PHP to a Database',
            '105_storage_engine' => 'MySQL Storage Engines',
        ],
    ],
    'bi_fundamentals' => [
        'title'       => 'Business Intelligence Fundamentals',
        'description' => 'Core BI concepts: data, analytics, reporting, and decision support.',
        'sections'    => [
            'BI_intro'       => 'Introduction to Business Intelligence',
            'BI_framework'   => 'BI Framework',
            'BI_data'        => 'BI Data Concepts',
            'BI_OTLPvsOLAP'  => 'OLTP vs OLAP',
            'BI_analytics'   => 'BI Analytics',
            'BI_reporting'   => 'BI Reporting',
            'BI_reporting-1' => 'BI Reporting (Part 2)',
            'BI_decision'    => 'Decision Support Systems',
        ],
    ],
    'bi_machine_learning' => [
        'title'       => 'Machine Learning for BI',
        'description' => 'Predictive analytics, supervised learning, and classification algorithms.',
        'sections'    => [
            'BI_intro_predictive'    => 'Introduction to Predictive Analytics',
            'BI_normalization'       => 'Data Normalization for ML',
            'BI_imputation'          => 'Data Imputation',
            'BI_feature_selection'   => 'Feature Selection',
            'BI_knn'                 => 'K-Nearest Neighbors (KNN)',
            'BI_logistic_regression' => 'Logistic Regression',
            'BI_tree'                => 'Decision Trees',
        ],
    ],
    'bi_nlp' => [
        'title'       => 'Natural Language Processing',
        'description' => 'NLP fundamentals, text mining techniques, and practical examples.',
        'sections'    => [
            'BI_NLP'               => 'Introduction to NLP',
            'BI_textmining'        => 'Text Mining',
            'BI_textminingexample' => 'Text Mining Examples',
        ],
    ],
    'css_basics' => [
        'title'       => 'CSS Fundamentals',
        'description' => 'Styling web pages with CSS — syntax, properties, layout, and best practices.',
        'sections'    => [
            'css_intro'      => 'Introduction to CSS',
            'css_howto'      => 'How to Add CSS',
            'css_syntax'     => 'CSS Syntax',
            'css_properties' => 'CSS Properties',
            'css_units'      => 'CSS Units',
            'css_bg_color'   => 'Background & Colors',
            'css_display'    => 'CSS Display Property',
            'css_cascade'    => 'CSS Cascading',
            'css_roadmap'    => 'CSS Learning Roadmap',
            'css_error'      => 'CSS Errors & Debugging',
            'css_bootstrap'  => 'CSS with Bootstrap',
        ],
    ],
    'bootstrap_basics' => [
        'title'       => 'Bootstrap Essentials',
        'description' => 'Building responsive, mobile-first UIs with Bootstrap 4.',
        'sections'    => [
            'bootstrap_forms' => 'Bootstrap Forms',
            'bootstrap_table' => 'Bootstrap Tables',
        ],
    ],
    'c_data_structures' => [
        'title'       => 'Data Structures in C',
        'description' => 'Structs, linked lists, queues, and dynamic memory management in C.',
        'sections'    => [
            'structs'            => 'C Structures (structs)',
            'structs2'           => 'Advanced Structs & Typedef',
            'linked_list'        => 'Singly Linked Lists',
            'linked_list_memory' => 'Linked Lists in Memory',
            'memory_allocate'    => 'Dynamic Memory Allocation',
            'queue_intro'        => 'Queues',
        ],
    ],
    'js_fundamentals' => [
        'title'       => 'JavaScript Fundamentals',
        'description' => 'Introduction to JavaScript for interactive web development.',
        'sections'    => [
            'js_intro'    => 'Introduction to JavaScript',
            'php_includes' => 'PHP Includes & Requires',
        ],
    ],
];

// ── Helpers ──────────────────────────────────────────────────────────

function php_to_html(string $raw): string
{
    // 1. Replace base_url asset references with {ASSETS} placeholder
    $raw = preg_replace(
        '/<\?(?:php\s+echo|=)\s*base_url\s*\(\s*[\'"]assets\/([^\'"]+)[\'"]\s*\)\s*;?\s*\?>/',
        '{ASSETS}assets/$1',
        $raw
    );

    // 2. Replace base_url('discussion') / base_url('') with #
    $raw = preg_replace(
        '/<\?(?:php\s+echo|=)\s*base_url\s*\(\s*[\'"][^\'\"]*[\'"]\s*\)\s*;?\s*\?>/',
        '#',
        $raw
    );

    // 3. Handle htmlspecialchars('...') — extract and escape content
    $raw = preg_replace_callback(
        '/<\?=\s*htmlspecialchars\s*\(\s*\'((?:[^\'\\\\]|\\\\.)*?)\'(?:\s*,\s*[^)]+)?\s*\)\s*\?>/s',
        function ($m) { return htmlspecialchars(stripcslashes($m[1])); },
        $raw
    );
    $raw = preg_replace_callback(
        '/<\?=\s*htmlspecialchars\s*\(\s*"((?:[^"\\\\]|\\\\.)*?)"(?:\s*,\s*[^)]+)?\s*\)\s*\?>/s',
        function ($m) { return htmlspecialchars(stripcslashes($m[1])); },
        $raw
    );

    // 4. Remove all remaining PHP tags (load->view calls, etc.)
    $raw = preg_replace('/<\?(?:php)?.*?\?>/s', '', $raw);

    // 5. Remove highlight.js <link> and <script> tags
    $raw = preg_replace('/<link[^>]+(?:highlight|highlights)[^>]*>\s*/i', '', $raw);
    $raw = preg_replace('/<script[^>]+(?:highlight|highlights)[^>]*>.*?<\/script>\s*/is', '', $raw);

    // 6. Remove inline hljs.highlightAll() script blocks
    $raw = preg_replace('/<script[^>]*>\s*(?:document\.addEventListener[^;]+;\s*)?hljs\.highlightAll\(\)\s*;?\s*<\/script>\s*/is', '', $raw);

    return $raw;
}

function extract_lesson(string $html): string
{
    $dom = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);

    // ── Pattern B: CI card-body view ──
    $nodes = $xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," card-body ")]');
    if ($nodes->length > 0) {
        $node = $nodes->item(0);
        // Remove "Back to Topics" button wrapper
        $back = $xpath->query(
            './/div[contains(@class,"mt-4")][.//a[contains(@href,"#") or contains(text(),"Back")]]',
            $node
        );
        foreach ($back as $el) {
            $el->parentNode->removeChild($el);
        }
        return trim(inner_html($node, $dom));
    }

    // ── Pattern A: <main class="...content..."> ──
    $nodes = $xpath->query('//main[contains(@class,"content")]');
    if ($nodes->length > 0) {
        return trim(inner_html($nodes->item(0), $dom));
    }

    // ── Pattern A: <div class="content ..."> (standalone HTML) ──
    $nodes = $xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," content ")]');
    if ($nodes->length > 0) {
        return trim(inner_html($nodes->item(0), $dom));
    }

    // ── Fallback: body minus <header> ──
    $bodies = $xpath->query('//body');
    if ($bodies->length > 0) {
        $body = $bodies->item(0);
        foreach ($xpath->query('.//header', $body) as $h) {
            $h->parentNode->removeChild($h);
        }
        return trim(inner_html($body, $dom));
    }

    return trim($html);
}

function inner_html(DOMNode $node, DOMDocument $dom): string
{
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $dom->saveHTML($child);
    }
    // DOMDocument URL-encodes { } in attribute values; restore the placeholder
    return str_replace('%7BASSETS%7D', '{ASSETS}', $html);
}

function load_section(string $filepath, string $title): array
{
    if (!file_exists($filepath)) {
        fwrite(STDERR, "  SKIP (not found): $filepath\n");
        return [];
    }
    $raw     = file_get_contents($filepath);
    $html    = php_to_html($raw);
    $lesson  = extract_lesson($html);
    if (empty(trim(strip_tags($lesson)))) {
        fwrite(STDERR, "  SKIP (empty content): $filepath\n");
        return [];
    }
    return [
        'title'     => $title,
        'lesson'    => $lesson,
        'questions' => [],
    ];
}

// ── Main conversion loop ─────────────────────────────────────────────

foreach ($topic_groups as $slug => $group) {
    echo "Building: $slug\n";

    $sections = [];
    foreach ($group['sections'] as $file_slug => $section_title) {
        $filepath = $views_dir . $file_slug . '.php';
        echo "  + $file_slug\n";
        $sec = load_section($filepath, $section_title);
        if ($sec) {
            $sections[] = $sec;
        }
    }

    if (empty($sections)) {
        echo "  ! No sections found — skipping.\n";
        continue;
    }

    $topic_data = [
        'topic'       => $slug,
        'title'       => $group['title'],
        'description' => $group['description'],
        'sections'    => $sections,
    ];

    $dest   = $json_dir . $slug . '.json';
    $pretty = json_encode($topic_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    file_put_contents($dest, $pretty);
    echo "  → Written: $dest (" . round(strlen($pretty) / 1024, 1) . " KB)\n";
}

echo "\nDone.\n";
