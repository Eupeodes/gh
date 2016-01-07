<html>
	<head>
		<title>Error $code</title>
		<style type="text/css">
			body {
				background-color: grey;
			}
			.wrapper {
				width: 250px;
				margin: auto;
				text-align: center;
				font-family: Arial, Helvetica, sans-serif;
			}
			.text {
				border: solid 1px black;
				border-radius: 5px;
				overflow: hidden;
				box-shadow: 1px 1px 2px 0px rgba(0,0,0,0.5);
			}
			.title {
				background-color: silver;
				padding: 10px;
				font-weight: bold;
				font-size: 15pt;
			}
			.content {
				background-color: white;
				padding: 10px;
			}
		</style>	
	</head>
	<body>
		<div class="wrapper">
			<img src="/img/icon.png" />
			<div class="text">
				<div class="title">$error</div>
				<div class="content">$message</div>
			</div>
		</div>
	</body>
</html>
