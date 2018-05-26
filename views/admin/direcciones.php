<?php
date_default_timezone_set("Chile/Continental");
?>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Resumen de Direcciones</h3>
            </div>
            <div class="box-body">
                <table id="direcciones" class="table table-striped table-bordered table-hover form-inline dt-bootstrap" cellspacing="0" width="100%">
                    <thead>
                        <th>Último Nombre Registrado</th>
                        <th>Último Rut Registrado</th>
                        <th>Dirección</th>
                        <th>Primer Contacto</th>
                        <th>Vendedor</th>
                        <th>Última Acción Comercial</th>
                        <th>Info</th>
                    <tfoot>
                        <th>Último Nombre Registrado</th>
                        <th>Último Rut Registrado</th>
                        <th>Dirección</th>
                        <th>Primer Contacto</th>
                        <th>Vendedor</th>
                        <th>Última Acción Comercial</th>
                    </tfoot>
                    <tbody>
                    <?php
                    //repetir los datos a mostrar
                    for ($i = 0; $i < count($direcciones); $i++) {
                        $direcciones[$i] = (object)$direcciones[$i];
                        $prospecto_id = $direcciones[$i]->prospecto_id;
                        if (Yii::$app->user->identity->tipo_usuario == 1){
                            $accion = \app\models\AccionComercial::getAccionesComercialesEdo($prospecto_id);
                        }else{
                            $accion = \app\models\AccionComercial::getAccionesComercialesPorProspecto($prospecto_id, Yii::$app->user->id);
                        }
                        $rut = "";
                        if ($direcciones[$i]->tipo_creacion == 1) {
                            if (is_null($direcciones[$i]->rut_prospecto)){
                                $rut = "";
                            }else{
                                $rut = $direcciones[$i]->rut_prospecto . '-' . $direcciones[$i]->dv_prospecto;
                            }
                            ?>
                            <tr class="text-center">
                                <td><?php echo "<small>" . $direcciones[$i]->nombre . "</small>"; ?></td>
                                <td><?php echo "<small>" . $rut . "</small>"; ?></td>
                                <td><?php echo "<p>".$direcciones[$i]->calle . ' ' . '<br><small class="comuna">' . $direcciones[$i]->comuna . '</small></p>'; ?></td>
                                <td><?php echo is_null($direcciones[$i]->update_time) ? $direcciones[$i]->update_time : date('d-m-Y', $direcciones[$i]->update_time); ?></td>
                                <td><?php $vendedor = \app\models\Vendedor::getById($direcciones[$i]->id_vendedor);
                                    echo "<p>".$vendedor['nombre'] . " " . $vendedor['apellido'] . "<br><small>" . $vendedor["username"] . "</small></p>"; ?></td>
                                <td><?php
                                    $acc = \app\models\AccionComercial::getLastAccionComercial($prospecto_id);
                                    for ($j=0; $j<count($acc); $j++){
                                        echo "<p>".$acc[$j]->accion ." - <small>". date('d-m-Y', $acc[$j]->timestamp) . "</small></p><br>";
                                    }
                                    ?></td>
                                <td><?php echo "<a class=\"btn btn-default\" role=\"button\" data-toggle=\"modal\" data-target=\"#modalDetails".$i."\" href=\"#modalDetails".$i."\"> <span class=\"glyphicon glyphicon-info-sign\" aria-hidden=\"true\"></span></a>";?></td>
                            </tr>

                            <?php
                            echo "<div class=\"modal fade\" tabindex=\"-1\" role=\"dialog\" id=\"modalDetails".$i."\" aria-labelledby=\"myLargeModalLabel\">";
                            ?>
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title">Detalle</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row text-center">
                                            <div class="col-md-6">
                                                <h4>Datos de contacto actuales</h4>
                                                <br>
                                                <?php
                                                if (count($accion) != 0){
                                                    echo "<p><strong>Rut: </strong>" . $direcciones[$i]->rut_comprador . " - " . $direcciones[$i]->dv_comprador . "</p>";
                                                    echo "<p><strong>Nombre: </strong>" . $direcciones[$i]->nombre_comprador . "</p>";
                                                    if (isset($direcciones[$i]->fono_contacto_2)){
                                                        echo "<p><strong>Teléfono: </strong>" . $direcciones[$i]->fono_contacto_1 . " | " . $direcciones[$i]->fono_contacto_2 . "</p>";
                                                    }else{
                                                        echo "<p><strong>Teléfono: </strong>" . $direcciones[$i]->fono_contacto_1 ."</p>";
                                                    }
                                                    echo "<p><strong>Correo: </strong>" . $direcciones[$i]->rut_comprador . "</p>";
                                                    echo "<p><strong>Tipo de Contacto realizado: </strong>" . $direcciones[$i]->tipo_accion . " y " . $direcciones[$i]->tipo_contacto . "</p>";
                                                }else{
                                                    echo "<h4>NO HAY DATOS</h4>";
                                                }
                                                ?>
                                            </div>
                                            <div class="col-md-6">
                                                <h4>Acciones Comerciales realizadas</h4>
                                                <br>
                                                <?php
                                                if (count($accion) != 0){
                                                    for ($j = 0; $j < count($accion); $j++) {
                                                        echo "<p>".date('d-m-Y', $accion[$j]['timestamp']) . " - " . $accion[$j]['accion'] . "</p><br>";
                                                    }
                                                }else{
                                                    echo "<h4>NO HAY DATOS</h4>";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default btn-primary" data-dismiss="modal">OK</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                            <?php echo "</div>";?>
                            <?php
                        }else{
                            if (is_null($direcciones[$i]->rut_comprador)){
                                $rut = "";
                            }else{
                                $rut = $direcciones[$i]->rut_comprador . '-' . $direcciones[$i]->dv_comprador;
                            }
                            ?>
                            <tr class="text-center">
                                <td><?php echo "<small>" . $direcciones[$i]->nombre_comprador . "</small>"; ?></td>
                                <td><?php echo "<small>" . $rut . "</small>"; ?></td>
                                <td><?php echo "<p>".$direcciones[$i]->calle . ' ' . '<br><small class="comuna">' . $direcciones[$i]->comuna . '</small></p>'; ?></td>
                                <td><?php echo is_null($direcciones[$i]->update_time) ? "<p>".$direcciones[$i]->create_time."</p>" : "<p>".date('d-m-Y', $direcciones[$i]->update_time)."</p>"; ?></td>
                                <td><?php $vendedor = \app\models\Vendedor::getById($direcciones[$i]->id_vendedor);
                                    echo "<p>".$vendedor['nombre'] . " " . $vendedor['apellido'] . "<br><small>" . $vendedor["username"] . "</small></p>"; ?></td>
                                <td><?php
                                    $acc = \app\models\AccionComercial::getLastAccionComercial($prospecto_id);
                                    for ($j=0; $j<count($acc); $j++){
                                        echo "<p>".$acc[$j]->accion ." - <small>". date('d-m-Y', $acc[$j]->timestamp) . "</small><br></p>";
                                    }
                                    ?></td>
                                <td><?php echo "<a class=\"btn btn-default\" role=\"button\" data-toggle=\"modal\" data-target=\"#modalDetails".$i."\" href=\"#modalDetails".$i."\"> <span class=\"glyphicon glyphicon-info-sign\" aria-hidden=\"true\"></span></a>";?></td>            </tr>

                            <?php
                            echo "<div class=\"modal fade\" tabindex=\"-1\" role=\"dialog\" id=\"modalDetails".$i."\" aria-labelledby=\"myLargeModalLabel\">";
                            ?>
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title">Detalle</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row text-center">
                                            <div class="col-md-6">
                                                <h3>Datos de contacto actuales</h3>
                                                <?php
                                                if (count($accion) != 0){
                                                    echo "<p><strong>Rut: </strong>" . $direcciones[$i]->rut_comprador . " - " . $direcciones[$i]->dv_comprador . "</p>";
                                                    echo "<p><strong>Nombre: </strong>" . $direcciones[$i]->nombre_comprador . "</p>";
                                                    if (isset($direcciones[$i]->fono_contacto_2)){
                                                        echo "<p><strong>Teléfono: </strong>" . $direcciones[$i]->fono_contacto_1 . " | " . $direcciones[$i]->fono_contacto_2 . "</p>";
                                                    }else{
                                                        echo "<p><strong>Teléfono: </strong>" . $direcciones[$i]->fono_contacto_1 ."</p>";
                                                    }
                                                    echo "<p><strong>Correo: </strong>" . $direcciones[$i]->email . "</p>";
                                                }else{
                                                    echo "<h4>NO HAY DATOS</h4>";
                                                }
                                                ?>
                                            </div>
                                            <div class="col-md-6">
                                                <h3>Acciones Comerciales realizadas</h3>
                                                <?php
                                                if (count($accion) != 0){
                                                    for ($j = 0; $j < count($accion); $j++) {
                                                        echo "<p>".date('d-m-Y', $accion[$j]['timestamp']) . " - " . $accion[$j]['accion'] . "</p><br>";
                                                    }
                                                }else{
                                                    echo "<h4>NO HAY DATOS</h4>";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default btn-primary" data-dismiss="modal">OK</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                            <?php echo "</div>";?>
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
    var table = $('#direcciones').DataTable({
        dom: 'l<\"search-table\"fB>rtip',
        lengthMenu: [5, 10, 25, 50, 100],
        iDisplayLength: 5,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json'
        },
        buttons: [],
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
        table.buttons().container().appendTo('#direcciones_filter');
        }
    });
});
");