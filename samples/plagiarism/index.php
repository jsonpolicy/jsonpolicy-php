<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

require dirname(__DIR__) . '/../vendor/autoload.php';
require __DIR__ . '/article.php';

use JsonPolicy\Manager as PolicyManager;

$manager = PolicyManager::bootstrap([
    'policies' => [
        file_get_contents(__DIR__  . '/policy.json')
    ],
    'custom_conditions' => [
        'Similar' => function($group, $operator, $manager) {
            $result = null;

            foreach ($group as $cnd) {
                $sub_result = null;

                foreach($cnd['right'] as $value) {
                    $sim = 0;
                    similar_text($cnd['left'], $value, $sim);

                    $sub_result = $manager->compute($sub_result, ($sim > 60), 'OR');
                }

                $result = $manager->compute($result, $sub_result, $operator);
            }

            return $result;
        }
    ]
]);

$article = new Article([
    'content' => 'This is my content'
]);

// Change existing or add a new element to match at least 60% of the content in the
// article defined above and see the how condition is evaluated below
$articles = [
    'Nope and how is one',
    'Lipsume blah blah'
];

// By default allow access to article if not explicitly denied. That is why we
// pass the third argument as `true`
if ($manager->isAllowedTo($article, 'publish', true, ['articles' => $articles])) {
    echo 'Yes. Your article is actually original';
} else {
    echo 'Ooops. Looks like your article is quite similar to some other article';
}