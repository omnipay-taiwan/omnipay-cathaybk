<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>國泰世華</h1>
    <?php if ($response->isSuccessful()) { ?>
        <div class="alert alert-success">Congratulations, your request was successful!</div>
    <?php } elseif ($response->isRedirect()) { ?>
        <div class="alert alert-info">Your request requires <?= $response->getRedirectMethod() ?>
            redirect to an off-site payment page.
        </div>

        <?php if ($response->getRedirectMethod() == 'GET') { ?>
            <p><a href="<?= $response->getRedirectUrl() ?>" class="btn btn-success">Redirect Now</a></p>
        <?php } elseif ($response->getRedirectMethod() == 'POST') { ?>
            <form method="POST" action="<?= $response->getRedirectUrl() ?>">
                <p>
                    <?php foreach ($response->getRedirectData() as $key => $value) { ?>
                        <input type="hidden" name="<?= $key ?>" value="<?= $value ?>"/>
                    <?php } ?>

                    <button class="btn btn-success">Redirect Now</button>
                </p>
            </form>
        <?php } ?>
    <?php } else { ?>
        <div class="alert alert-error">Sorry, your request failed.</div>
    <?php } ?>

    <p>The response object had the following to say:</p>

    <p><b>$response->isSuccessful()</b></p>
    <pre><?= $response->isSuccessful() ? 'true' : 'false' ?></pre>

    <p><b>$response->isRedirect()</b></p>
    <pre><?= $response->isRedirect() ? 'true' : 'false' ?></pre>

    <?php if (method_exists($response, 'getRedirectUrl')) { ?>
        <p><b>$response->getRedirectUrl()</b></p>
        <pre><?= $response->getRedirectUrl() ?></pre>
    <?php } ?>

    <?php if (method_exists($response, 'getRedirectMethod')) { ?>
        <p><b>$response->getRedirectMethod()</b></p>
        <pre><?= $response->getRedirectMethod() ?></pre>
    <?php } ?>

    <?php if (method_exists($response, 'getRedirectData')) { ?>
        <p><b>$response->getRedirectData()</b></p>
        <pre><?php var_dump($response->getRedirectData()) ?></pre>
    <?php } ?>

    <p><b>$response->getMessage()</b></p>
    <pre><?= $response->getMessage() ?></pre>

    <p><b>$response->getCode()</b></p>
    <pre><?= $response->getCode() ?></pre>

    <p><b>$response->getTransactionReference()</b></p>
    <pre><?= $response->getTransactionReference() ?></pre>

    <?php if (method_exists($response, 'getCardReference')) { ?>
        <p><b>$response->getCardReference()</b></p>
        <pre><?php var_dump($response->getCardReference()) ?></pre>
    <?php } ?>

    <p><b>$response->getData()</b></p>
    <pre><?php var_dump($response->getData()) ?></pre>
</div>
</body>
</html>

<?php

    //if ($response->isRedirect()) {
//    $response->redirect();
    //}
