<?php

require "db_funcs.php";

function getNewImage()
{
    static $oldImageId;

    $row = sql_fetch(
        'SELECT * FROM images 
        WHERE id != :oldId 
        ORDER BY RAND() 
        LIMIT 1;',
        [
            ':oldId' => $oldImageId ?? 0
        ]
    );

    $answers = getRandomWords(3, $row['image_answer']);
    $answers['0'] = $row['image_answer'];
    shuffle_assoc($answers);
    $row['answers'] = $answers;

    $oldImageId = $row['id'];
    return $row;
}

function getImage(int $id, array $answerIds)
{
    $row = fetch('images', ['id' => $id]);
    if (!$row) return;

    $row['answers'] = getWords($answerIds, $row['image_answer']);

    return $row;
}

function getRandomWords(int $count = 3, string $forbiddenWord = "_")
{
    return sql_fetchAll(
        "SELECT * FROM answers
         Where answer NOT LIKE :forbbidenWord
         ORDER BY RAND()
         LIMIT :count",
        [
            ":forbbidenWord" => $forbiddenWord,
            ":count" => $count
        ],
        PDO::FETCH_KEY_PAIR
    );
}

function getWords(array $wordIds, string $default = 'placeholder')
{

    // $ids = implode(' OR id = ', $wordIds);
    // $fetchedWords = sql_fetchAll(
    //     'SELECT * FROM answers
    //     WHERE id = :ids',
    //     [':ids' => $ids],
    //     PDO::FETCH_KEY_PAIR
    // );
    $fetchedWords = sql_fetchAll(
        'SELECT * FROM answers',
        null,
        PDO::FETCH_KEY_PAIR
    );
    $words = [];
    foreach ($wordIds as $id) {
        $words[$id] = $fetchedWords[$id] ?? $default;
    }
    return $words;
}


function shuffle_assoc(&$array)
{
    $keys = array_keys($array);

    shuffle($keys);

    foreach ($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;

    return true;
}
