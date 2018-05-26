<?php
//var_dump($this->context->route );
$classActiveR = false;
$classActiveB = false;
$reportsUrl = ['admin/getprospectos', 'admin/getusers'];
$basesUrl = ['admin/uploadventas', 'admin/uploadprospectos'];
in_array($this->context->route, $reportsUrl) ? $classActiveR = true : $classActiveR = false;
in_array($this->context->route, $basesUrl) ? $classActiveB = true : $classActiveB = false;

use adminlte\widgets\Menu;
use yii\helpers\Html;
use yii\helpers\Url;
?>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <?= Html::img('@web/img/user-icon2.png', ['class' => 'img-circle', 'alt' => 'User Image']) ?>
            </div>
            <div class="pull-left info">
                <p><?= Yii::$app->user->identity->nombre ." ". Yii::$app->user->identity->apellido?></p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <?=
        Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    [
                        'label' => 'Menu',
                        'options' => ['class' => 'header']
                    ],
                    [
                        'label' => 'Dashboard',
                        'icon' => 'fa fa-dashboard',
                        'url' => '@web/admins',
                        'active' => $this->context->route == 'admin/index'
                    ],
                    [
                        'label' => 'Resumenes',
                        'icon' => 'fa fa-table',
                        'url' => '#',
                        'active' => $classActiveR,
                        'items' => [
                            [
                                'label' => 'Resumen Direcciones',
                                'icon' => 'fa fa-table',
                                'url' => '@web/admins/direcciones',
                                'active' => $this->context->route == 'admin/getprospectos'
                            ],
                            [
                                'label' => 'Resumen Usuarios',
                                'icon' => 'fa fa-table',
                                'url' => '@web/admins/users',
                                'active' => $this->context->route == 'admin/getusers'
                            ]
                        ]
                    ],
                    [
                        'label' => 'Crear Usuario',
                        'icon' => 'fa fa-plus',
                        'url' => '@web/admins/createuser',
                        'active' => $this->context->route == 'admin/createuser',
                    ],
                    [

                    ],
                    [
                        'label' => 'Cargar Datos',
                        'icon' => 'fa fa-file-excel-o',
                        'url' => '#',
                        'active' => $classActiveB,
                        'items' => [
                            [
                                'label' => 'Cargar Bases',
                                'icon' => 'fa fa-database',
                                'url' => '@web/admins/uploadventas',
                                'active' => $this->context->route == 'admin/uploadventas'
                            ],
                            [
                                'label' => 'Cargar Direcciones',
                                'icon' => 'fa fa-database',
                                'url' => '@web/admins/uploadprospectos',
                                'active' => $this->context->route == 'admin/uploadprospectos'
                            ]
                        ]
                    ]
                ],
            ]
        )
        ?>

    </section>
    <!-- /.sidebar -->
</aside>
