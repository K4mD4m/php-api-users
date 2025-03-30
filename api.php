<?php

//Ustawienie nagłówków, aby API zwracało dane w formacie JSON i obsługiwało CORS
header("Content-Type: application/json;");
header("Access-Control-Allow-Origin: *"); //Zezwolenie na dostęp z dowolnej domeny
header("Access-Control-Allow-Methods: GET, POST");

// Pliki do przechowywania danych (symulacja bazy danych)
$dataFile = "data.json";

// Jeśli plik nie istnieje, utwórz pustą tablicę użytkowników
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

// Odczytanie istniejacych użytkowników
$users = json_decode(file_get_contents($dataFile), true);

// Sprawdzamy metodę żądania HTTP
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Jeśli użytkownik wysyła GET, zwracamy listę użytkowników
    echo json_encode($users);
} elseif ($method === 'POST') {
    //Odczytujemy dane wysłane w POST (przysyłane w JSON)
    $input = json_decode(file_get_contents("php://input"), true);

    // Sprawdzamy, czy przesłano poprawne dane
    if (!isset($input['name']) || empty($input['name'])) {
        echo json_encode(["error" => "Brak nazwy użytkownika"]);
        exit;
    }

    // Tworzymy nowego użytkownika (ID + name)
    $newUser = [
        "id" => count($users) + 1, // Proste generowanie ID
        "name" => htmlspecialchars($input['name']) // Zabezpieczenie przed XSS
    ];

    // Dodajemy nowego użytkownika do tablicy
    $users[] = $newUser;

    // Zapisujemy do pliku JSON
    file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT));

    // Zwracamy odpowiedź z nowym użytkownikiem
    echo json_encode(["success" => "Użytkownik dodany", "user" => $newUser]);
} else {
    // Jeśli metoda nie jest GET ani POST, zwracamy błąd
    echo json_encode(["error" => "Nieobsługiwana metoda HTTP"]);
}