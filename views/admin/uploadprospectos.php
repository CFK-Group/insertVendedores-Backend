<div class="row" style="margin-top: 5rem">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">
                    Cargar Direcciones
                </h3>
            </div>
            <div class="box-body with-border">
                <form action="uploadprospectos" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Seleccione el archivo a subir</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <label class="btn btn-default">AQUÍ
                            <input type="file" name="cvs" class="hidden" id="cvs-file" onchange="file_value(this)"></label>&nbsp;<p for="cvs" id="file-text" style="display: inline-block"></p>
                    </div>

                    <button type="submit" class="btn btn-primary disabled" id="send" disabled>Enviar</button>
                    <br>
                </form>
            </div>
        </div>
        <br>
        <?php
        if ($fileChecker == 1) {
            if (count($error) > 0) {
                ?>
                <!-- ERROR -->
                <div class="callout callout-danger">
                    <h4>Atención!</h4>

                    <p>Hemos tenido un problema, antes de que el error sucediera logramos almacenar <?= $counter ?>
                        registros.</p>
                    <p>ERROR: <br>
                        <?php
                        foreach ($error as $err) {
                            foreach ($err as $item) {
                                echo($item);
                                echo("<br>");
                            }
                        }
                        ?>
                        </p>
                </div>
                <?php
            } else {
                ?>
                <!-- SUCCESS -->
                <div class="callout callout-success">
                    <h4>Perfecto</h4>

                    <p>Todo salió bien, se han guardado <?= $counter ?> registros</p>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<?php
$this->registerJs("
        $('#cvs-file').on('change', function(){
            var file_input = this.files[0];
            if (file_input != null){
                $('#send').removeClass('disabled');
                $('#send').removeAttr('disabled');
            }
            var name = this.files[0].name;;
            $('#file-text').text(name);
        });
        ");
?>
