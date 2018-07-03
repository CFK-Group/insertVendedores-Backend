<?php
$usuario = (\app\models\Vendedor::getById(Yii::$app->user->id));
$data[0] = [];
$categories = "[";
for ($i = 0; $i < count($data[key($data)]); $i++){
    $categories = $categories . "\"". array_keys(reset($data))[$i] . "\", ";
}
$categories = $categories . "]";


$flag = true;
reset($data);
$series = "[{";
while($flag){
    $series = $series . "name: '". key($data)."', data: [";
    $current = current($data);
    for ($i = 0; $i < count($current); $i++){
        $series = $series . "". array_values($current)[$i] . ", ";
    }
    if(next($data)=== false){
        $flag = false;
        $series = $series . "]}";
    }else{
        $series = $series . "]},{";
    }
}
$series = $series . "]";
?>



            <div class="row">
                <div class="col-md-12 col-sm-6 col xs-12">
                    <form role="form" class="form-inline">
                        <label for="range">Periodo de tiempo a mostrar para los cuadros</label>
                        <select class="form-control" name="range" id="range">
                            <option value="">Sin Calcular</option>
                            <option value="1 week ago">1 Semana</option>
                            <option value="2 week ago">2 Semanas</option>
                            <option value="3 week ago">3 Semanas</option>
                            <option value="first day of this month">Mes Actual</option>
                        </select>
                    </form>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon"><i class="glyphicon glyphicon-cloud-upload"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text info-box-text-small">Total direcciones cargadas</span>
                            <span class="info-box-number" id="dirCargadas"> -- </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon"><i class="glyphicon glyphicon-cloud-upload"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text info-box-text-small">Total direcciones con accion comercial</span>
                            <span class="info-box-number" id="dirAccionComercial"> -- </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon"><i class="glyphicon glyphicon-cloud-upload"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text info-box-text-small">Total prospectos creados</span>
                            <span class="info-box-number" id="prospectosCreados"> -- </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
            </div>


            <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon"><i class="glyphicon glyphicon-cloud-upload"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text info-box-text-small">Total ventas instaladas</span>
                            <span class="info-box-number" id="vInstaladas"> -- </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon"><i class="glyphicon glyphicon-cloud-upload"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text info-box-text-small">Total ventas en INS</span>
                            <span class="info-box-number" id="vINS"> -- </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon"><i class="glyphicon glyphicon-cloud-upload"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text info-box-text-small">Prospectos con Mail</span>
                            <span class="info-box-number" id="pmail"> -- </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
            </div>



<div class="row">
<?php
if ($usuario->tipo_usuario == 1) {
    ?>
    <div class="col-lg-4 col-xs-6">
    <?php
    }else{
    ?>
    <div class="col-lg-offset-1 col-lg-5 col-xs-6">
    <?php
}
?>
            <!-- small box -->
            <div class="small-box bg-green" id="direccion">
                <div class="inner">
                    <h3>Descargar Excel</h3>
                    <p>con el Resumen de Direcciones y Acciones Comerciales</p>
                </div>
                <div class="icon">
                    <i class="fa fa-home"></i>
                </div>
                <!--<a href="<?=Yii::getAlias('@web').'/admins/reportedirecciones?token='.md5(uniqid(rand(), true)) ?>" class="small-box-footer" id="dw_dir" download>
                    Descargar Aqu&iacute;&nbsp;&nbsp;&nbsp;<i class="fa fa-arrow-circle-right"></i>
                </a>-->
                <a class="small-box-footer" data-toggle="modal" data-target="#dir-modal" >
                    Descargar <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
<?php
if ($usuario->tipo_usuario == 1) {
    ?>
    <div class="col-lg-4 col-xs-6">
    <?php
}else{
    ?>
        <div class="col-lg-5 col-xs-6">
    <?php
}
        ?>
            <!-- small box -->
            <div class="small-box bg-green" id="usuario">
                <div class="inner">
                    <h3>Descargar Excel</h3>
                    <p>con el Resumen de Actividades de los Usuarios</p>
                </div>
                <div class="icon">
                    <i class="fa fa-user"></i>
                </div>
                <a class="small-box-footer" data-toggle="modal" data-target="#user-modal" >
                    Descargar <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
