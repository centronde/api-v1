<?php

declare(strict_types=1);

use PswGroup\Api\Repository\AccountContactRepository;
use PswGroup\Api\TestClient;

include '../vendor/autoload.php';

$client = new TestClient(
    '[yourClientId]',
    '[yourClientSecret]'
);

$contactRepository = new AccountContactRepository($client);
$contacts = $contactRepository->loadAll();

$list = [];

foreach ($contacts as $contact) {
    $list[] = [
        'firstname' => $contact->getFirstname(),
        'lastname' => $contact->getLastname(),
        'telephone' => $contact->getTelephone(),
        'email' => $contact->getEmail(),
    ];
}

echo sprintf('Found %d contacts.%s', count($contacts), PHP_EOL . PHP_EOL);
outputTable($list);

function outputTable(array $list, $padTypes = []): void
{
    if (count($list) === 0) {
        return;
    }

    $lengths = [];

    foreach (array_keys($list[0]) as $column) {
        $lengths[$column] = maxLength($list, $column);
    }

    $isFirst = true;

    foreach ($list as $row) {
        if ($isFirst) {
            $isFirst = false;

            foreach (array_keys($row) as $column) {
                echo '| ' . pad($column, $lengths[$column], ' ', $padTypes[$column] ?? STR_PAD_RIGHT) . ' ';
            }
            echo '|' . PHP_EOL;
        }

        foreach ($row as $column => $data) {
            echo '| ' . pad($data, $lengths[$column], ' ', $padTypes[$column] ?? STR_PAD_RIGHT) . ' ';
        }
        echo '|' . PHP_EOL;
    }
}

function maxLength(array $list, string $column): int
{
    $max = mb_strlen($column, 'UTF-8');

    foreach ($list as $row) {
        $length = mb_strlen((string) $row[$column], 'UTF-8');

        if ($length > $max) {
            $max = $length;
        }
    }

    return $max;
}

function pad(string $input, int $pad_length, string $pad_string = ' ', int $pad_type = STR_PAD_RIGHT)
{
    $mb_diff = strlen($input) - mb_strlen($input, 'UTF-8');

    return str_pad($input, $pad_length + $mb_diff, $pad_string, $pad_type);
}