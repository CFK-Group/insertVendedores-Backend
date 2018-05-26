<div class="row" style="margin-top: 5rem">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    Cargar Bases
                </h3>
                <p>
                    Selecciona la base que desear cargar en la lista desplegable, luego elige el archivo desde tu disco duro
                </p>
            </div>
            <div class="box-body">
                <form action="uploadventas" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="tipo">El archivo a subir corresponde a</label><br>
                        <select name="tipo" id="tipo" class="form-control" required>
                            <option value="">Seleccione una opci&oacute;n</option>
                            <option value="1">Base de Ventas</option>
                            <option value="2">Base de Cobranza</option>
                            <option value="3">Base de Desconexiones</option>
                            <!--<option value="4">Base de Apertura</option>-->
                            <option value="5">Base de Dotación</option>
                        </select>
                    </div>

                    <div class="form-group" id="radio" style="display: none">
                        <input type="radio" name="tipoBase" value="portal" id="base1"><label for="base1">&nbsp;&nbsp;Base Portal</label><br>
                        <input type="radio" name="tipoBase" value="vtr" id="base2"><label for="base2">&nbsp;&nbsp;Base VTR</label>
                    </div>

                    <div class="form-group">
                        <label>Seleccione el archivo a subir</label><br>
                        <label class="btn btn-default">BUSCAR
                            <input type="file" name="cvs" class="hidden" id="cvs-file"></label>&nbsp;<p for="cvs" id="file-text" style="display: inline-block"></p>
                    </div>

                    <button type="submit" class="btn btn-primary disabled" id="send" disabled>Enviar</button>
                    <br>
                </form>
            </div>
        </div>
        <?php
        if ($fileChecker == 1) {
            if (count($error) > 0) {
                ?>
                <!-- ERROR -->
                <div class="callout callout-danger">
                    <h4>Atención!</h4>

                    <p>Hemos tenido un problema, antes de que el error sucediera logramos almacenar <?= $counterNews ?>
                        registros, además hemos actualizado <?= $counterUpdate ?> registros.</p>
                    <p>ERROR: <br>
                        <?php
                        foreach ($error as $err) {
                            foreach ($err as $item) {
                                echo($item);
                                echo("<br>");
                            }
                        }
                        //var_dump($error);
                        ?>
                    </p>
                </div>
                <?php
            } else {
                ?>
                <!-- SUCCESS -->
                <div class="callout callout-success">
                    <h4>Perfecto</h4>
                    <p>Se han creado <?= $counterNews ?> nuevos registros, además hemos actualizado <?= $counterUpdate ?> registros.</p>
                    <p>Se han detectado <?= $counterPerdidas ?> ventas potencialmente no comisiables</p>
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
        
        $('#tipo').on('change', function(){
            if(this.value === '1'){
                $('#radio').css('display', 'block');
            }
            else{
                $('#radio').css('display', 'none');
            }
        });
        ");
?>