<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
//use app\assets\AppAsset;
use app\assets\AdminLteAsset;

//AppAsset::register($this);
$asset      = AdminLteAsset::register($this);
$baseUrl    = $asset->baseUrl;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.3.1/css/buttons.bootstrap.min.css">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<?php $this->beginBody() ?>

<div class="wrapper">
    <?= $this->render('header.php', ['baserUrl' => $baseUrl, 'title'=>Yii::$app->name]) ?>
    <?= $this->render('leftside.php', ['baserUrl' => $baseUrl]) ?>
    <?= $this->render('content.php', ['content' => $content]) ?>
    <?= $this->render('footer.php', ['baserUrl' => $baseUrl]) ?>
</div>

<!--footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?//= date('Y') ?></p>

        <p class="pull-right"><?//= Yii::powered() ?></p>
    </div>
</footer-->

<?php $this->endBody() ?>
<script type="application/javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="application/javascript" src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
<script type="application/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script>
<script type="application/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.bootstrap.min.js"></script>
<script type="application/javascript" src="//cdn.datatables.net/buttons/1.3.1/js/buttons.print.min.js"></script>
</body>
</html>
<?php $this->endPage() ?>
