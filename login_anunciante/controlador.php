<?php
require "../conexaoMysql.php";

// Define uma classe para a resposta do login
class LoginResult
{
    public $isAuthorized;
    public $newLocation;

    function __construct($isAuthorized, $newLocation)
    {
        $this->isAuthorized = $isAuthorized;
        $this->newLocation = $newLocation;
    }
}

function checkUserCredentials($pdo, $email, $senha)
{
    $sql = "SELECT Id, SenhaHash FROM Anunciante WHERE Email = ?";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($senha, $user['SenhaHash'])) {
            return false;
        }
        return $user['Id'];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}


$acao = $_GET['acao'] ?? '';

switch ($acao) {
    case 'login':
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        $pdo = mysqlConnect();
        $anuncianteId = checkUserCredentials($pdo, $email, $senha);

        if ($anuncianteId) {
            $cookieParams = session_get_cookie_params();
            $cookieParams['httponly'] = true;
            session_set_cookie_params($cookieParams);
            
            session_start();
            $_SESSION['loggedIn'] = true;
            $_SESSION['id_anunciante'] = $anuncianteId;
            
            $response = new LoginResult(true, 'principal_restrita.php');
        } else {
            $response = new LoginResult(false, '');
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        break;

    case 'logout':
        session_start();
        session_unset();
        session_destroy();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        header("Location: login.html");
        exit();
        break;

    default:
        header("HTTP/1.1 400 Bad Request");
        echo "Ação não especificada.";
        break;
}