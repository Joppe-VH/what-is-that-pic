<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

print '<pre>';
print_r($_GET);
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
            <form action="index.php?id=1" method="POST">
                <header>
                    <div>
                        <img src="https://static.arasaac.org/pictograms/2349/2349_500.png" alt="">
                    </div>
                </header>
                <button type="submit">Woord1</button>
                <button type="submit">Woord2</button>
                <button type="submit">Woord3</button>
                <button type="submit">Woord4</button>
            </form>
        </section>
    </main>
</body>

</html>