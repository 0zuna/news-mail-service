<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;                                                                                               
use Slim\Views\TwigExtension;
use Sendinblue\Mailin;
require 'vendor/autoload.php';
ini_set('date.timezone', 'America/Mexico_City');

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['db']['host']   = "127.0.0.1";
$config['db']['user']   = "root";
$config['db']['pass']   = "Gaddp552014";
$config['db']['dbname'] = "monitoreoGa";
$config['db']['charset']= "utf8";
$config['db']['port']	= "3306";

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['db'] = function ($c) {
	$db = $c['settings']['db'];
	$pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'] . ";charset=" . $db['charset'] . ";port=" . $db['port'],$db['user'], $db['pass']);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	return $pdo;
};
//TWIG                                                                                                             
$container['view'] = function ($container) {
	$view = new Twig('./templates', [
		'cache' => false,
		'debug' => true,
	]);

	$basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new TwigExtension($container['router'], $basePath));
	$view->addExtension(new Twig_Extension_Debug());
	return $view;
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
	$ch = curl_init('192.168.3.154/news-mail-service/mail/corpogas/');
	curl_exec($ch);
	curl_close($ch);
});
$app->get('/mail/{hinata}/', function (Request $request, Response $response) {
	global $ids;
	$ids=array();
	$hinata = $request->getAttribute('hinata');
	$sakura=$this->db->prepare("select
					menu_items.query
				from boards 
					inner join menus on boards.id=menus.board_id
					inner join menu_items on menus.id=menu_items.menu_id
				where boards.alias='$hinata'");
	$sakura->execute();
	$data=$sakura->fetchAll(PDO::FETCH_FUNC, function($query){
		if($query!=''){
		$sakura=$this->db->prepare(str_replace('noticiasDia', 'FeedNewPemex', $query));
		$sakura->execute();
		$sakura->fetchAll(PDO::FETCH_FUNC, function($idPeriodico,$idEditorial,$tes){
			global $ids;
			$r=(int)$tes;
			$sakura=$this->db->prepare("select Titulo from noticiasDia where idEditorial=$idEditorial or idEditorial=$idPeriodico or idEditorial=$r");
			$sakura->execute();
			$ids=array_merge($ids,$sakura->fetchAll());
		});}
	});
	if($ids){
		$men=$this->view->fetch('mail.tpl.php',['items'=>$ids]);
		$mailin = new Mailin("https://api.sendinblue.com/v2.0","wjSbMAENLm2TGfpW");
		$data = array(
			"to" =>["er1k_92@hotmail.com"=>"Ninja!"],
			"from" =>["gaimpresos@gacomunicacion.com", "ga impresos!"],
			"subject" => "NEWS",
			"html" => $men,
			);
		$mailin->send_email($data);
	}
});
$app->run();
