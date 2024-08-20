<?php

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/");
    exit;
}

include '../config.php';
$query = new Query();

$userId = $_SESSION['user_id'];
$numWords = (int) ($_GET['num_words'] ?? 10);
$filter = $_GET['filter'] ?? 'all';

$results = [];

if ($filter === 'liked') {
    $likedWords = $query->search('liked_words', 'word_id', 'WHERE user_id = ?', [$userId], 'i');
    $likedWordIds = array_column($likedWords, 'word_id');

    if (!empty($likedWordIds)) {
        $placeholders = implode(',', array_fill(0, count($likedWordIds), '?'));
        $types = str_repeat('i', count($likedWordIds) + 2);
        $params = array_merge($likedWordIds, [$userId, $numWords]);
        $results = $query->select(
            'words',
            'id, word, translation',
            "WHERE id IN ($placeholders) AND user_id = ? ORDER BY RAND() LIMIT ?",
            $params,
            $types
        );
    }
} else {
    $results = $query->select(
        'words',
        'id, word, translation',
        'WHERE user_id = ? ORDER BY RAND() LIMIT ?',
        [$userId, $numWords],
        'ii'
    );
}

function getRandomOptions($correctWord, $allWords, $numOptions = 4)
{
    $options = [$correctWord];
    $allWordsCount = count($allWords);

    if ($allWordsCount > 1) {
        foreach ($allWords as $randomWord) {
            if ($randomWord !== $correctWord && !in_array($randomWord, $options)) {
                $options[] = $randomWord;
            }
            if (count($options) >= $numOptions) {
                break;
            }
        }
    }

    shuffle($options);
    return $options;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon.ico">
    <title>Vocabulary Test</title>
    <link rel="stylesheet" href="../css/exercise-vocabulary.css">
</head>



<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h1>Vocabulary Test</h1>

        <form id="numWordsForm" action="" method="GET" style="display: flex; align-items: center; gap: 10px;">
            <div>
                <label for="num_words" style="font-size: 16px; color: #333;">Tests:</label>
                <select name="num_words" id="num_words"
                    style="padding: 5px; border-radius: 4px; border: 1px solid #ddd; font-size: 14px;">
                    <option value="10" <?= $numWords == 10 ? 'selected' : '' ?>>10</option>
                    <option value="15" <?= $numWords == 15 ? 'selected' : '' ?>>15</option>
                    <option value="20" <?= $numWords == 20 ? 'selected' : '' ?>>20</option>
                    <option value="30" <?= $numWords == 30 ? 'selected' : '' ?>>30</option>
                    <option value="50" <?= $numWords == 50 ? 'selected' : '' ?>>50</option>
                </select>
            </div>

            <div>
                <label for="filter" style="font-size: 16px; color: #333;">Filter:</label>
                <select name="filter" id="filter"
                    style="padding: 5px; border-radius: 4px; border: 1px solid #ddd; font-size: 14px;">
                    <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All</option>
                    <option value="liked" <?= $filter == 'liked' ? 'selected' : '' ?>>Liked</option>
                </select>
            </div>
        </form>

        <?php if (empty($results)): ?>
            <div class="information-not-found">
                <i class="fa fa-info-circle"></i>
                <p>No words found.</p>
                <a href="../dictionary/" class="btn btn-primary">Add Words</a>
            </div>
        <?php else: ?>
            <form action="test_result.php" method="POST">
                <?php foreach ($results as $index => $word): ?>
                    <div class="question">
                        <p><?= ($index + 1) . ") " . htmlspecialchars($word['translation']) ?></p>
                        <?php
                        $allWords = array_column($results, 'word');
                        $options = getRandomOptions($word['word'], $allWords);
                        ?>
                        <?php foreach ($options as $optionIndex => $option): ?>
                            <label>
                                <input type="radio" name="answers[<?= $index ?>]" value="<?= htmlspecialchars($option) ?>">
                                <?= htmlspecialchars($option) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <input type="hidden" name="total_questions" value="<?= count($results) ?>">
                <input type="hidden" name="words_data" value="<?= htmlspecialchars(json_encode($results)) ?>">
                <button type="submit">Submit Test</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('num_words').addEventListener('change', function () {
            document.getElementById('numWordsForm').submit();
        });

        document.getElementById('filter').addEventListener('change', function () {
            document.getElementById('numWordsForm').submit();
        });
    </script>

    <script>
        window.onload = function () {
            clearRadioButtons();
        };
        function clearRadioButtons() {
            const radioButtons = document.querySelectorAll('input[type="radio"]');
            radioButtons.forEach(radio => {
                radio.checked = false;
            });
        }
    </script>

</body>

</html>