<?php

// KeyGenerator.php
class KeyGenerator {
    public static function generateKey() {
        return bin2hex(random_bytes(32)); // Генерация 256-битного (32 байта) случайного ключа в шестнадцатеричном формате
    }
}

// HmacGenerator.php
class HmacGenerator {
    public static function generateHmac($key, $message) {
        return hash_hmac('sha256', $message, $key); // Вычисление HMAC с использованием SHA-256
    }
}

// GameRules.php
class GameRules {
    private static $moves = array(); // Список доступных ходов

    public static function setMoves($moves) {
        self::$moves = $moves;
    }

    public static function getMoves() {
        return self::$moves;
    }

    public static function determineWinner($userMove, $computerMove) {
        // Получаем список ходов
        $moves = self::getMoves();
        $numMoves = count($moves);

        // Определяем индексы выбранных ходов
        $userIndex = array_search($userMove, $moves);
        $computerIndex = array_search($computerMove, $moves);

        // Вычисляем расстояние между ходами с учетом размера списка ходов
        // Изменяем формулу для определения победителя
        $distance = ($computerIndex - $userIndex + $numMoves) % $numMoves;

        // Определяем победителя
        if ($distance == 0) {
            return "Draw"; // Ничья
        } elseif ($distance % 2 == 1) {
            return "Win"; // Победа пользователя
        } else {
            return "Lose"; // Победа компьютера
        }
    }

}
// HelpTable.php
class HelpTable {
    private static function generateTable($moves) {
        // Генерация таблицы
        $table = "";

        // Генерация заголовка
        $table .= "+------------------------+";
        foreach ($moves as $move) {
            $table .= "-------+";
        }
        $table .= "\n";

        // Заголовок строк и данных
        $table .= "| v PC\\User >           ";
        foreach ($moves as $move) {
            $table .= "| " . str_pad($move, 6, " ", STR_PAD_BOTH);
        }
        $table .= "|\n";

        // Разделительная строка
        $table .= "+------------------------+";
        foreach ($moves as $move) {
            $table .= "-------+";
        }
        $table .= "\n";

        // Данные таблицы
        foreach ($moves as $move1) {
            $table .= "| " . str_pad($move1, 23, " ", STR_PAD_RIGHT);
            foreach ($moves as $move2) {
                $result = GameRules::determineWinner($move2, $move1); // Инвертируем порядок аргументов
                $table .= "| " . str_pad($result, 6, " ", STR_PAD_BOTH);
            }
            $table .= "|\n";
        }

        // Завершающая строка таблицы
        $table .= "+------------------------+";
        foreach ($moves as $move) {
            $table .= "-------+";
        }
        $table .= "\n";

        return $table;
    }

    public static function displayHelp($moves) {
        $table = self::generateTable($moves);
        echo $table;
    }
}

// Основной скрипт

// Получаем ходы из аргументов командной строки
$moves = array_slice($argv, 1);

// Проверяем, что количество ходов нечетное и не меньше 3
if (count($moves) < 3 || count($moves) % 2 == 0 || count($moves) !== count(array_unique($moves))) {
    echo "Error: Invalid number of moves or duplicate moves. Please provide an odd number of unique moves (at least 3).\n";
    exit(1);
}

// Устанавливаем ходы для правил игры
GameRules::setMoves($moves);

// Генерируем случайный ключ
$key = KeyGenerator::generateKey();

// Генерируем ход компьютера
$computerMove = $moves[array_rand($moves)];

// Вычисляем HMAC
$hmac = HmacGenerator::generateHmac($key, $computerMove);

// Отображаем таблицу помощи только если пользователь запросил
if (in_array('?', $argv)) {
    HelpTable::displayHelp($moves);
}

// Показываем HMAC
echo "HMAC: $hmac\n\n";

// Выводим меню пользователю
echo "\nAvailable moves:\n";
foreach ($moves as $index => $move) {
    echo ($index + 1) . " - $move\n";
}
echo "0 - exit\n";
echo "? - help\n";

// Запрашиваем ход пользователя
echo "\nEnter your move: ";
$userChoice = readline();

// Проверяем, если пользователь ввёл '?', отображаем таблицу помощи
if ($userChoice === '?') {
    HelpTable::displayHelp($moves);
    echo "\nEnter your move: ";
    $userChoice = readline();
}

if ($userChoice === '0') {
    echo "\nExiting the program ";
    exit(1);
}

// Обрабатываем выбор пользователя
if (!is_numeric($userChoice) || $userChoice < 0 || $userChoice > count($moves)) {
    echo "\nError: Invalid input. Please enter a number corresponding to your move.\n";
    echo "\nEnter your move: ";
    $userChoice = readline();
}

$userMove = $moves[$userChoice - 1];

// Определяем победителя
$result = GameRules::determineWinner($userMove, $computerMove);

// Выводим результат
echo "\nYour move: $userMove\n";
echo "Computer move: $computerMove\n";
echo "$result\n";
echo "HMAC key: $key\n";

?>