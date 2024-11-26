<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('database.php');

$id = @$_GET['id'];
$post_answerIds = @$_POST['answers-id'];


if ($id && $post_answerIds) {

    $answerIds = explode(',', $post_answerIds);
    $image = getImage($id, $answerIds);
    if (!$image) {
        // redirect to home... TODO
    }
} else {
    $image = getNewImage();
    $answerIds = implode(",", array_keys($image['answers']));
}




print '<pre>';
print_r(@$_POST);
print '</pre>';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>What's That Pic!</title>
    <link rel="stylesheet" href="https://unpkg.com/mvp.css">
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
                            <label for="radio<?= $id; ?>"><?= $word; ?></label>
                        <?php endforeach; ?>
                    </li>
                </ul>
                <input type="hidden" name="answer-ids" id="answer-ids" value="<?= $answerIds; ?>" />
                <input type="submit" name="submit" id="submit" value="Oké">
            </form>
        </section>
    </main>
</body>

</html>