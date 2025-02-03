<?php

use OpenAI\Client;
use OpenAI\Factory;
use LumeSocial\Services\AI\ContentGenerator;
use LumeSocial\Services\AI\ContentReviewer;
use LumeSocial\Models\AiSettings;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize OpenAI client
$client = (new Factory())
    ->withApiKey(getenv('OPENAI_API_KEY'))
    ->withOrganization(getenv('OPENAI_ORG_ID'))
    ->make();

// Initialize AI settings
$settings = new AiSettings([
    'model' => 'gpt-3.5-turbo',
    'image_count' => 3,
    'temperature' => 0.7,
    'max_tokens' => 500,
]);

// Initialize services
$contentGenerator = new ContentGenerator($client, $settings);
$contentReviewer = new ContentReviewer($client, $settings);

// Test content generation
try {
    $result = $contentGenerator->generate(
        "Write a tweet about artificial intelligence in healthcare"
    );
    echo "Generated Content:\n";
    echo $result['content'] . "\n\n";
} catch (Exception $e) {
    echo "Error generating content: " . $e->getMessage() . "\n";
}

// Test content variations
try {
    $variations = $contentGenerator->generateVariations(
        "Write a tweet about artificial intelligence in healthcare",
        3
    );
    echo "Content Variations:\n";
    foreach ($variations as $index => $variation) {
        echo ($index + 1) . ". " . $variation . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error generating variations: " . $e->getMessage() . "\n";
}

// Test content review
try {
    $content = "AI is revolutionizing healthcare with faster diagnoses and personalized treatments! #AI #Healthcare #Innovation";
    $review = $contentReviewer->review($content);
    echo "Content Review:\n";
    echo json_encode($review, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error reviewing content: " . $e->getMessage() . "\n";
} 