<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;                                                                                               
use Slim\Views\TwigExtension;
use Sendinblue\Mailin;
require 'vendor/autoload.php';
ini_set('date.timezone', 'America/Mexico_City');
setlocale(LC_ALL,"es_MX.UTF8");

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
	$rest = $time->modify('-5 hour');
	$full=$rest->format('H:i:s');
	
	$sakura=$this->db->prepare("select idEditorial, Periodico, Seccion, Categoria, NumeroPagina, Autor, Fecha, Hora, Titulo, Encabezado, Texto, PaginaPeriodico, idCapturista, calificacionSemantica, calificacionLexica, Activo, Foto, PieFoto from noticiasDia where Hora>'$full' and fecha = CURDATE()");
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
	$n=fopen("log", "a");
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
	$sakura->fetchAll(PDO::FETCH_FUNC, function($query){
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
		echo 'mail';
	}
	fclose($n);
});
$app->get('/sakura/{hinata}/', function (Request $request, Response $response) {
	global $ids;
	global $mai;
	$ids=array();
	$mai=array();
	$hinata = $request->getAttribute('hinata');
	$sakura=$this->db->prepare("select
					menu_items.query_web,
					boards.id,
					menu_items.text
				from boards 
					inner join menus on boards.id=menus.board_id
					inner join menu_items on menus.id=menu_items.menu_id
				where boards.alias='$hinata'");
	$sakura->execute();
	$sakura->fetchAll(PDO::FETCH_FUNC, function($query,$id,$naomi){
		global $ids;
		if($query!=''){
			$query=str_replace("CONCAT(DATE_FORMAT(n.Fecha, '%Y-%m-%d'),' ',n.Hora) as Fecha,", '', $query);
			$sakura=$this->db->prepare("select n.Titulo, n.Encabezado, n.idEditorial as idEditorial, n.idPeriodico, n.Fecha, $id as board_id from ($query) as n left join notification_controls no on n.idEditorial=no.idEditorial where no.idEditorial is null group by n.idEditorial");
			$sakura->execute();
			$haruka=$sakura->fetchAll();
			if($haruka)
				array_push($ids, ['naomi'=>$naomi,'haruka'=> $haruka]);
		}
	});
	if($ids){
		//$ids = array_map("unserialize", array_unique(array_map("serialize", $ids)));
		foreach ($ids as $k=>$value) {
			foreach ($value['haruka'] as $k2=>$value2) {
				$value['haruka'][$k2]['Fecha']=strftime("%A %d de %B del %Y",strtotime($value2['Fecha']));
				$sakura=$this->db->prepare("insert into notification_controls(idEditorial, user_id, board_id) values(".$value2['idEditorial'].",1,".$value2['board_id'].")");
				$sakura->execute();
			}
			$ids[$k]['haruka']=$value['haruka'];
		}
		$sakura=$this->db->prepare("select email, nombre from notification_mails where board_id=".$ids[0]['haruka'][0]['board_id']." and activo=1");
		$sakura->execute();
		$sakura->fetchAll(PDO::FETCH_FUNC, function($sakura,$hinata){
			global $mai;
			$mai=array_merge($mai,[$sakura=>$hinata]);
		});
		if($mai){
			$men=$this->view->fetch('mail.tpl.php',['items'=>$ids]);
			$mailin = new Mailin("https://api.sendinblue.com/v2.0","wjSbMAENLm2TGfpW");
			$data = array(
				"to" =>$mai,
				"from" =>["gaimpresos@gacomunicacion.com", "ga impresos!"],
				"subject" => "Notas Nuevas",
				"html" => $men,
				);
			$mailin->send_email($data);
		}
	}
});

$app->run();
