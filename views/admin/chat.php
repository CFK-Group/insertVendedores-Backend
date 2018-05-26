
<div>
	
	<span id="texto">bla bla bla bla</span>
	<br/>
	<input placeholder="idVendedor" id="idVendedor" type="text">
	<input placeholder="mensaje" id="textoMensaje" type="text">
	<input type="submit" onclick="enviarMensaje()" value="Enviar">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script src="http://colas.deppor.com/js/socket.io.js"></script>
    <script>
    function enviarMensaje(){
    var mensaje = $("#textoMensaje").val();
    var vendedor = $("#idVendedor").val();
    socket.emit('chatMsg', { 'idVendedor': vendedor, 'mensaje':mensaje });
    }

    var socket;

    $(document).ready(function () {
    var url = "http://www.xpass.cl:2500";
    socket = io.connect(url);
    comenzarMonitoreo();
    });

    function comenzarMonitoreo() {
    //socket.emit('monitorearCola', { idCola: idCola });
    socket.on("nuevoChat", function(data){
        console.log("nuevoChat");
    alert("nueva peticion de chat desde vendedor: " + data.idVendedor);
    });
    socket.on("mensaje", function(data){
        console.log("nuevoMSG");
    alert("Mensaje de vendedor: " + data.idVendedor + " es: " + data.mensaje);
    });
    }
    </script>


</div>