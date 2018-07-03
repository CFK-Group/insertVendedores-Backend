<h1>VENTAS PERDIDAS</h1>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <table id="table" class="table table-bordered table-striped dt-responsive nowrap" width="100%">
            </table>
        </div>
    </div>
</div>
<?php
$v = "[";

foreach ($vp as $venta){
    $v = $v . "[\"".$venta['id']."\",\"".$venta['id_servicio']."\",\"".$venta['rut_cliente']."-".$venta['dv_cliente']."\",\"".$venta['tipo_carga']."\",\"".$venta['estado_tango']."\"], ";
}
$v = $v . "]";
$this->registerJsFile("https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js", ['depends'=>['app\assets\AppAsset']]);
$this->registerJsFile("https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js", ['depends'=>['app\assets\AppAsset']]);
$this->registerJs(
    "var v = $v;
    $(document).ready(function () {
        var table = $('#table').DataTable({
        \"language\": {
                \"url\": \"//cdn.datatables.net/plug-ins/1.10.13/i18n/Spanish.json\"
            },
            \"aLengthMenu\": [[10, 25, 50, 100, -1], [10, 25, 50, 100, \"Todos\"]],
            data: v,
            fixedHeader: {
                header: true,
                footer: true
            },
            columns: [
                {title: \"<input type='text' class='lookfor' placeholder='Buscar ID' onkeyup='search(this)'/>\"},
                {title: \"id servicio\"},
                {title: \"rut cliente\"},
                {title: \"tabla\"},
                {title: \"estado tango\"}
            ],
        });
        
      
    });"
)
    ?>