<?php
if ($usuario->tipo_usuario == 1) {
    ?>
    <div class="col-lg-4 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green" id="ventas">
            <div class="inner">
                <h3>Descargar Excel</h3>
                <p>con el Reporte de Ventas mensual</p>
            </div>
            <div class="icon">
                <i class="fa fa-line-chart"></i>
            </div>
            <a class="small-box-footer" data-toggle="modal" data-target="#ventas-modal">
                Descargar <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    </div>

    <?php
}
?>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Resumen por Vendedor para el periodo de tiempo seleccionado</h3>
                </div>
                <div class="box-body">
                    <table class="table table-striped table-condensed table-bordered table-hover" id="resumen">
                        <tr>
                            <th>Vendedor</th>
                            <th>Total direcciones cargadas</th>
                            <th>Total direcciones con accion comercial</th>
                            <th>Total prospectos creados</th>
                            <th>Total ventas instaladas</th>
                            <th>Total ventas en INS</th>
                            <th>Prospectos con Mail</th>
                        </tr>
                        <tr>
                            <td class="table-spinner" colspan="7">
                                Sin Calcular
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>


<?php
if ($usuario->tipo_usuario == 1) {
    ?>
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Cantidad de Actividades Diarias
                        <small>para los últimos 7 días</small>
                    </h3>
                </div>
                <div class="box-body with-border">
                    <div id="graph" style="height: 400px"></div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $this->registerJsFile("https://code.highcharts.com/highcharts.js");
    $this->registerJsFile("https://code.highcharts.com/highcharts-3d.js");
    $this->registerJS(
        "Highcharts.chart('graph', {
        chart: {
        type: 'column',
            options3d: {
            enabled: true,
                alpha: 10,
                beta: 25,
                depth: 70
            }
        },
        title: {
        text: ''
        },
        subtitle: {
        //text: 'separadas por actividad, para los últimos 7 días'
        },
        plotOptions: {
        column: {
            depth: 25
            }
        },
        xAxis: {
        categories: " . $categories . "
        },
        yAxis: {
        title: {
            text: null
            }
        },
        series: " . $series . "
});"
    );
}
?>
    <div class="modal fade" id="dir-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Resumen de direcciones</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p>Seleccione el intervalo de fechas para obtener los datos</p>
                            <form action="<?=Yii::getAlias('@web').'/admins/reportedirecciones' ?>" method="get" id="dw_dir">
                                <div class="form-group">
                                    <label for="from">Desde el: &nbsp;&nbsp;</label>
                                    <input type="date" name="from" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="to">Hasta el: &nbsp;&nbsp;</label>
                                    <input type="date" name="to" class="form-control">
                                    <input type="hidden" name="token" value="<?= md5(uniqid(rand(), true))?>" id="dir_tkn">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" form="dw_dir" id="submit-dir">Descargar</i></button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="user-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Actividad de Usuarios</h4>
                </div>
                <div class="modal-body">
                    <p>Seleccione el intervalo de fechas para obtener los datos</p>
                    <form action="<?=Yii::getAlias('@web').'/admins/reporteusers'?>" method="GET" id="dw_usr">
                        <div class="form-group">
                            <label for="from">Desde el: &nbsp;&nbsp;</label>
                            <input type="date" name="from" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="to">Hasta el: &nbsp;&nbsp;</label>
                            <input type="date" name="to" class="form-control">
                            <input type="hidden" name="token" value="<?= md5(uniqid(rand(), true))?>" id="usr_tkn">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="submit-usr" form="dw_usr">Descargar</i></button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="ventas-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Reporte de Ventas</h4>
                </div>
                <div class="modal-body">
                    <p>Seleccione el intervalo de fechas para obtener los datos</p>
                    <form action="<?=Yii::getAlias('@web').'/admins/reporteventas'?>" method="GET" id="dw_ventas">
                        <div class="form-group">
                            <label for="from">Mes: &nbsp;&nbsp;</label>
                            <select name="mes">
                                <option value="">Seleccione</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                            <input type="hidden" name="token" value="<?= md5(uniqid(rand(), true))?>" id="ventas_tkn">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="submit-ventas" form="dw_ventas">Descargar</i></button>
                </div>
            </div>
        </div>
    </div>


<?php


