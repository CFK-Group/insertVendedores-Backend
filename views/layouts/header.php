<?php
use yii\helpers\Html;
?>
<header class="main-header">

    <!-- Logo -->
    <a href="<?= Yii::getAlias('@web').'/admins'?>" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini" style="font-size: 14px;"><b><?=$this->context->module->name?></b></span>
        <!-- logo for regular state and mobile devic1es -->
        <span class="logo-lg"><b><?=$this->context->module->id?></b> Administrator</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <?= Html::img('@web/img/user-icon2.png', ['class' => 'user-image', 'alt'=>'User Image']) ?>
                        <span class="hidden-xs"><?= Yii::$app->user->identity->nombre ." ". Yii::$app->user->identity->apellido?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <?= Html::img('@web/img/user-icon2.png', ['class' => 'img-circle', 'alt'=>'User Image']) ?>
                            <p>
                                <?= Yii::$app->user->identity->nombre ." ". Yii::$app->user->identity->apellido?>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-right">

                                <?php
                                if (Yii::$app->user->isGuest) {
                                    ?>
                                    <a href="<?php echo Yii::getAlias('@web/sites/login')  ?>" class="btn btn-default btn-flat">Login</a>
                                    <?php
                                }else {
                                    ?>
                                    <form action="<?php echo Yii::getAlias('@web/sites/logout')  ?>" method="post">
                                        <input type="hidden" name="salir" value="true">
                                        <input type="submit" class="btn btn-default btn-flat" value="Salir" />
                                    </form>
                                    <?php
                                }
                                ?>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->
            </ul>
        </div>
    </nav>
</header>
