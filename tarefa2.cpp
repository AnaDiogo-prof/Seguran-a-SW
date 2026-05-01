//Criado por Ana Santos, em 15-03-2026

#include <iostream>
#include <string>

using namespace std;

int main() {
    string nome;
    int idade;
    char genero;
    bool generoValido = false;

    // Receber e validar o nome (máx 60 caracteres)
    do {
        cout << "Introduza o nome (max 60 caracteres): ";
        getline(cin, nome);

        if (nome.length() == 0 || nome.length() > 60) {
            cout << "Nome invalido. Tente novamente.\n";
        }

    } while (nome.length() == 0 || nome.length() > 60);

    // receber e validar a idade (0 - 130)
    while (true) {
        cout << "Introduza a idade (0-130): ";
        cin >> idade;

        if (cin.fail()) {
            cout << "Idade invalida. Introduza um numero.\n";
            cin.clear();
            cin.ignore(1000, '\n');
            continue;
        }

        if (idade < 0 || idade > 130) {
            cout << "Idade fora do intervalo.\n";
            cin.ignore(1000, '\n');
            continue;
        }

       // cin.ignore(1000, '\n');
        break;
    }

    cin.ignore(); // limpar buffer

    // Validar genero opcional
    do {
        cout << "Introduza o genero (m/f) ou pressione ENTER para ignorar: ";
        string entrada;
        getline(cin, entrada);

        if (entrada == "") {
            genero = ' ';
            generoValido = true;
        }
        else if (entrada == "m" || entrada == "M") {
            genero = 'm';
            generoValido = true;
        }
        else if (entrada == "f" || entrada == "F") {
            genero = 'f';
            generoValido = true;
        }
        else {
            cout << "Genero invalido. Use m ou f.\n";
        }

    } while (!generoValido);

    // Saida formatada
    if (genero == 'm') {
        cout << "Caro " << nome << ", tem " << idade << " anos\n";
    }
    else if (genero == 'f') {
        cout << "Cara " << nome << ", tem " << idade << " anos\n";
    }
    else {
        cout << "Caro/a " << nome << ", tem " << idade << " anos\n";
    }

    return 0;
}