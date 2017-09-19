<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require 'vendor/autoload.php';
ini_set('date.timezone', 'America/Mexico_City');

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['db']['host']   = "127.0.0.1";
$config['db']['user']   = "root";
$config['db']['pass']   = "Gaddp552014";
$config['db']['dbname'] = "monitoreoGa";
$config['db']['charset']= "utf8";
$config['db']['port']	= "3307";

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['db'] = function ($c) {
	$db = $c['settings']['db'];
	$pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'] . ";charset=" . $db['charset'] . ";port=" . $db['port'],$db['user'], $db['pass']);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	return $pdo;
};
$app->get('/prepare/', function (Request $request, Response $response) {
	$sakura=$this->db->prepare("delete from FeedNewPemex");
	$sakura->execute();
	$time = new DateTime();
	$rest = $time->modify('-5 minutes');
	$full=$rest->format('H:i:s');
	
	$sakura=$this->db->prepare("select idEditorial, Periodico, Seccion, Categoria, NumeroPagina, Autor, Fecha, Hora, Titulo, Encabezado, Texto, PaginaPeriodico, idCapturista, calificacionSemantica, calificacionLexica, Activo, Foto, PieFoto from noticiasDia where Hora>'$full' and Categoria=80");
	$sakura->execute();
	$sakura->fetchAll(PDO::FETCH_FUNC, function($idEditorial,$Periodico, $Seccion, $Categoria, $NumeroPagina, $Autor, $Fecha, $Hora, $Titulo, $Encabezado, $Texto, $PaginaPeriodico, $idCapturista, $calificacionSemantica, $calificacionLexica, $Activo, $Foto, $PieFoto){
		$sakura=$this->db->prepare("insert into FeedNewPemex(
					idEditorial,
					Periodico,
					Seccion,
					Categoria,
					NumeroPagina,
					Autor,
					Fecha,
					Hora,
					Titulo,
					Encabezado,
					Texto,
					PaginaPeriodico,
					idCapturista,
					calificacionSemantica,
					calificacionLexica,
					Activo,
					Foto,
					PieFoto
					)
				values(
					$idEditorial,
					$Periodico,
					$Seccion,
					$Categoria,
					'$NumeroPagina',
					'$Autor',
					'$Fecha',
					'$Hora',
					'$Titulo',
					'$Encabezado',
					'$Texto',
					$PaginaPeriodico,
					$idCapturista,
					$calificacionSemantica,
					$calificacionLexica,
					$Activo,
					$Foto,
					'$PieFoto'
					)"
				);
		$sakura->execute();
	});
});
$app->run();
