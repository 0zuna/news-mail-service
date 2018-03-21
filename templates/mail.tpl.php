<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width" />
		<title></title>
		<link href="https://fonts.googleapis.com/css?family=Raleway:100,300" rel="stylesheet" type="text/css">
		<style>
			body{
				font-family: 'Raleway', sans-serif;
			}
			a{
				position:relative;
				left: 80%;
			}
			ul{
				list-style:none;
			}
			img{
				/*width:100%;*/
				box-shadow: 1px 1px 1px #888888;
			}
			.card{
				width: 600px;
				background-color: white;
				box-shadow: 3px 3px 10px #888888;
			}
			.content{
				text-align: left;
				padding: 20px;
				border-bottom: 2px solid #D3D7CF;
			}
			.item{
				/*display: inline-block;*/
				width: 100%;
				/*height: 75px;*/
				/*margin: 10px;*/
				padding:5px;
			}
			.item>img{
				width:60px;
			}
			.tema{
				text-align: center;
			}
		</style>
	</head>

	<body>
		<center>
			<div class='card'>
				<img src='http://192.168.3.154/siscap.la/public/img/tableros/logoGA2.png' alt='' />
				{% for item in items %}
				<div class='content'>
					<div class='tema'>
						<h4>{{item['naomi']}}</h4>
					</div>
					{% for sakura in item['haruka']%}
					<ul>
						<li>
							<div class='item'>
								<img src="http://192.168.3.154/siscap.la/public/img/portadas/thumbs/thumb-{{sakura['idPeriodico']}}.jpg" alt="">
							</div>
						</li>
						<li>
							<div class='item'>
								{{sakura['Titulo']}}
							<div>
						</li>
						<li>
							<div class='item'>
								{{sakura['Fecha']}}
							</div>
						</li>
						<li>
							<div class='item'>
								<a href="{{sakura['Encabezado']}}">Click aquí</a>
							<div>
						</li>
					</ul>
					{% endfor %}
				</div>
			{% endfor %}
			</div>
		</center>
	</body>
</html>
