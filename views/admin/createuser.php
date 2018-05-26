<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-primary">
            <div class="box-body">
                <form id="createForm">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" class="form-control" name="nombre" placeholder="Nombre Apellido" required>
                    </div>

                    <div class="form-group">
                        <label for="Email">Correo</label>
                        <input type="email" class="form-control" name="email" placeholder="ejemplo@correo.cl">
                    </div>

                    <div class="form-group">
                        <label for="username">Codigo Tango</label>
                        <input type="text" class="form-control text-uppercase" name="username" placeholder="AABBCC" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="text" class="form-control" name="password" placeholder="ingrese aquí" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo_usuario">Rol</label>
                        <select name="tipo_usuario" class="form-control" id="rol" required>
                            <option value="2">Supervisor</option>
                            <option value="3" selected>Vendedor</option>
                            <option value="4" >Ejecutivo Chat</option>
                        </select>
                    </div>

                    <div class="form-group" id="supers">
                        <label for="tipo_usuario">Supervisor</label>
                        <select name="id_supervisor" class="form-control" id="rol" required>
                            <option value="0">Seleccione</option>
                            <?php
                            for ($i=0; count($supers)>$i; $i++){
                                echo "<option value=\"".$supers[$i]->id."\">".$supers[$i]->nombre." - ".$supers[$i]->username."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <input class="btn btn-primary" type="submit" value="OK">
                </form>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-success" role="alert" style="display: none;">
    <p>Usuario creado correctamente</p>
</div>

<div class="alert alert-danger" role="alert" style="display: none;">
    <p>UPS!! algo salió mal, intente nuevamente o contacte a soporte técnico</p>
</div>

<?php
$this->registerJS("
$('#rol').change(function(){
    if ($(this).val() !== \"3\"){
        $('#supers').hide();
    }else{        
        $('#supers').show();
    }
});

$('#createForm').submit(function(e){
    e.preventDefault();    
    var send = $.ajax({
        type: 'post',
        url: '../vendedors/createUser',
        datatype: 'json',
        data: $(this).serialize(),
    })
    
    send.done(function(data){
        if (data == \"success\"){
            $('.alert-success').show();
            $('#createForm')[0].reset();
        }else{
            $('.alert-danger').show();
            for(var prop in data){
                $('.alert-danger').append('<p>'+data[prop]+'</p>');
            }
        }
    });
    
    send.fail(function(data){
        $('.alert-danger').show();
        $('.alert-danger').append('<p>'+data+'</p>');
    });
    
})");
?>