$this->registerJs("
$('#submit-usr').click(function(){
    $('#usuario').addClass('loading')
});

$('#submit-dir').click(function(){
    $('#direccion').addClass('loading')
});

$('#submit-ventas').click(function(){
    $('#ventas').addClass('loading')
});

$('#dw_usr, #dw_dir, #dw_ventas').submit(function(evt){
    setInterval(function(){
        var cookie = document.cookie;
        if( cookie.length > 0 ){
            var cookies = cookie.split(';');
            for( var i = 0; i < cookies.length ; i++){
                var ecookie = cookies[i].split('=');
                if(ecookie[0] = 'token'){
               
                    if ( $('#dir_tkn').attr('value') == ecookie[1] ){
                       $('#direccion').removeClass('loading');
                       $('#dir-modal').modal('hide');
                    }
                    
                    if ($('#usr_tkn').attr('value')== ecookie[1]){
                        $('#usuario').removeClass('loading');
                        $('#user-modal').modal('hide');
                    }
                    
                    if ($('#ventas_tkn').attr('value')== ecookie[1]){
                        $('#ventas').removeClass('loading');
                        $('#ventas-modal').modal('hide');
                    }
                }
            }
        }
    }, 1000);
    })
");

$this->registerJS("

    function getData(range) {
        $.ajax({
            url:'". Yii::getAlias('@web/admins/resumedata') ."',
            method: 'GET',
            data: {
                'range': range,
                'id_usuario': ".Yii::$app->user->identity->getId() .",
                'tipo_usuario': ".Yii::$app->user->identity->getTipoUsuario() .",
            },
            success: function(data){
                //console.log('OK');
                $('#dirCargadas').text(data.direccionesCargadas);
                $('#dirAccionComercial').text(data.prospectosConAccionComercial);
                $('#prospectosCreados').text(data.prospectosCreados);
                $('#vInstaladas').text(data.ventasTerminadas);
                $('#vINS').text(data.ventasEnInstalacion);
                $('#pmail').text(data.prospectosConMail);
                var tr;
                $('.generated').remove();
                $('.table-spinner').remove();
                //console.log(Object.keys(data.dataVendedores));
                if (Object.keys(data.dataVendedores).length > 0){
                    for (var vendedor in data.dataVendedores){
                        tr = $('<tr>');
                        tr.append(\"<td style='text-align: center' class='generated'>\" + vendedor + \"</td>\");
                        tr.append(\"<td style='text-align: center' class='generated'>\" + data.dataVendedores[vendedor].direccionesCargadas + \"</td>\");
                        tr.append(\"<td style='text-align: center' class='generated'>\" + data.dataVendedores[vendedor].prospectosConAccionComercial + \"</td>\");
                        tr.append(\"<td style='text-align: center' class='generated'>\" + data.dataVendedores[vendedor].prospectosCreados + \"</td>\");
                        tr.append(\"<td style='text-align: center' class='generated'>\" + data.dataVendedores[vendedor].ventasTerminadas + \"</td>\");
                        tr.append(\"<td style='text-align: center' class='generated'>\" + data.dataVendedores[vendedor].ventasEnInstalacion + \"</td>\");
                        tr.append(\"<td style='text-align: center' class='generated'>\" + data.dataVendedores[vendedor].prospectosConMail + \"</td>\");
                        $('#resumen').append(tr);
                    }
                }else{
                        tr = $('<tr>');
                        tr.append(\"<td style='text-align: center' class='generated' colspan='6'><b>No tiene vendedores asignados</b></td>\");
                        $('#resumen').append(tr);

                }
                //console.log(data);
                
            },
            error: function(data){
                console.log('ERROR');
                console.log(data);
            },
            dataType: 'json'
        });
    }
    
    $('#range').change(function(){
        range = $(this).val()
        if (range != ''){
            getData(range);
            $('.info-box-number').html('<img src=\"img/spinner.gif\" alt=\"loading...\" class=\"spinner\">');
            $('#resumen').append('<td class=\"table-spinner\" colspan=\"6\"><img src=\"img/spinner.gif\" alt=\"loading...\" class=\"spinner\" style=\"margin: 0 auto\"></td>');    
            $('.generated').remove();
        }else{
            $('.info-box-number').html('--');
            $('#resumen').append('<td class=\"table-spinner\" colspan=\"6\">Sin Calcular</td>');    
            $('.generated').remove();
        }
    })
");

