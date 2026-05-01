<?php
//Programa desenvolvido por Ana Santos, adaptação do programa da sessão 4 para php em 08-04-2026
// - Prevenir ataques XSS
// htmlspecialchars; POST; input tratado com trim(); nosniff; lista de seleção no menu

header("Content-Type: text/html; charset=UTF-8");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

session_start();

// Estrutura para os dados do utilizador
class User {
    public $username;
    public $passwordHash;
}

const MAX_USERS = 5;

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

// Função de hash com BCrypt
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT); //usa o BCrypt para gerar um hash seguro com salt automático
}

// Função para prevenir XSS ao mostrar dados
function safeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Validar username (apenas letras e sem acentos)
function validarUsername($username) {
    if (empty($username)) {return false;}

    for ($i = 0; $i < strlen($username); $i++) {
        $c = $username[$i];
        if (!ctype_alpha($c) || ord($c) > 127) {
            return false;
        }
    }
    return true;
}

// Validar critérios da password
function validarPassword($password) {
    if (strlen($password) < 10) {return false;}

    $hasUpper = false;
    $hasLower = false;
    $hasDigit = false;
    $hasSymbol = false;

    for ($i = 0; $i < strlen($password); $i++) {
        $c = $password[$i];

        if (ctype_upper($c)) {$hasUpper = true;}
        elseif (ctype_lower($c)) {$hasLower = true;}
        elseif (ctype_digit($c)) {$hasDigit = true;}
        elseif ($c == '-' || $c == '.' || $c == '!' || $c == '?') {$hasSymbol = true;}
        else {return false;} // caractere inválido
    }

    return $hasUpper && $hasLower && $hasDigit && $hasSymbol;
}

// Procurar utilizador
function encontrarUser($username) {
    foreach ($_SESSION['users'] as $i => $user) {
        if ($user->username === $username) {
            return $i;
        }
    }
    return -1;
}

// Validar login
function validarLogin($i, $password) {
    //usa BCrypt para verificar se a password corresponde ao hash armazenado
    return password_verify($password, $_SESSION['users'][$i]->passwordHash);
}

$message = "";

// PROCESSAMENTO DO FORMULÁRIO
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $acao = $_POST['acao'] ?? '';

    // entrada de dados com trim()
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // Criar conta
    if ($acao === "criar") {

        if (count($_SESSION['users']) >= MAX_USERS) {
            $message = "Limite de contas atingido. Contacte o administrador.";
        }

        elseif (!validarUsername($username)) { //chama a função para validar os caracteres do username
            $message = "Username invalido (apenas letras sem acentos).";
        }

        elseif (encontrarUser($username) != -1) { //chama a função para verificar se o username existe no array
            $message = "Utilizador ja existe.";
        }

        elseif (!validarPassword($password)) { //chama a função para verificar se a pass cumpre os critérios
            $message = "Password invalida! Tem de cumprir todas as regras.";
        }

        elseif ($password !== $confirm) {
            $message = "Passwords nao coincidem.";
        }

        else {
            // Gerar hash com o BCrypt
            $hash = hashPassword($password); //usa a função para gerar o hash

            //armazenar dados no array
            $user = new User();
            $user->username = $username;
            $user->passwordHash = $hash;

            $_SESSION['users'][] = $user;

            $message = "Conta criada com sucesso!";
        }
    }

    // Login
    if ($acao === "login") {

        //só é possível fazer login se já existir um user criado
        if (count($_SESSION['users']) == 0) {
            $message = "Nao existem contas. Nao e possivel fazer login.";
        }

        else {
            //verificar se o user consta na lista
            $index = encontrarUser($username);

            if ($index == -1) {
                $message = "Utilizador nao encontrado.";
            }

            elseif (validarLogin($index, $password)) {
                //usa o BCrypt para verificar se a password corresponde ao hash guardado
                $message = "Login bem sucedido!";
            } else {
                $message = "Password incorreta.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Sistema de Login</title>
</head>
<body>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f2f6ff; /* cor clara */
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

header {
    background-color: #4a6fa5;
    color: white;
    text-align: center;
    padding: 15px;
    font-size: 22px;
    font-weight: bold;
}

footer {
    background-color: #4a6fa5;
    color: white;
    text-align: center;
    padding: 10px;
    margin-top: auto;
}

.container {
    width: 100%;
    max-width: 600px;
    margin: 30px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
}

h2, h3 {
    text-align: center;
}

select, input, button {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    margin-bottom: 10px;
}
</style>

<header>
    Sessão 5
</header>

<div class="container">

<h2>=== MENU ===</h2>

<?php if ($message): ?>
    <p><?= safeOutput($message) ?></p>
<?php endif; ?>

<!-- SELEÇÃO -->
<label> <input type="text" /> Escolha uma opção:</label>
<select id="opcao" onchange="mostrarFormulario()">
    <option value="">-- Selecionar --</option>
    <option value="login">Login</option>
    <option value="criar">Criar Conta</option>
</select>

<!-- LOGIN -->
<div id="formLogin" style="display:none;">
    <h3>Login</h3>
    <form method="POST">
        <input type="hidden" name="acao" value="login">

        Nome de utilizador:
        <input type="text" name="username" required>

        Password:
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>

<!-- CRIAR CONTA -->
<div id="formCriar" style="display:none;">
    <h3>Criar Conta</h3>
    <form method="POST">
        <input type="hidden" name="acao" value="criar">

        Nome de utilizador:
        <input type="text" name="username" required>

        Password:
        <input type="password" name="password" required>

        Confirmar password:
        <input type="password" name="confirm" required>

        <button type="submit">Criar Conta</button>
    </form>
</div>

</div>

<footer>
    Desenvolvido por Ana Santos – <?php echo date("Y"); ?>
</footer>

<script>
function mostrarFormulario() {
    var opcao = document.getElementById("opcao").value;

    document.getElementById("formLogin").style.display = "none";
    document.getElementById("formCriar").style.display = "none";

    if (opcao === "login") {
        document.getElementById("formLogin").style.display = "block";
    }
    else if (opcao === "criar") {
        document.getElementById("formCriar").style.display = "block";
    }
}
</script>

</body>
</html>
