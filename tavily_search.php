<?php
header('Content-Type: application/json');

$tavily_api_key = 'tvly-';

$data = json_decode(file_get_contents('php://input'), true);
$query = $data['query'] ?? '';


if (empty($query)) {
    echo json_encode(['error' => 'No query provided']);
    exit;
}

$url = 'https://api.tavily.com/search';
$params = [
    'api_key' => $tavily_api_key,
    'query' => $query,
    'search_depth' => 'advanced',
    'include_images' => false,
    'max_results' => 5,
    'sort_by' => 'relevance'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($params)
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo json_encode(['error' => 'Failed to get results from Tavily API']);
} else {
    $tavily_results = json_decode($result, true);
    $formatted_results = [];
    
    foreach ($tavily_results['results'] as $book) {
        $formatted_book = [
            'title' => $book['title'],
            'url' => $book['url'],
            'description' => $book['description'],
            'summary' => generateSummary($book['description']),
            'recommendation_reason' => generateRecommendationReason($book['description'], $query)
        ];
        $formatted_results[] = $formatted_book;
    }
    
    echo json_encode(['results' => $formatted_results]);
}

function generateSummary($description) {
    // 実際のプロジェクトではより洗練された要約アルゴリズムを使用することをお勧めします
    return substr($description, 0, 150) . '...';
}

function generateRecommendationReason($description, $query) {
    // この関数も、より高度なNLP技術を使用して改善できます
    $keywords = explode(' ', $query);
    $reason = "この本は";
    foreach ($keywords as $keyword) {
        if (stripos($description, $keyword) !== false) {
            $reason .= $keyword . "に関する洞察を提供し、";
        }
    }
    $reason .= "あなたの課題解決に役立つ可能性があります。";
    return $reason;
}