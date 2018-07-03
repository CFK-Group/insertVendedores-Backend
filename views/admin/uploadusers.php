<h1>Subir Usuarios</h1>
<div class="row" style="margin-top: 5rem">
    <div class="col-md-10 col-md-offset-1">
        <form action="uploadusers" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Seleccione el archivo a subir</label>&nbsp;&nbsp;&nbsp;&nbsp;
                <label class="btn btn-default">AQUÍ
                    <input type="file" name="cvs" class="hidden" id="cvs-file" onchange="file_value(this)"></label>&nbsp;<p for="cvs" id="file-text" style="display: inline-block"></p>
            </div>

            <button type="submit" class="btn btn-default btn-success disabled" id="send">Enviar</button>
            <br>
        </form>


        <!--<h1>DIR....aunque deberian ser fechas pero bueh</h1>
        <form action="uploadusersdir" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Seleccione el archivo a subir</label>&nbsp;&nbsp;&nbsp;&nbsp;
                <label class="btn btn-default">AQUÍ
                    <input type="file" name="cvs" class="hidden" id="cvs-file-2" onchange="file_value(this)"></label>&nbsp;<p for="cvs" id="file-text-2" style="display: inline-block"></p>
            </div>

            <button type="submit" class="btn btn-default btn-success disabled" id="send-2">Enviar</button>
            <br>
        </form>-->
        <br>
        <?php
        //upload:1 OK
        //upload: 2 some failed
        if ($upload==1) {
            ?>
            <div class="alert alert-success" role="alert">
                <p>Los datos en el archivo <strong><?php echo $file_name; ?></strong> se han cargado correctamente</p>
            </div>
            <?php
        }else /*if ($upload==2)*/{
            ?>
            <div class="alert alert-warning" role="alert">
                <p>UPS!! algo salió mal, intente nuevamente o contacte a soporte técnico</p>
                <p><?= $upload ?></p>
            </div>
            <?php
        }
        ?>
        <p>
        <?= $save_vendedor?>
        </p>
    </div>
</div>

<?php
$this->registerJs("
        $('#cvs-file').on('change', function(){
            var file_input = this.files[0];
            if (file_input == null){
               null;
            }else{
                $('#send').removeClass('disabled');
            }
            var name = this.files[0].name;;
            $('#file-text').text(name);
        });
        
        /*$('#cvs-file-2').on('change', function(){
            var file_input = this.files[0];
            if (file_input == null){
               null;
            }else{
                $('#send-2').removeClass('disabled');
            }
            var name = this.files[0].name;;
            $('#file-text-2').text(name);
        });*/
        ");
?>
