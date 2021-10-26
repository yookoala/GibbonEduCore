<?php

class State {
    const NO_DATA = 0;
    const SET_ENV = 1;
    const CREATE_TABLE = 2;
    const INSERT_DATA = 3;
    const ALTER_TABLE = 4;

    public static function parseLine($line, $previousState)
    {
        switch (true) {
        case empty(trim($line)):
        case preg_match('/^--/', $line):
            return static::NO_DATA;
        case preg_match('/^SET /i', $line):
            return static::SET_ENV;
        case preg_match('/^CREATE TABLE /i', $line);
            return static::CREATE_TABLE;
        case preg_match('/^INSERT INTO /i', $line):
            return static::INSERT_DATA;
        case preg_match('/^ALTER TABLE /i', $line):
            return static::ALTER_TABLE;
        default:
            return $previousState;
        }

    }
}

function parseAuthor($author) {
    if (!preg_match('/^([\w0-9 ]+?)\s*(\d{4}\-\d{1,2}\-\d{1,2} \d{2}:\d{2}:\d{2} [+\-\d]*).*$/', $author, $matches)) {
        throw new \Exception(sprintf('Fail to parse author: %s', $author));
    }
    return [
        'name' => $matches[1],
        'date' => DateTime::createFromFormat('Y-m-d H:i:s P', $matches[2]),
    ];
}

function parseQueriesWithBlame($resource) {
    $last_state = State::NO_DATA;
    $date = new DateTime('now');
    $buffer = '';
    $i = 1;

    while (($content = fgets($resource, 4096)) !== false) {
        if (!preg_match('~^(\w+) \((.+?)\) (.*?)$~', $content, $matches)) {
            throw new \Exception(sprintf('Fail to parse line: %s', $content));
        }
        list($all, $hash, $author, $line) = $matches;
        $info = parseAuthor($author);
        $state = State::parseLine($line, $last_state);
        if ($state === $last_state) {
            if ($date > $info['date']) {
                $date = $info['date'];
            }
            $buffer .= $line . "\n";
        } else {
            if ($last_state !== State::NO_DATA) {
                $i++;
                echo "{$date->format('Y-m-d H:i:s P')}\n";
                echo $buffer . "\n";
                echo '<<<< ---- >>>>' . "\n";
            }
            $date = new DateTime('now');
            $buffer = $line . "\n";
            $last_state = $state;
        }
    }

    if ($last_state !== State::NO_DATA) {
        $i++;
        echo "{$date->format('Y-m-d H:i:s P')}\n";
        echo $buffer . "\n";
        echo '<<<< ---- >>>>' . "\n";
    }

    echo "\ni = $i\n";
}

function parseQueries($resource) {
    $last_state = State::NO_DATA;
    $date = new DateTime('now');
    $buffer = '';
    $i = 1;

    while (($line = fgets($resource, 4096)) !== false) {
        $state = State::parseLine($line, $last_state);
        if ($state === $last_state) {
            $buffer .= $line;
        } else {
            if ($last_state !== State::NO_DATA) {
                $i++;
                echo $buffer . "\n";
                echo '<<<< ---- >>>>' . "\n";
            }
            $buffer = $line;
            $last_state = $state;
        }
    }

    if ($last_state !== State::NO_DATA) {
        $i++;
        echo $buffer;
        echo '<<<< ---- >>>>' . "\n";
    }

    echo "\ni = $i\n";
}

#$fh = fopen(__DIR__ . '/gibbon.sql', 'r');
#$fh = popen('git blame ' . __DIR__ . '/gibbon.sql', 'r');
$fh = fopen(__DIR__ . '/output.sql', 'r');
parseQueries($fh);
