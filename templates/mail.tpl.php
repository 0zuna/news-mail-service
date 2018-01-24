<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width" />
		<title></title>
		<style>
			img{
				/*width:100%;*/
				box-shadow: 2px 2px 20px #888888;
			}
			.card{
				width: 600px;
				background-color: white;
				box-shadow: 3px 3px 10px #888888;
				font-family: 'Times New Roman', Times, serif;
			}
			.content{
				text-align: left;
				border-bottom: 1px solid #D3D7CF;
			}

			.item{
				display: inline-block;
				width: 50%;
				/*height: 75px;*/
				margin: 10px;
				padding:20px;
			}
			.button {
				background-color: #e7e7e7;
				color: black;
				border: none;
				padding: 15px 32px;
				text-align: center;
				text-decoration: none;
				display: inline-block;
				font-size: 16px;
				box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
				cursor: pointer;
			}
		</style>
	</head>

	<body>
		<center>
			<div class='card'>
				<img src='http://187.247.253.5/siscap.la/public/img/tableros/logoGA2.png' alt='' />
				{% for item in items %}
				<div class='content'>
					<div class='item'>
						{{item['Titulo']}}
					</div>
					{%if(item)%}
					<a class='button' href="{{item['Encabezado']}}">Nueva</a>
					{%endif%}
				</div>
				{% endfor %}
			</div>
		</center>
	</body>
</html>
