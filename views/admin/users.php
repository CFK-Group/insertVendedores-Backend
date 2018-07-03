<?php
    date_default_timezone_set("Chile/Continental");
?>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Resumen de Usuarios</h3>
            </div>
            <div class="box-body">
                <table id="usuarios" class="table table-striped table-bordered table-hover form-inline dt-bootstrap" cellspacing="0" width="100%">
                    <thead>
                    <th>Usuario</th>
                    <th>Último Ingreso</th>
                    <th>Última acción realizada</th>
                    <?php
                    if ($admin) {
                        ?>
                        <th>Ultimo update de ubicación</th>
                        <?php
                    }
                    ?>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Usuario</th>
                        <th>Último Ingreso</th>
                        <th>Última acción realizada</th>
                        <?php
                        if ($admin) {
                            ?>
                            <th>Ultimo update de ubicación</th>
                            <?php
                        }
                        ?>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    for ($i=0;$i<count($vendedores);$i++) {
                        if (isset($date_info)){
                            $date_info = null;
                        }
                        if ($admin){
                            $var = \app\models\AccionVendedor::getLastlogin($vendedores[$i]['id']);
                            $accion = \app\models\AccionVendedor::getLastaccion($vendedores[$i]['id']);
                            $pos = \app\models\AccionVendedor::getLastposition($vendedores[$i]['id']);
                        }else{
                            $var = \app\models\AccionVendedor::getLastlogin($vendedores[$i]['id_vendedor']);
                            $accion = \app\models\AccionVendedor::getLastaccionsuper($vendedores[$i]['id_vendedor']);
                        }

                        $cargo = "";
                        switch ($vendedores[$i]['tipo_usuario']) {
                            case 1:
                                $cargo = "Jefatura <br>";
                                break;
                            case 2:
                                $cargo = "Supervisor <br>";
                                break;
                            case 3:
                                $cargo = "Vendedor <br>";
                                break;
                        }

                        if ($vendedores[$i]['tipo_usuario'] == 3 ||$vendedores[$i]['tipo_usuario'] == 2) {
                            ?>
                            <tr class="text-center">
                                <td>
                                    <b><?php echo $vendedores[$i]['nombre'] ?></b><br>
                                    <small><?php echo $cargo ?><?php echo $vendedores[$i]['username'] ?></small>
                                </td>
                                <td>
                                    <?php
                                    echo isset($var)? date('d-m-Y H:i', $var): "SIN INFORMACIÓN";
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if (!isset($var)){
                                        $info = "SIN INFORMACIÓN";
                                    }else{
                                        $info = $accion['accion'];
                                        $date_info = date('d-m-Y H:i', $accion['timestamp']);
                                    }
                                    ?>
                                    <span class="text-uppercase"><?php echo $info?></span><br>
                                    <small><?php echo isset($date_info)? $date_info : ''; ?></small>
                                </td>
                                <?php
                                if ($admin) {
                                    ?>
                                    <td>
                                        <?php
                                        if (isset($pos)){
                                            ?>
                                            <a style="display: block" target="_blank" href="http://maps.google.com/?q=<?php echo $pos['lat'] . ',' . $pos['lon']?>"><?php echo $pos['lat'] . ' ,' . $pos['lon']?></a>
                                            <?php
                                        }else{
                                            ?>
                                            <p>SIN INFORMACIÓN</p>
                                            <?php
                                        }
                                        ?>
                                        <small><i><?php echo isset($pos)? date('d-m-Y H:i', $pos['timestamp']) : '';?></i></small>
                                    </td>
                                    <?php
                                }
                                ?>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(
"$(document).ready(function() {
    var table = $('#usuarios').DataTable({
        dom: 'l<\"search-table\"fB>rtip',
        lengthMenu: [5, 10, 25, 50, 100],
        iDisplayLength: 5,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json'
        },
        buttons: [
                {
                text: 'Crear Usuario Nuevo',
                action: function ( e, dt, node, config ) {
                    window.location.href = \"".Yii::getAlias('@web').'/admins/createuser'."\";
                }
            }],
        initComplete: function() {
            var i = 0;
            this.api().columns().every(function() {
                var column = this;
                var select = $('<select id=\"filter_' + i + '\"><option value=\"\"></option></select>')
                    .appendTo($(column.footer()).empty())
                    .on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );

                        column
                            .search(val ? '^.{0,}' + val + '.{0,}' : '', true, false)
                            .draw();
                    });

                column.data().unique().sort().each(function(d, j) {

                    if ($(d).html() === undefined || $(d).html() === '') {
                        var a = d;
                    } else {
                        var a = $(d).html();
                        if ($.parseHTML(a).length > 1) {
                            a = $($.parseHTML(a)).filter('small').text()
                        }
                    }
                    a.replace(/ñ/gi, 'ntilde');
                    a.replace(/<[^>]*>/g, ''); 
                    if (a.replace(/<[^>]*>/g, '') != '') {
                        var b = a.toUpperCase();
                        if ($('select#filter_' + i + ' option[value=\"' + b + '\"]').length <= 0) {
                            select.append('<option value=\"' + b + '\">' + b + '</option>')
                        }
                    }
                });
                i++;
            });
        table.buttons().container().appendTo('#usuarios_filter');
        }
    });
});
");