<?php header('Content-Type: text/html; charset=UTF-8'); ?>
    <div class="container">
        <div class="chat_container">
            <div class="col-sm-3 chat_sidebar">
                <div class="row">
                    <div class="member_list_head">
                        <p>Todos los Chats</p>
                    </div>
                    <div class="member_list">
                        <ul class="list-unstyled">

                        </ul>
                    </div></div>
            </div>
            <!--chat_sidebar-->


            <div class="col-sm-9 message_section" style="display: none;">
                <div class="row">
                    <div class="new_message_head">
                        <div class="pull-left">
                            <button class="user-name" data-id-vendedor="'+idVendedor+'">
                                <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div><!--new_message_head-->

                    <div class="chat_area" id="chat_area">
                        <ul class="list-unstyled">

                        </ul>
                    </div><!--chat_area-->
                    <div class="message_write">
                        <textarea id="textoMensaje" class="form-control" placeholder="Escriba su Mensaje"></textarea>
                        <div class="clearfix"></div>
                        <div class="chat_bottom">
                            <input id="sendButton" type="submit" onclick="enviarMensaje()" value="Enviar" class="pull-right btn btn-primary">
                        </div>
                    </div>
                </div>
            </div> <!--message_section-->
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script src="http://colas.deppor.com/js/socket.io.js"></script>
    <script type="text/javascript">

        /*window.addEventListener("beforeunload", function(e){
            $.ajax({
                method: "POST",
                //url: "http://vendedores.xpass.cl/web/sites/logout"
                url: "http://localhost/insertVendedor/web/sites/logout",
                success: function(s){
                    console.log("asdsad" + s);
                },
                error: function (e) {
                    console.log(e);
                }
            });
        });*/
        var socket;
        var queue =  new Array();
        $(document).ready(function () {
            var url = "http://www.xpass.cl:2500";
            socket = io.connect(url, { query: "idEjecutivo=<?php echo Yii::$app->user->id;?>" } );
            comenzarMonitoreo();
        });

        $('#textoMensaje').keypress(function(event) {
            if (event.keyCode == 13 || event.which == 13) {
                event.preventDefault();
                enviarMensaje();
            }
        });

        function comenzarMonitoreo() {
            socket.on("nuevoChat", function (data) {
                //console.log(data);
                //
                //  data.idVendedor --> token
                //
                //evaluacion de quien est? solicitando un chat.
                var datosUser = $.ajax({
                    method: "GET",
                    url: "http://vendedores.xpass.cl/web/vendedors/getVendedor?token=" + data.idVendedor
                });
                //si el usuario es valido, obtengo los datos
                datosUser.done(function (user) {
                    //
                    //  user.nombreVendedor  --> Nombre completo
                    //  user.codigoTango --> Codigo TANGO
                    //  user.idVendedor --> id_bd
                    //
                    user = JSON.parse(user); //asumiremos que el json.parse nos devolvera un objeto
                    //lo agregamos a la cola de solicitudes si es que no lo tenemos
                    if (objectFindByKey(queue, 'id', user.id)) {
                        //si ya tengo una solicitud iniciada, envio un nuevo mensaje
                    } else {
                        //si no tengo solicitudes iniciadas, la agrego a la lista de solicitudes
                        $.ajax({
                            type: "GET",
                            datatype: "json",
                            contentType: "application/json",
                            url: "http://vendedores.xpass.cl/web/chats/msgs?idVendedor="+user.idVendedor+"&idEjecutivo=<?php echo Yii::$app->user->id;?>",
                            success: function(msgs){
                                msgs = JSON.parse(msgs);
                                queue[user.codigoTango] = msgs;
                                newChat(user, data.idVendedor);
                                showNotification(user.codigoTango);
                            }
                        });
                    }
                })
            });

            socket.on("mensaje", function (data) {
                //cuando llegue un nuevo mensaje, lo agrego al listado de mensajes del usuario
                var datosUser = $.ajax({
                    method: "GET",
                    url: "http://vendedores.xpass.cl/web/vendedors/getVendedor?token=" + data.idVendedor
                });

                datosUser.done(function (user) {
                    //user return
                    //'nombreVendedor' => $vendedor->nombre,
                    //'codigoTango' => $vendedor->username,
                    //'idVendedor' => $vendedor->id
                    user = JSON.parse(user); //asumiremos que el json.parse nos devolvera un objeto
                    $.ajax({
                        type: "GET",
                        datatype: "json",
                        contentType: "application/json",
                        url: "http://vendedores.xpass.cl/web/chats/msgs?idVendedor=" + user.idVendedor + "&idEjecutivo=<?php echo Yii::$app->user->id;?>",
                        success: function (response) {
                            //data = son los mensajes que se recibieron desde el server

                            response = JSON.parse(response);
                            queue[user.codigoTango] = response;
                            var tango = $('.user-name').attr("data-id-vendedor");
                            if (tango === user.codigoTango) {
                                loadChat(user.nombreVendedor, user.codigoTango);
                            }
                        }
                    });

                });
            });

            socket.on("exit", function (data) {
                var datosUser = $.ajax({
                    method: "GET",
                    url: "http://vendedores.xpass.cl/web/vendedors/getVendedor?token=" + data.tokenVendedor
                });

                datosUser.done(function (user) {
                    user = JSON.parse(user);
                    queue[user.codigoTango]= JSON.parse(queue[user.codigoTango]);
                    var hora = "disconnected";
                    var mensaje = {"mensaje": "El usuario se ha desconectado, ya no puedes comunicarte a menos que el vuelva comunicarse contigo", "hora": hora, "emisor": "user-out"};
                    queue[user.codigoTango][queue[user.codigoTango].length] = mensaje;
                    var tango = $('.user-name').attr("data-id-vendedor");

                    if (tango === user.codigoTango) {
                        loadChat(user.nombreVendedor, user.codigoTango);
                    }

                    $('ul li[data-id-vendedor='+user.codigoTango+'] > .chat-body > .header_sec > .pull-container').append(
                        "<p><span class='label label-default'>Chat terminado</span><p>"
                    );
                });
            });
        }
        function newChat(user, token) {
            var f = new Date();
            var hora=f.getHours()+":"+addZero(f.getMinutes());
            $(".member_list ul").append(
                '<li class="left clearfix" onclick="loadChat(\''+ user.nombreVendedor+'\',\''+ user.codigoTango+'\')" data-id-vendedor=\"'+user.codigoTango+'\">' +
                    '<span class="chat-img pull-left">' +
                        '<img src="img/user_vacio.jpg" alt="User Avatar" class="img-circle">' +
                    '</span>' +
                    '<div class="chat-body clearfix">' +
                        '<div class="header_sec">' +
                            '<strong class="primary-font">'+user.nombreVendedor+'</strong> ' +

                            '<div class="pull-right pull-container">' +
                                '<div class="pull-right"><strong>'+hora+'&nbsp;&nbsp;&nbsp;</strong>' +
                                '<span id="close-chat" onclick="closeChat(\''+user.codigoTango+'\', \''+token+'\')">' +
                                    '<i class="glyphicon glyphicon-trash"></i>' +
                                '</span></div>'+
                            '</div>'+
                        '</div>' +
                        '<div class="contact_sec">' +
                            '<strong class="primary-font">'+user.codigoTango+'</strong>' +
                        '</div>' +
                    '</div>' +
                '</li>');
        }

        function loadChat(name, codigoTango){
            if ($('#textoMensaje').attr("disabled")==="disabled"){
                $('#textoMensaje').removeAttr("disabled");
                $('#sendButton').removeClass('disabled');
            }
            var display = $('.message_section').css("display");
            if (display === "none"){
                $('.message_section').css("display", "inherit");
            }
            $('.user-name').text(name);
            $('.user-name').attr("data-id-vendedor", codigoTango);
            $('.chat_area ul').html("");
            for(vendedor in queue){
                if (vendedor === codigoTango) {

                    if (typeof queue[vendedor] === "string"){
                        mensajes = JSON.parse(queue[vendedor]);
                    }else{
                        mensajes = queue[vendedor];
                    }
                    for (var i = 0; i < mensajes.length; i++) {
                        renderMsg(mensajes[i]);
                        if (mensajes[i].hora === 'disconnected'){
                            $('#textoMensaje').attr('disabled', 'disabled');
                            $('#sendButton').addClass('disabled');
                        }
                    }
                }
            }
            $('#textoMensaje').focus();
            $('.chat_area').scrollTop(document.getElementById('chat_area').scrollHeight);
        }

        function enviarMensaje(){
            if ($('#textoMensaje').val() !== "") {
                var f = new Date();
                var hora = f.getHours() + ":" + addZero(f.getMinutes());
                var txt = $("#textoMensaje").val();
                var user = $(".user-name").attr("data-id-vendedor");

                var token = $.ajax({
                    method: "GET",
                    url: "http://vendedores.xpass.cl/web/vendedors/getVendedor?tango=" + user
                });

                token.done(function (data) {
                    //data entrega el token
                    // data.token
                    //
                    data = JSON.parse(data);
                    socket.emit('chatMsg', {'idVendedor': data.token, 'mensaje': txt});
                    var mensaje = {"mensaje": txt, "hora": hora, "emisor": "ejecutivo"};
                    //queue[user].push(mensaje);
                    renderMsg(mensaje);
                    $('.chat_area').scrollTop(document.getElementById('chat_area').scrollHeight);
                    $("#textoMensaje").val("");
                });
            }
        }

        function renderMsg(mensaje){
            var msg = mensaje.mensaje;
            var emisor = mensaje.emisor;
            var hora = mensaje.hora;

            if(msg != "EL EJECUTIVO HA CERRADO LA CONVERSACION, PARA ABRIR UNA NUEVA SESION CIERRE LA VENTANA DE CHAT Y VUELVA A ABRIR"){
                if (emisor === "vendedor"){
                    $(".chat_area ul").append(
                        '<li class="left clearfix">' +
                        '<span class="chat-img1 pull-left">' +
                        '<img src="img/user_vacio.jpg" alt="User Avatar" class="img-circle">' +
                        '</span>' +
                        '<div class="chat-body1 clearfix">' +
                        '<p>' +
                        msg +
                        '<br><br>' +
                        '<small class="chat_time pull-right">'+hora+'</small>' +
                        '<br>' +
                        '</p>' +
                        '</div>' +
                        '</li>'
                    );
                }else if(emisor === "ejecutivo"){
                    $(".chat_area ul").append(
                        '<li class="left clearfix admin_chat">' +
                        '                <span class="chat-img1 pull-right">' +
                        '                <img src="img/user_vacio.jpg" alt="User Avatar" class="img-circle">' +
                        '                </span>' +
                        '                <div class="chat-body1 clearfix">' +
                        '                <p>' + msg +
                        '            <br><br>' +
                        '            <small class="chat_time pull-left">'+hora+'</small>' +
                        '            <br>' +
                        '            </p>' +
                        '            </div>' +
                        '            </li>'
                    );
                }else if(emisor === "user-out" || emisor === ""){
                    $(".chat_area ul").append(
                        '<div class="alert alert-warning text-center" role="alert">'+
                        msg+
                        '</div>'
                    )
                }
            }

        }

        function objectFindByKey(array, key, value) {
            for (var i = 0; i < array.length; i++) {
                if (array[i][key] === value) {
                    return true;
                }
            }
            return false;
        }

        function addZero(i) {
            if (i < 10) {
                i = "0" + i;
            }
            return i;
        }

        function closeChat(tango, token){
            if (!e) var e = window.event;
            e.cancelBubble = true;
            if (e.stopPropagation) e.stopPropagation();
            var display = $('.message_section').css("display");
            if (display === "block"){
                $('.message_section').css("display", "none");
            }


            if (delete queue[tango]){
                $('ul li[data-id-vendedor='+tango+']').remove();
                console.log(token);
                socket.emit('chatMsg', {'idVendedor': token, 'mensaje': 'EL EJECUTIVO HA CERRADO LA CONVERSACION, PARA ABRIR UNA NUEVA SESION CIERRE LA VENTANA DE CHAT Y VUELVA A ABRIR'});
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (Notification) {
                if (Notification.permission !== "granted")
                    Notification.requestPermission();
            }
        });

        function showNotification(user) {
            if (Notification){
                if (Notification.permission !== "granted")
                    Notification.requestPermission();
                else {
                    var notification = new Notification('Insert Chat', {
                        icon: 'http://www.freeiconspng.com/uploads/message-icon-png-15.png',
                        body: "Nuevo Mensaje de " + user,
                        requireInteraction: false
                    });

                    notification.onclick = function () {
                        window.focus();
                        this.cancel();
                    };

                    notification.show();
                }
            }else {
                alert("NUEVO CHAT DESDE: " + user);
            }
        }
    </script>

