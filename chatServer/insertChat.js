var app = require('express')();
var puerto = 2500;
var server = app.listen(puerto);
var io = require('socket.io').listen(server);

var mysql      = require('mysql');
var db = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'colseco',
  database : 'vendedores_insert'
});

io.set('log level', 1);
console.log("");
console.log("--------------- InsertChat Node Server--------------- ");
console.log("--------------- Servidor corriendo en puerto " + puerto);
console.log("");
console.log("Escuchando...");
console.log("");

db.connect();
console.log("Conectado a BD");

global.clientsSocketConnections = [];
global.agentsSocketConnections = [];
global.vendedoresEsperandoEjecutivo = [];

function insertMensaje(idVendedor, idEjecutivo, isFromVendedor, mensaje){
	var tipoMensaje = 2;
	var timeStamp = Date.now() / 1000;
	var subQueryidVendedor="(select id from vendedor where api_token='"+ idVendedor +"')";
	if(isFromVendedor)
		tipoMensaje = 1;
	var updateQuery = "INSERT INTO chat (idVendedor, idEjecutivo, tipoMensaje, mensaje, timestamp) VALUES " +
				" ( " + subQueryidVendedor +", "+idEjecutivo+", "+tipoMensaje+", '"+mensaje+"', "+timeStamp+")";
	console.log("Query para insert: " + updateQuery);
	db.query(updateQuery);
}

function getIndiceEjecutivoConMenorCarga(){
	var menorLargoDeCola = 5000;
	var indiceEjecutivoMenosOcupado = -1;
	
	global.clientsSocketConnections.forEach(function(item,index,arr){ 
		var largoFila =  Object.keys(item).length;
		if(largoFila < menorLargoDeCola){
			menorLargoDeCola = largoFila;
			indiceEjecutivoMenosOcupado = index;
		}
	});
	return indiceEjecutivoMenosOcupado;
	
}

function getIdEjecutivoByIdVendedor(idVendedor){
	var returner = -1;
	for( id_ejecutivo in global.clientsSocketConnections ){
		if( idVendedor in global.clientsSocketConnections[id_ejecutivo] ){
			console.log("vendedor lo está atendiendo ejecutivo " + id_ejecutivo);
			returner = id_ejecutivo;
			break;
		}
	}
	return returner;
}

