//Programa da sessão 3 atualizado por Ana Santos, em 02-04-2026
// - Codificação de password: BCrypt
// - Verificação de critérios de password

#include <iostream>
#include <string>
#include <cctype>
#include <cstdlib>
#include <bcrypt/BCrypt.hpp> // Biblioteca para encriptação segura

using namespace std;

// Estrutura para os dados do utilizador
struct User {
    string username;
    string passwordHash;
};

const int MAX_USERS = 5;
User users[MAX_USERS];
int userCount = 0;

// Função de hash com BCrypt
string hashPassword(string password) {
    return BCrypt::generateHash(password); //usa o BCrypt para gerar um hash seguro com salt automático
}

// Validar username (apenas letras e sem acentos)
bool validarUsername(string username) {
    for (char c : username) {
        if (!isalpha(c) || c > 127) {
            return false;
        }
    }
    return !username.empty();
}

// Validar critérios da password
bool validarPassword(string password) {
    if (password.length() < 10) return false;

    bool hasUpper = false;
    bool hasLower = false;
    bool hasDigit = false;
    bool hasSymbol = false;

    for (char c : password) {
        if (isupper(c)) hasUpper = true;
        else if (islower(c)) hasLower = true;
        else if (isdigit(c)) hasDigit = true;
        else if (c == '-' || c == '.' || c == '!' || c == '?') hasSymbol = true;
        else return false; // caractere inválido
    }

    return hasUpper && hasLower && hasDigit && hasSymbol;
}

// Procurar utilizador
int encontrarUser(string username) {
    for (int i = 0; i < userCount; i++) {
        if (users[i].username == username) {
            return i;
        }
    }
    return -1;
}

// Validar login
bool validarLogin(int i, string password) {
    //usa BCrypt para verificar se a password corresponde ao hash armazenado
    return BCrypt::validatePassword(password, users[i].passwordHash);
}

// Criar conta
void criarConta() {
    if (userCount >= MAX_USERS) {
        cout << "Limite de contas atingido. Contacte o administrador.\n";
        return;
    }

    string username, password, confirm;

    cout << "Nome de utilizador: ";
    cin >> username;

    if (!validarUsername(username)) { //chama a função para validar os caracteres do username
        cout << "Username invalido (apenas letras sem acentos).\n";
        return;
    }

    if (encontrarUser(username) != -1) { //chama a função para verificar se o username existe no array
        cout << "Utilizador ja existe.\n";
        return;
    }

    // Mostrar regras da password
    cout << "\n=== REGRAS DA PASSWORD ===\n";
    cout << "- Minimo 10 caracteres\n";
    cout << "- Pode usar letras, numeros e simbolos (- . ! ?)\n";
    cout << "- Deve conter pelo menos:\n";
    cout << "  * 1 letra maiuscula\n";
    cout << "  * 1 letra minuscula\n";
    cout << "  * 1 numero\n";
    cout << "  * 1 simbolo (- . ! ?)\n";
    cout << "- A ordem dos caracteres e livre\n\n";

    cout << "Password: ";
    cin >> password;

    if (!validarPassword(password)) { //chama a função para verificar se a pass cumpre os critérios
        cout << "\nPassword invalida!\n";
        cout << "Tem de cumprir todas as regras apresentadas acima.\n";
        return;
    }

    cout << "Confirmar password: ";
    cin >> confirm;

    if (password != confirm) {
        cout << "Passwords nao coincidem.\n";
        return;
    }

    // Gerar hash com o BCrypt 
    string hash = hashPassword(password); //usa a função para gerar o hash

    //armazenar dados no array
    users[userCount].username = username;
    users[userCount].passwordHash = hash;

    userCount++;

    cout << "Conta criada com sucesso!\n";
}

// Login
void login() {
	//só é possível fazer login se já existir um user criado
    if (userCount == 0) {
        cout << "Nao existem contas. Nao e possivel fazer login.\n";
        return;
    }

    string username, password;

    cout << "Nome de utilizador: ";
    cin >> username;
	
	//verificar se o user consta na lista
    int index = encontrarUser(username);

    if (index == -1) {
        cout << "Utilizador nao encontrado.\n";
        return;
    }

    cout << "Password: ";
    cin >> password;

	//usa o BCrypt para verificar se a password corresponde ao hash guardado
    if (validarLogin(index, password)) { 
        cout << "Login bem sucedido!\n";
    } else {
        cout << "Password incorreta.\n";
    }
}

// Programa principal
int main() {
    int opcao;

    do {
        cout << "\n=== MENU ===\n";
        cout << "1. Login\n";
        cout << "2. Criar Conta\n";
        cout << "3. Sair\n";
        cout << "Opcao: ";
        cin >> opcao;

        switch (opcao) {
            case 1:
                login(); //chamar função
                break;
            case 2:
                criarConta(); //chamar função
                break;
            case 3:
                cout << "Obrigado por usar o programa!\n";
                break;
            default:
                cout << "Opcao invalida.\n";
        }

    } while (opcao != 3);

    return 0;
}