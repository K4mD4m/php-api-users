<?php

//Ustawienie nagłówków, aby API zwracało dane w formacie JSON i obsługiwało CORS
header("Content-Type: application/json;");
header("Access-Control-Allow-Origin: *"); //Zezwolenie na dostęp z dowolnej domeny
header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT");

// Pliki do przechowywania danych (symulacja bazy danych)
$dataFile = "data.json";

// Jeśli plik nie istnieje, utwórz pustą tablicę użytkowników
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

// Odczytanie istniejacych użytkowników
$users = json_decode(file_get_contents($dataFile), true) ?? [];

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
} elseif ($method === 'DELETE') {
    // Pobieramy ID użytkownika do usunięcia
    parse_str(file_get_contents("php://input"), $deleteData);
    $id = $deleteData['id'] ?? null;

    if (!$id) {
        echo json_encode(["error" => "Brak ID użytkownika"]);
        exit;
    }

    // Szukamy użytkownika o podanym ID do usunięcia
    $index = array_search($id, array_column($users, 'id'));
    if ($index === false) {
        echo json_encode(["error" => "Użytkownik nie istnieje"]);
        exit;
    }

    // Usuwamy użytkownika
    array_splice($users, $index, 1);
    file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT));

    echo json_encode(["success" => "Użytkownik usunięty", "id" => $id]);
} elseif ($method === 'PUT') {
    //Odczytujemy dane wysłane w PUT
    $input = json_decode(file_get_contents("php://input"), true);

    //Sprawdzamy, czy przesłano poprawne dane 
    $id = $input['id'] ?? null;
    $newName = $input['name'] ?? null;

    if (!$id || !$newName) {
        echo json_encode(["error" => "Brak ID lub nazwy użytkownika"]);
        exit;
    }

    //Szukamy użytkownika o podanym ID do aktualizacji
    $index = array_search($id, array_column($users, 'id'));
    if ($index === false) {
        echo json_encode(["error" => "Użytkownik nie istnieje"]);
        exit;
    }

    // Zaktualizowanie danych użytkownika
    $users[$index]['name'] = htmlspecialchars($newName);

    // Zapisujemy zaktualizowaną tablicę do pliku JSON
    file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT));

    // Zwracamy odpowiedź z zaktualizowanym użytkownikiem
    echo json_encode(["success" => "Użytkownik zaktualizowany", "user" => $users[$index]]);
} else {
    echo json_encode(["error" => "Nieobsługiwana metoda HTTP"]);
}