io.sockets.on('connection', function ( socket ) {

    console.log("Llega nueva conexion!");
    var tipoCliente = socket.handshake.headers["tipocliente"];
    console.log("tipocliente =>" + tipoCliente);
		
    if(tipoCliente=="vendedor"){
    	
    	console.log("Nuevo vendedor conectado...");
		console.log("Headers: " + JSON.stringify(socket.handshake.headers));
		var idVendedor = socket.handshake.headers["idvendedor"]
		console.log("Busco si ya tengo socket activo para vendedor: " + idVendedor);
		var indexEjecutivoAtiende = -1;
		
		var yaAtendido = false;
		var socketEjecutivo = null;
		
		socket.on('disconnect', function () {
			if(yaAtendido){
				
				var idEjecutivoAtiende = getIdEjecutivoByIdVendedor(idVendedor);
				
				console.log("Se desconecta vendedor: " + idVendedor + " era atendido por ejectivo " + idEjecutivoAtiende);
				if(global.clientsSocketConnections[idEjecutivoAtiende] != undefined){ 
					global.agentsSocketConnections[idEjecutivoAtiende].emit("exit", {"tokenVendedor": idVendedor});
					delete global.clientsSocketConnections[idEjecutivoAtiende][idVendedor];
					console.log("Eliminado de lista de sockets del ejecutivo....");
				}
					
				else
					console.log("No encuentro socket para cerrar");
			}
		});
		
		socket.on('chatMsg', function (data) {
        	console.log("+++ Nuevo mensaje desde vendedor " + idVendedor );
        	console.log("+++ Mensaje es " + data );
			if( idVendedor in global.vendedoresEsperandoEjecutivo ){
				
				console.log("Vendedores en espera: ");
				for(var id in global.vendedoresEsperandoEjecutivo){
					console.log(id + " => " + global.vendedoresEsperandoEjecutivo[id]);
				}
				socket.emit("sinejecutivo", {});
				return;
			}
			
			//console.log("Array ejecutivos es de largo  " + Object.keys(global.agentsSocketConnections).length );
			var idEjecutivoAtiende = getIdEjecutivoByIdVendedor(idVendedor);
			console.log("Me atiende ejecutivo: " + idEjecutivoAtiende );
			//var idEjecutivoAtiende = getIdEjecutivoByIdVendedor(idVendedor);
			
			console.log("Listado de ejecutivos conectados: ");
			for(var indice_ejecutivo_atiende in global.agentsSocketConnections){
				console.log("indice ejec: " + indice_ejecutivo_atiende + ", socket: " + global.agentsSocketConnections[indice_ejecutivo_atiende]);
			}
			socketEjecutivo = global.agentsSocketConnections[idEjecutivoAtiende];
        	socketEjecutivo.emit("mensaje", { 'idVendedor': idVendedor, 'mensaje':data });
        	console.log("+++ Mensaje enviado a ejecutivo " + idEjecutivoAtiende);
			
			insertMensaje(idVendedor, idEjecutivoAtiende, true, data);
		});
		
		socket.on('otroMensaje', function (data) {
        	console.log("+++ otroMensaje " + data);	        
		});
		
		if( Object.keys(global.agentsSocketConnections).length  == 0 ){
			console.log("No hay ejecutivos conectados");
			socket.emit("esperandoejecutivo", {});
			global.vendedoresEsperandoEjecutivo[idVendedor] = socket;
			console.log("Agregado vendedor a cola de espera, nuevo largo es " +  Object.keys(global.vendedoresEsperandoEjecutivo).length );
		}
		else {
			global.clientsSocketConnections.forEach(function(item,index,arr){
				if( idVendedor in item ){
					console.log("vendedor lo está atendiendo ejecutivo " + index);
					indexEjecutivoAtiende = index;
					socketEjecutivo = item;
					yaAtendido = true;
				} else {
				}
			});
			
			
			if(!yaAtendido){
				console.log("Vendedor " + idVendedor + " pide nuevo chat, lo asigno...");
				
				indexEjecutivoAtiende = getIndiceEjecutivoConMenorCarga();
				//console.log("Array ejecutivos: " + global.agentsSocketConnections);
				
				console.log("Vendedor es asignado a ejecutivo: " + indexEjecutivoAtiende);
				socketEjecutivo = global.agentsSocketConnections[indexEjecutivoAtiende];
				socketEjecutivo.emit("nuevoChat", {'idVendedor': idVendedor });
				socket.emit("ejecutivoasignado", { 'ejecutivo': indexEjecutivoAtiende });
				console.log("Ejecutivo " +  indexEjecutivoAtiende + " notificado" );
				yaAtendido = true;
				global.clientsSocketConnections[indexEjecutivoAtiende][idVendedor] = socket;
			}
			
		}				
    	
    }
    else {
		var indiceEjecutivo=-1;
    	console.log("Nuevo ejecutivo de soporte conectado.");
		indiceEjecutivo = "" + socket.handshake.query['idEjecutivo'];
		console.log("Nuevo Ejecutivo conectado con id " + indiceEjecutivo);
		
		/*
        for(var k=0; k<100; k++){
        	if( ! (k in global.agentsSocketConnections) ){
        		console.log("Nuevo ejecutivo de soporte conectado en posicion " + k);
        		indiceEjecutivo = k;
        		global.clientsSocketConnections[indiceEjecutivo] = [];
        		global.agentsSocketConnections[indiceEjecutivo] = socket;
        		
        		if(  Object.keys(global.vendedoresEsperandoEjecutivo).length > 0){
        			console.log("Hay vendedores esperando, los asigno a ejecutivo");
        			
					for(var id_vendedor in global.vendedoresEsperandoEjecutivo){
						console.log("nuevo ejecutivo atenderá vendedor: " + id_vendedor );
						var socket_vendedor = global.vendedoresEsperandoEjecutivo[id_vendedor];
    					global.clientsSocketConnections[indiceEjecutivo][id_vendedor] = socket_vendedor;
    					socket_vendedor.emit("ejecutivoasignado", { 'ejecutivo': indiceEjecutivo });
						delete vendedoresEsperandoEjecutivo[id_vendedor];
						console.log("Se eliminó de cola de espera vendedor: " + id_vendedor );
					}
					
        		} else {
					console.log("No hay vendedores esperando...  Largo es: " +  Object.keys(global.vendedoresEsperandoEjecutivo).length );
				}
        		
        		break;
        	} else {
        		console.log("Ya existe ejecutivo " + k);
        	}
        } */
		
		global.clientsSocketConnections[indiceEjecutivo] = [];
		global.agentsSocketConnections[indiceEjecutivo] = socket;
		
		if(  Object.keys(global.vendedoresEsperandoEjecutivo).length > 0){
			console.log("Hay vendedores esperando, los asigno a ejecutivo");
			
			for(var id_vendedor in global.vendedoresEsperandoEjecutivo){
				console.log("nuevo ejecutivo atenderá vendedor: " + id_vendedor );
				var socket_vendedor = global.vendedoresEsperandoEjecutivo[id_vendedor];
				global.clientsSocketConnections[indiceEjecutivo][id_vendedor] = socket_vendedor;
				//socket_vendedor.emit("ejecutivoasignado", { 'ejecutivo': indiceEjecutivo });
				socket_vendedor.emit("llegaejecutivo", { 'ejecutivo': indiceEjecutivo });
				delete vendedoresEsperandoEjecutivo[id_vendedor];
				console.log("Se eliminó de cola de espera vendedor: " + id_vendedor );
				
				socket.emit("nuevoChat", {'idVendedor': id_vendedor });
			}
			
		} else {
			console.log("No hay vendedores esperando...  Largo es: " +  Object.keys(global.vendedoresEsperandoEjecutivo).length );
		}
		
        socket.emit("ejecutivoasignado", { "indiceEjecutivo" : indiceEjecutivo, } );
        socket.on('disconnect', function () {
            console.log("Se desconecta ejecutivo " + indiceEjecutivo);
			
			if(indiceEjecutivo in global.clientsSocketConnections && global.clientsSocketConnections[indiceEjecutivo] != null ){
				console.log("Ejecutivo atendia vendedores !! Gestionandolos....");
				var socketsAtendidos = global.clientsSocketConnections[indiceEjecutivo];
				global.clientsSocketConnections.splice(indiceEjecutivo, 1);
				global.agentsSocketConnections.splice(indiceEjecutivo, 1);
				
				if( Object.keys(socketsAtendidos).length >0 && Object.keys(global.agentsSocketConnections).length > 0 ){  // quedan ejecutivos, traspaso a otros...
					
					for(var token_vendedor in socketsAtendidos){
						var socket_vendedor = socketsAtendidos[token_vendedor];
						
						var indiceMenosOcupado = getIndiceEjecutivoConMenorCarga();
						console.log("indice Vendedor menos ocupado es : " + indiceMenosOcupado);
						socketEjecutivo = global.agentsSocketConnections[indiceMenosOcupado];
						socketEjecutivo.emit("nuevoChat", {'idVendedor': token_vendedor });
						socket.emit("ejecutivoasignado", { 'ejecutivo': indiceMenosOcupado });
						console.log("Ejecutivo " +  indiceMenosOcupado + " notificado" );
						global.clientsSocketConnections[indiceMenosOcupado][token_vendedor] = socket_vendedor;
						
					}
					/*
					socketsAtendidos.forEach(function(socket, tokenVendedor, arr){
						
						var indiceMenosOcupado = getIndiceEjecutivoConMenorCarga();
						console.log("Vendedor es asignado a ejecutivo: " + indiceMenosOcupado);
						socketEjecutivo = global.agentsSocketConnections[indiceMenosOcupado];
						socketEjecutivo.emit("nuevoChat", {'idVendedor': tokenVendedor });
						socket.emit("ejecutivoasignado", { 'ejecutivo': indiceMenosOcupado });
						console.log("Ejecutivo " +  indiceMenosOcupado + " notificado" );
						global.clientsSocketConnections[indiceMenosOcupado][tokenVendedor] = socket;
					});    */
				} else {  // no quedan ejecutivos, traspaso a cola de en espera
					console.log("No quedaban ejecutivos !!!  Pasando a cola de espera");
					for( id_vendedor in socketsAtendidos){
						console.log("Vendedor " + id_vendedor + " en espera...");
						global.vendedoresEsperandoEjecutivo[id_vendedor] = socketsAtendidos[id_vendedor];
						socketsAtendidos[id_vendedor].emit("esperandoejecutivo", {});
					}					
				}
			} else {
				console.log("Ejecutivo desconectado no atendia vendedores");
				global.clientsSocketConnections.splice(indiceEjecutivo, 1);
				global.agentsSocketConnections.splice(indiceEjecutivo, 1);
			}
			
        });
        
        socket.on("chatMsg", function(data){
        	var idDestinatario = data.idVendedor;
        	var mensaje = data.mensaje;
            console.log("*** Nuevo mensaje desde ejecutivo: " + indiceEjecutivo);
            console.log("*** Nuevo mensaje para vendedor: " + idDestinatario);
            console.log("*** Mensaje: " + data.mensaje );
            
            if( ! idDestinatario in global.clientsSocketConnections[indiceEjecutivo] ) {
            	socket.emit("vendedorDesconectado", data.idVendedor);
            	console.log("*** Ejecutivo no atiende a vendedor " + idDestinatario);
            } else {
            	console.log("*** Enviando mensaje...");
            	socketVendedor = global.clientsSocketConnections[indiceEjecutivo][idDestinatario];
            	socketVendedor.emit("mensaje", { 'mensaje': mensaje });
            	console.log("*** Enviando mensaje al socket de vendedor");
				
				insertMensaje(idDestinatario, indiceEjecutivo, false, mensaje);
            }
        });                        
    }
});
