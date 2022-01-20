<?PHP 
 



class apptest
{
	private $host = "localhost";
	private $username = "root";
	private $password = "";
	private $dbname = "test";

	public $conn;
	public $twig; 
	function __construct()
	{

		$this->conn = new MYSQLI($this->host, $this->username, $this->password, $this->dbname);

		if(!$this->conn)
		{
			die("Database error. Please contact the website owner");
		}

		require_once __DIR__ . "./composer/vendor/autoload.php";
		$loader = new \Twig\Loader\FilesystemLoader('./templates');
		$this->twig = new \Twig\Environment($loader);
	
	}

	function Login($email, $password)
	{

		if(empty($email)){
			return false; 
			exit();
		}
		if(empty($password))
		{
			return true;
			exit();
		}
		$sql = "SELECT * FROM `accounts` WHERE email = ? ";  
		$stmt = $this->conn->prepare($sql);
		echo mysqli_error($this->conn);
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$result = $stmt->get_result(); 
		
		if(!empty($result)){

			if($result->num_rows > 0)
			{
				while ($r = $result->fetch_object()) 
				{

					
					if(crypt($password, $r->password) == $r->password)
					{

						return true;
					}
					else{
						return false;
					}
				}
			}
			else{
				return false;
			}
		}
		else{

			return false;
		}

	}	

	function Register($email, $password, $name)
	{
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		$selectSQL = "SELECT * FROM `accounts` WHERE email = ? ";  
		$stmt = $this->conn->prepare($selectSQL);
		
		$stmt->bind_param('s', $email);
		$stmt->execute();	
		$selectResults = $stmt->get_result();
					
					$stmt->close();

		if(!empty($selectResults))
		{
			if($selectResults->num_rows <= 0 ){
					
					$stored_hash = password_hash($password, PASSWORD_DEFAULT);
					$crypted_password = crypt($password, $stored_hash);

					
			 
					$stmt = $this->conn->prepare("INSERT INTO `accounts` (email, password, name) VALUES (?, ?, ?)");
					$stmt->bind_param('sss', $email, $crypted_password, $name);  

					$stmt->execute();

					return json_encode(array('Error' => '', 'Msg' => "Registered"));
				}
				else{
					return json_encode(array('Error' => 'exist', 'Msg' => "Email already exists"));
				}

		}	
		 
	}



}

 
$ch = new apptest();

if(isset($_POST['submit']))
{
	if($ch->Login($_POST['email'], $_POST['password']))
	{
		echo "<br> logged in";
	}
	else{
		echo "<br> wrong login";
	}
}
if(isset($_POST['rsubmit']))
{
	$json = $ch->Register($_POST['email'], $_POST['password'], $_POST['name']);

	$dc = json_decode($json);
	var_dump($json);
	if($dc->Error == '')
	{
		echo "Register success. Redirecting...";
		//header("refresh:3;url=index.php");
	}
	else{
		echo "Check the fields"; 
	}
}
	if(!isset($_POST['register']) && !isset($_POST['rsubmit'])){
		echo $ch->twig->render('home.html.twig', ['time' => 'login']);
		}
		else{
			echo $ch->twig->render('register.html.twig', ['time' => 'register']);
		}

?> 