<?php
	function cleanInput($data) {
		$data = trim($data);
		$data = stripcslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	function getConnectionDB() {
		$conn = mysqli_connect("localhost", "root", "", "encryption");
		if($conn === false){
			die("ERROR: Could not connect. " . mysqli_connect_error());
		}
		return $conn;
	}

	include("Crypt/RSA.php");
	define('CRYPT_RSA_PKCS15_COMPAT', true);
	$rsa = new Crypt_RSA();
	extract($rsa->createKey());
	$inputToEncrypt = "";
	$inputError = "";
	$encryptedContent = "";
	$decryptedContent = "";
	$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (empty(cleanInput($_POST["inputText"]))) {
			$inputError = "Please enter text to encrypt";
		} else {
			$inputToEncrypt = cleanInput($_POST["inputText"]);
			$rsa->loadKey($publickey);
			$encryptedContent = base64_encode($rsa->encrypt($inputToEncrypt));
			if ($encryptedContent) {
				$rsa->loadKey($privatekey);
				$decryptedContent = $rsa->decrypt(base64_decode($encryptedContent));
			}
			$conn = getConnectionDB();
			$sqlInsert = "INSERT INTO encryptions (public_key, private_key, input) VALUES ('$publickey', '$privatekey', '$inputToEncrypt')";
			mysqli_query($conn, $sqlInsert);
		}
	} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
		$conn = getConnectionDB();
		$sqlQuerieLast = "SELECT * FROM encryptions ORDER BY id DESC LIMIT 1;";
		$result = mysqli_query($conn, $sqlQuerieLast);
		$rows = mysqli_num_rows($result);
		if ($rows > 0) {
			$row = mysqli_fetch_array($result);
			$publickey = $row['public_key'];
			$privatekey = $row['private_key'];
			$inputToEncrypt = $row['input'];
			$rsa->loadKey($publickey);
			$encryptedContent = base64_encode($rsa->encrypt($inputToEncrypt));
			if ($encryptedContent) {
				$rsa->loadKey($privatekey);
				$decryptedContent = $rsa->decrypt(base64_decode($encryptedContent));
			}
		}
	}
    # Code based on: https://www.youtube.com/watch?v=G9gkh4GRUAY
                   # https://www.youtube.com/watch?v=78RUaIe4X0A
                   # https://www.youtube.com/watch?v=2HVKizgcfjo
                   # https://github.com/mousems/php-rsa/blob/master/index2.php
?>


<html lang="en">
<head>
	<meta charset="utf-8">
	<title>RSA Encryption</title>
	<base href="/">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="https://netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>  
    
</head>
<body>
	<div class="container-fluid h-100 mt-4 mb-4">
		<div class="row justify-content-center">
			<div class="col-md-5 col-12">
				<div class="container">
					<div class="row">
						<h2>RSA Encryption</h2>
					</div>
					<form class="row" action="form.php" method="post">
						<div class="col-12 p-0">
								<div class="card-body">
									<h6 class="card-title">Enter text to encrypt</h6>
									<div class="input-group">
										<textarea class="form-control" aria-label="input" name="inputText"><?php echo $inputToEncrypt; ?></textarea>
									</div>
									<label><?php echo $inputError; ?></label>
									<br/>
									<button type="submit" value="Login" class="btn btn-primary">Accept</button>
								</div>
						</div>
					</form>
					<br/>
					<form class="row" action="form.php" method="get">
						<div class="col-12 p-0">
							<div class="card">
								<h6 class="card-header">Encrypted Text</h6>
								<div class="card-body">
									<div class="input-group">
										<textarea class="form-control" aria-label="input" disabled><?php echo $encryptedContent; ?></textarea>
									</div>
									<br/>
									<button type="submit" value="Load" class="btn btn-primary">Loading from SQL Database</button>
								</div>
							</div>
						</div>
					</form>
					<br/>
					<div class="row">
						<div class="col-12 p-0">
							<div class="card">
								<h6 class="card-header">Decrypted Text</h6>
								<div class="card-body">
									<div class="input-group">
										<textarea class="form-control" aria-label="input" disabled><?php echo $decryptedContent; ?></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>

