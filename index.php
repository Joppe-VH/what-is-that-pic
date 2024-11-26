<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('database.php');

$id = @$_GET['id'];
$answerIds = @$_POST['answer-ids'];
$answer = @$_POST['radio'];
$submit = @$_POST['submit'];

if ($id) {
    if (
        strtolower($submit) == 'volgende'
        || !isset($answerIds)
    ) {
        header("Location: index.php");
    }
    $answerArray = explode(',', $answerIds);
    $image = getImage($id, $answerArray);
    if (!$image) {
        header("Location: index.php");
    }
} else {
    $image = getNewImage();
    $answerIds = implode(",", array_keys($image['answers']));
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>What's That Pic!</title>
    <link rel="stylesheet" href="https://unpkg.com/mvp.css">
    <style>
        .correct {
            color: green;
        }

        .not-correct {
            color: red;
        }
    </style>
</head>

<body>
    <main>
        <section>
            <form action="index.php?id=<?= $image['id']; ?>" method="POST">
                <header>
                    <div>
                        <img src="<?= $image['image_link']; ?>" alt="">
                    </div>
                </header>
                <ul>
                    <li>
                        <?php foreach ($image['answers'] as $id => $word) : ?>
                            <input type="radio" name="radio" id="radio<?= $id; ?>" value="<?= $id; ?>" />
                            <label
                                <?php if (isset($answer) && $answer == $id): ?>
                                class="<?= $id == 0 ? 'correct' : 'not-correct' ?>"
                                <?php endif; ?>
                                for="radio<?= $id; ?>"><?= $word; ?></label>
                        <?php endforeach; ?>
                    </li>
                </ul>
                <input type="hidden" name="answer-ids" id="answer-ids" value="<?= $answerIds; ?>" />
                <input type="submit" name="submit" id="submit" value="<?= isset($answer) && $answer == 0 ? 'Volgende' : 'Oké' ?>">
            </form>
        </section>
    </main>
</body>

</html>